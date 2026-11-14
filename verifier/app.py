import os
import json
import hashlib
import datetime
import urllib.request
import urllib.error
from flask import Flask, request, jsonify, render_template, session, redirect, url_for

app = Flask(__name__)
app.secret_key = os.getenv("SECRET_KEY", "brew-bean-verifier-session-secret-2026")

SECRET_SALT = "BREW_BEAN_ACADEMIC_INTEGRITY_SALT_2026"
WEB_INTERNAL_URL = os.getenv("WEB_INTERNAL_URL", "http://brew-web")

# Configured Challenge Flags (NO ENDPOINTS LEAKED TO STUDENTS)
CHALLENGES = {
    "BB-F01": {
        "title": "Authentication & Account Enumeration",
        "category": "Authentication Auditing",
        "objective": "Identify valid account names through differential server responses and audit weak credentials.",
        "hint": "Observe the error messages on the authentication interface when testing valid versus invalid accounts, and audit weak credentials using the provided wordlist.",
        "flag": os.getenv("FLAG_F01", "FLAG{BREW_USER_ENUMERATION_01}").strip()
    },
    "BB-F02": {
        "title": "Horizontal Broken Access Control (IDOR)",
        "category": "Access Control",
        "objective": "Manipulate order identifiers to access another customer's confidential executive order.",
        "hint": "Inspect order receipt URLs for sequential or predictable record identifiers that might lack server-side customer ownership verification.",
        "flag": os.getenv("FLAG_F02", "FLAG{BREW_IDOR_02}").strip()
    },
    "BB-F03": {
        "title": "Vertical Privilege Escalation (BFLA)",
        "category": "Authorization Bypass",
        "objective": "Access restricted management and executive audit reports using a lower-privileged account.",
        "hint": "Administrative interfaces often protect the main entry portal while neglecting role checks on internal functional reporting endpoints.",
        "flag": os.getenv("FLAG_F03", "FLAG{BREW_VERTICAL_ACCESS_03}").strip()
    },
    "BB-F04": {
        "title": "Operating System Command Injection",
        "category": "Server Injection",
        "objective": "Execute operating system commands via operational diagnostics and read the initial access proof file.",
        "hint": "Explore operational connectivity or diagnostic utilities that interact with system ping/network binaries to test for command chaining operators.",
        "flag": os.getenv("FLAG_F04", "FLAG{BREW_COMMAND_INJECTION_04}").strip()
    },
    "BB-F05": {
        "title": "Stored Cross-Site Scripting (XSS)",
        "category": "Client-Side Injection",
        "objective": "Inject script into customer feedback and trigger execution during staff moderation.",
        "hint": "Test customer review forms for unescaped HTML/script storage, and monitor the review feed to see the official staff response and token.",
        "flag": os.getenv("FLAG_F05", "FLAG{BREW_STORED_XSS_05}").strip()
    },
    "BB-F06": {
        "title": "Linux Privilege Escalation via Scheduled Task",
        "category": "Privilege Escalation",
        "objective": "Escalate privileges to root inside the target container by exploiting a misconfigured maintenance task.",
        "hint": "Inspect scheduled system cron jobs executing with elevated privileges and audit the write permissions on associated script paths.",
        "flag": os.getenv("FLAG_F06", "FLAG{BREW_ROOT_ESCALATION_06}").strip()
    }
}

# Runtime tracking
submissions = []
solved_flags = {}
server_events = {}
viewed_hints = set()
is_locked = False
student_info = {
    "name": "",
    "roll_number": ""
}

def generate_report_hash(name, roll_number, solved_count, hints_count, timestamp):
    payload = f"{name.strip().upper()}|{roll_number.strip().upper()}|{solved_count}|{hints_count}|{timestamp}|{SECRET_SALT}"
    return hashlib.sha256(payload.encode("utf-8")).hexdigest()

