import os
import json
import datetime
from flask import Flask, request, jsonify, render_template

app = Flask(__name__)

# Configured Challenge Flags
CHALLENGES = {
    "BB-F01": {
        "title": "Username Enumeration & Weak Password",
        "category": "Authentication",
        "description": "Exploit differentiated login error messages and audit weak account credentials.",
        "target": "/login.php",
        "flag": os.getenv("FLAG_F01", "FLAG{BREW_USER_ENUMERATION_01}").strip()
    },
    "BB-F02": {
        "title": "Horizontal Privilege Escalation (IDOR)",
        "category": "Broken Access Control",
        "description": "Manipulate order identifier parameter to view confidential executive customer orders.",
        "target": "/order-details.php?id=104",
        "flag": os.getenv("FLAG_F02", "FLAG{BREW_IDOR_02}").strip()
    },
    "BB-F03": {
        "title": "Vertical Privilege Escalation",
        "category": "Broken Function Level Authorization",
        "description": "Bypass role enforcement to access confidential administrative audit reports.",
        "target": "/admin/reports.php",
        "flag": os.getenv("FLAG_F03", "FLAG{BREW_VERTICAL_ACCESS_03}").strip()
    },
    "BB-F04": {
        "title": "OS Command Injection",
        "category": "Command Injection",
        "description": "Inject operating system commands into delivery gateway diagnostic tool as www-data.",
        "target": "/track-delivery.php",
        "flag": os.getenv("FLAG_F04", "FLAG{BREW_COMMAND_INJECTION_04}").strip()
    },
    "BB-F05": {
        "title": "Stored Cross-Site Scripting (XSS)",
        "category": "Client-Side Injection",
        "description": "Inject JavaScript in customer feedback and execute in authenticated staff browser session.",
        "target": "/reviews.php -> /staff/reviews.php",
        "flag": os.getenv("FLAG_F05", "FLAG{BREW_STORED_XSS_05}").strip()
    },
    "BB-F06": {
        "title": "Linux Privilege Escalation via Cron",
        "category": "Privilege Escalation",
        "description": "Exploit writable root-owned cache maintenance script to obtain root and read flag.",
        "target": "/root/flags/root.txt via /opt/scripts/cleanup_cache.sh",
        "flag": os.getenv("FLAG_F06", "FLAG{BREW_ROOT_ESCALATION_06}").strip()
    }
}

# Runtime submission tracking
submissions = []
solved_flags = {}

@app.route("/")
@app.route("/instructor")
def dashboard():
    return render_template(
        "index.html",
        challenges=CHALLENGES,
        solved_flags=solved_flags,
        submissions=submissions,
        total_flags=len(CHALLENGES),
        solved_count=len(solved_flags)
    )

@app.route("/api/submit_flag", methods=["POST"])
def submit_flag():
    data = request.get_json(silent=True) or request.form
    submitted_flag = (data.get("flag") or "").strip()
    student = (data.get("student") or "Student / Penetration Tester").strip()

    if not submitted_flag:
        return jsonify({"success": False, "message": "No flag submitted."}), 400

    matched_id = None
    for cid, cinfo in CHALLENGES.items():
        if submitted_flag == cinfo["flag"]:
            matched_id = cid
            break

    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    if matched_id:
        is_first_solve = matched_id not in solved_flags
        solved_flags[matched_id] = {
            "solved_by": student,
            "solved_at": timestamp
        }
        submissions.insert(0, {
            "timestamp": timestamp,
            "student": student,
            "flag_id": matched_id,
            "status": "VALID",
            "message": f"Successfully verified challenge {matched_id} ({CHALLENGES[matched_id]['title']})"
        })
        return jsonify({
            "success": True,
            "valid": True,
            "flag_id": matched_id,
            "title": CHALLENGES[matched_id]["title"],
            "first_solve": is_first_solve,
            "message": f"Congratulations! Flag accepted for [{matched_id}] {CHALLENGES[matched_id]['title']}."
        })
    else:
        submissions.insert(0, {
            "timestamp": timestamp,
            "student": student,
            "flag_id": "UNKNOWN",
            "status": "INVALID",
            "message": f"Invalid flag submission: {submitted_flag[:15]}..."
        })
        return jsonify({
            "success": False,
            "valid": False,
            "message": "Invalid flag. Check the syntax and ensure exploitation succeeded."
        }), 400

@app.route("/api/internal/record_flag", methods=["POST"])
def internal_record_flag():
    data = request.get_json(silent=True) or {}
    flag_id = data.get("flag_id")
    source = data.get("source", "internal_hook")
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    if flag_id in CHALLENGES:
        solved_flags[flag_id] = {
            "solved_by": f"Automated Verification ({source})",
            "solved_at": timestamp
        }
        submissions.insert(0, {
            "timestamp": timestamp,
            "student": f"Automated Bot ({source})",
            "flag_id": flag_id,
            "status": "VERIFIED_INTERNAL",
            "message": f"Challenge {flag_id} automatically verified via {source} callback."
        })
        return jsonify({"success": True, "flag_id": flag_id, "status": "RECORDED"})

    return jsonify({"success": False, "message": "Unknown flag ID"}), 400

@app.route("/api/status", methods=["GET"])
def get_status():
    return jsonify({
        "total_challenges": len(CHALLENGES),
        "solved_count": len(solved_flags),
        "solved_flags": solved_flags,
        "recent_submissions": submissions[:10]
    })

@app.route("/api/reset", methods=["POST"])
def reset_progress():
    global solved_flags, submissions
    solved_flags.clear()
    submissions.clear()
    return jsonify({"success": True, "message": "Lab verification records successfully reset."})

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000)