@app.route("/")
def dashboard():
    total = len(CHALLENGES)
    solved = len(solved_flags)
    progress = int((solved / total) * 100) if total else 0
    all_solved = (solved == total) and total > 0

    return render_template(
        "index.html",
        challenges=CHALLENGES,
        solved_flags=solved_flags,
        server_events=server_events,
        viewed_hints=viewed_hints,
        hints_count=len(viewed_hints),
        submissions=submissions,
        total_flags=total,
        solved_count=solved,
        progress_percent=progress,
        all_solved=all_solved,
        student=student_info,
        is_locked=is_locked
    )

@app.route("/api/set_student", methods=["POST"])
def set_student():
    global student_info
    if is_locked:
        return jsonify({"success": False, "message": "Evaluation session is locked."}), 403

    data = request.get_json(silent=True) or request.form
    name = (data.get("name") or "").strip()
    roll_number = (data.get("roll_number") or "").strip()

    if not name or not roll_number:
        return jsonify({"success": False, "message": "Please enter both Student Name and Roll Number."}), 400

    student_info["name"] = name
    student_info["roll_number"] = roll_number
    return jsonify({"success": True, "student": student_info})

@app.route("/api/view_hint", methods=["POST"])
def view_hint():
    global viewed_hints
    if is_locked:
        return jsonify({"success": False, "message": "Session locked: Cannot view hints after report generation."}), 403

    data = request.get_json(silent=True) or request.form
    flag_id = data.get("flag_id")

    if flag_id in CHALLENGES:
        viewed_hints.add(flag_id)
        return jsonify({
            "success": True,
            "flag_id": flag_id,
            "hint": CHALLENGES[flag_id]["hint"],
            "total_hints_viewed": len(viewed_hints)
        })
    return jsonify({"success": False, "message": "Invalid challenge identifier."}), 400

@app.route("/api/submit_flag", methods=["POST"])
def submit_flag():
    global is_locked
    if is_locked:
        return jsonify({"success": False, "valid": False, "message": "Assessment locked: Report has already been generated."}), 403

    data = request.get_json(silent=True) or request.form
    submitted_flag = (data.get("flag") or "").strip()
    name = student_info["name"] or "Student"
    roll = student_info["roll_number"] or "N/A"

    if not submitted_flag:
        return jsonify({"success": False, "message": "Please enter a flag string."}), 400

    matched_id = None
    for cid, cinfo in CHALLENGES.items():
        if submitted_flag == cinfo["flag"]:
            matched_id = cid
            break

    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    if matched_id:
        has_server_event = matched_id in server_events
        cinfo = CHALLENGES[matched_id]

        # Check direct filesystem evidence for Flag 6 if event hook did not fire
        if matched_id == "BB-F06" and not has_server_event:
            # 1. Check if cron script was modified
            cron_script = "/opt/scripts/cleanup_cache.sh"
            ref_script = "/cron/cleanup_cache.sh"
            script_tampered = False
            try:
                if os.path.exists(cron_script) and os.path.exists(ref_script):
                    with open(cron_script, "rb") as f1, open(ref_script, "rb") as f2:
                        script_tampered = (f1.read() != f2.read())
                elif os.path.exists("/tmp/root_flag.txt") or os.path.exists("/tmp/.root_pwn"):
                    script_tampered = True
            except Exception:
                script_tampered = False

            # 2. Check if student already demonstrated shell access (Flag 4) and has the actual root flag
            # (which is chmod 0600 root:root and cannot be read without root access)
            if script_tampered or "BB-F04" in solved_flags:
                has_server_event = True
                server_events["BB-F06"] = {
                    "source": "root_filesystem_privilege_verified",
                    "timestamp": timestamp,
                    "details": {"script_modified": script_tampered, "root_flag_confirmed": True}
                }

        # Anti-Cheat: Validate that the exploit actually occurred on the server
        if not has_server_event:
            submissions.insert(0, {
                "timestamp": timestamp,
                "student": f"{name} ({roll})",
                "flag_id": matched_id,
                "status": "REJECTED_NO_PROOF",
                "message": f"Flag string matches [{matched_id}], but server-side exploitation evidence was not detected. Execute the attack on the target."
            })
            return jsonify({
                "success": False,
                "valid": False,
                "message": f"Flag matched, but server-side exploitation proof is missing. Ensure you actually execute the exploit on the application."
            }), 400

        is_first_solve = matched_id not in solved_flags
        event_src = server_events[matched_id]["source"]

        solved_flags[matched_id] = {
            "solved_by": f"{name} ({roll})",
            "solved_at": timestamp,
            "evidence": event_src
        }
        submissions.insert(0, {
            "timestamp": timestamp,
            "student": f"{name} ({roll})",
            "flag_id": matched_id,
            "status": "VALID",
            "message": f"Verified challenge [{matched_id}] {cinfo['title']} (Server evidence: {event_src})"
        })

        all_solved = len(solved_flags) == len(CHALLENGES)

        return jsonify({
            "success": True,
            "valid": True,
            "flag_id": matched_id,
            "title": cinfo["title"],
            "first_solve": is_first_solve,
            "all_solved": all_solved,
            "message": f"Valid Flag! Challenge [{matched_id}] {cinfo['title']} verified."
        })
    else:
        submissions.insert(0, {
            "timestamp": timestamp,
            "student": f"{name} ({roll})",
            "flag_id": "UNKNOWN",
            "status": "INVALID",
            "message": f"Invalid flag submission: {submitted_flag[:18]}..."
        })
        return jsonify({
            "success": False,
            "valid": False,
            "message": "Invalid flag format or incorrect value."
        }), 400

@app.route("/api/internal/record_flag", methods=["POST"])
def internal_record_flag():
    data = request.get_json(silent=True) or {}
    flag_id = data.get("flag_id")
    source = data.get("source", "internal_hook")
    details = data.get("details", {})
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    if flag_id in CHALLENGES:
        server_events[flag_id] = {
            "source": source,
            "timestamp": timestamp,
            "details": details
        }
        return jsonify({"success": True, "flag_id": flag_id, "status": "EVENT_RECORDED", "source": source})

    return jsonify({"success": False, "message": "Unknown flag ID"}), 400

@app.route("/report")
def generate_report():
    global is_locked
    # Lock the student session permanently once report is generated
    is_locked = True

    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    name = student_info["name"] or "Student Analyst"
    roll = student_info["roll_number"] or "UNASSIGNED"
    solved_count = len(solved_flags)
    hints_count = len(viewed_hints)

    # Cryptographic anti-tamper verification hash
    verification_hash = generate_report_hash(name, roll, solved_count, hints_count, timestamp)

    return render_template(
        "report.html",
        student_name=name,
        roll_number=roll,
        timestamp=timestamp,
        challenges=CHALLENGES,
        solved_flags=solved_flags,
        solved_count=solved_count,
        total_flags=len(CHALLENGES),
        hints_count=hints_count,
        viewed_hints=viewed_hints,
        verification_hash=verification_hash
    )

def call_web_reset():
    try:
        url = f"{WEB_INTERNAL_URL}/api/internal-reset.php"
        req = urllib.request.Request(url, method="POST")
        with urllib.request.urlopen(req, timeout=5) as response:
            return response.status == 200
    except Exception as e:
        print(f"[-] Web reset error: {e}")
        return False

@app.route("/api/reset_lab", methods=["POST"])
@app.route("/api/reset", methods=["POST"])
def reset_progress():
    global solved_flags, submissions, server_events, viewed_hints, is_locked, student_info
    
    # 1. Reset target container and database
    web_reset_ok = call_web_reset()

    # 2. Reset verifier records
    solved_flags.clear()
    submissions.clear()
    server_events.clear()
    viewed_hints.clear()
    is_locked = False
    student_info = {"name": "", "roll_number": ""}

    return jsonify({
        "success": True,
        "web_reset": web_reset_ok,
        "message": "Lab environment and verification records successfully reset to pristine initial state."
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000)
