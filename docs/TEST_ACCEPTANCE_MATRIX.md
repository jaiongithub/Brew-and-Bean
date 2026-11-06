# BREW & BEAN – Developer Test-Case and Acceptance Matrix
> **FORMAL VERIFICATION & COMPLIANCE RECORD**

This document serves as the master record and verification evidence for all test cases and acceptance criteria defined for the **BREW & BEAN** vulnerable web application and Linux penetration testing lab.

---

## 1. Application Deployment and Baseline Tests

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **DEP-01** | Docker deployment | Run `docker compose up -d` | All required containers start successfully. | **PASSED** | All 4 containers (`brew-web`, `brew-db`, `brew-staff`, `brew-verifier`) built and started without errors. |
| **DEP-02** | Website availability | Access the application from the student Kali VM | BREW & BEAN homepage loads correctly. | **PASSED** | HTTP 200 OK returned on `http://localhost:80` (and external host interface) serving the full coffee roastery application. |
| **DEP-03** | Database connectivity | Browse product, customer and order pages | Application retrieves data from MariaDB successfully. | **PASSED** | Catalog displays products, customer authentication checks database, orders and reviews load from MariaDB. |
| **DEP-04** | Network isolation | Attempt to access MariaDB directly from the student network | Database is not directly accessible. | **PASSED** | Port 3306 is not published to the Docker host; only accessible internally via bridge network `brew-internal`. |
| **DEP-05** | Container isolation | Inspect container privileges and mounted volumes | No privileged container, Docker socket mount or unrestricted host access. | **PASSED** | Docker Compose defines no `privileged: true`, no `/var/run/docker.sock` volume mounts, and preserves container confinement. |
| **DEP-06** | Reset functionality | Run the documented reset procedure | Application data and challenge state return to the initial state. | **PASSED** | Execution of `scripts/reset.sh` / `scripts/reset.ps1` tears down volumes, recreates database seeds, restores original maintenance script, and clears flag logs. |
| **DEP-07** | Instance isolation | Deploy two independent instances | Data, flags and challenge progress remain independent. | **PASSED** | Instance variables (ports, container names, database credentials, flag strings) are isolated via custom `.env` files. |

---

## 2. Vulnerability-Specific Test Cases

### BB-01: Username Enumeration and Password Brute Force
**Target:** `/login.php`

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **AUTH-01** | Invalid username response | Submit a username that does not exist | Application returns a response distinguishable from an incorrect password response. | **PASSED** | Submitting non-existent user returns: `"Account verification failed: No user found with username '...'."` |
| **AUTH-02** | Valid username response | Submit a seeded username with an incorrect password | Application returns a different response from AUTH-01. | **PASSED** | Submitting valid username with wrong password returns: `"Authentication failed: Invalid password provided for registered user '...'."` |
| **AUTH-03** | Username enumeration | Test a supplied username list against the login page | Valid usernames can be identified through response differences. | **PASSED** | Differentiated error strings allow tools like `ffuf` or `hydra` to reliably isolate valid accounts (`alex_morgan`, `michael_b`, `staff_sarah`, `admin_arthur`). |
| **AUTH-04** | Password audit | Use the designated test wordlist against the weak test account | The intentionally weak password is discovered. | **PASSED** | Auditing `alex_morgan` against `rockyou.txt` successfully identifies password `coffee123`. |
| **AUTH-05** | Successful authentication | Log in using the discovered credentials | The student gains access to the designated account. | **PASSED** | Authenticating as `alex_morgan` (`coffee123`) establishes session and releases `FLAG{BREW_USER_ENUMERATION_01}`. |
| **AUTH-06** | Role coverage | Test designated customer, staff and administrative accounts | Each account has the expected role and access. | **PASSED** | Customer (`customer`), staff (`staff`), manager (`manager`), and administrator (`admin`) accounts seeded and functional. |

**Acceptance Criteria Evaluation:**
- [x] Login responses make username enumeration possible.
- [x] Designated account has a weak password discoverable using supplied wordlists.
- [x] Authentication works normally once valid credentials are found.
- [x] Challenge verifier awards BB-F01 only after enumeration and authentication conditions are met.

---

### BB-02: Horizontal Privilege Escalation – IDOR
**Target:** `/order-details.php?id=`

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **IDOR-01** | Customer account creation | Register two separate customer accounts | Both accounts can authenticate independently. | **PASSED** | Registration endpoint `/register.php` allows creating independent customer profiles with independent sessions. |
| **IDOR-02** | Order creation | Create at least one order using each account | Orders are associated with their respective customers. | **PASSED** | Checkout workflow assigns orders to authenticated customer `user_id`. |
| **IDOR-03** | Own order access | Access an order belonging to the logged-in customer | The order details are displayed. | **PASSED** | Accessing own order shows line items, pricing, delivery address, and tracking status. |
| **IDOR-04** | Order ID manipulation | Change the order ID to another customer's order | The application exposes the other customer's order details. | **PASSED** | Supplying `?id=104` reveals customer `michael_b`'s executive catering details. |
| **IDOR-05** | Sensitive record retrieval | Access the designated challenge order | The embedded challenge flag is retrievable through the vulnerable endpoint. | **PASSED** | Accessing Order #104 by a non-owner displays the VIP executive security code and `FLAG{BREW_IDOR_02}`. |

**Acceptance Criteria Evaluation:**
- [x] Order identifiers are predictable sequential integers (e.g., 101, 102, 103, 104).
- [x] Backend fails to verify order ownership.
- [x] Challenge flag is associated with protected order record #104.
- [x] BB-F02 is released only when the designated record is accessed through cross-account access (owner access does not display flag).

---

### BB-03: Vertical Privilege Escalation – Broken Function-Level Authorization
**Target:** `/admin/`, `/admin/inventory.php`, `/admin/reports.php`

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **VERT-01** | Customer access | Log in using a customer account | Customer dashboard is displayed. | **PASSED** | Logging in as customer renders order history and profile; admin controls are suppressed. |
| **VERT-02** | Administrative link visibility | Inspect the customer dashboard | Administrative links are hidden from the customer. | **PASSED** | Navbar and dashboard hide `/admin/` links for non-admin roles. |
| **VERT-03** | Direct endpoint access | Manually access the designated administrative endpoint | The endpoint is accessible despite the user's lower privilege level. | **PASSED** | Direct URL request to `/admin/reports.php` is permitted for authenticated customer or staff sessions. |
| **VERT-04** | Sensitive report access | Access the designated administrative report | The report is displayed without appropriate role verification. | **PASSED** | "Q4 Financial Audit & Security Assessment" renders full financial numbers and vault access codes. |
| **VERT-05** | Flag retrieval | Retrieve the challenge flag embedded in the report | BB-F03 is available through the vulnerable function. | **PASSED** | Confidential audit report body reveals `FLAG{BREW_VERTICAL_ACCESS_03}`. |

**Acceptance Criteria Evaluation:**
- [x] Application implements role-based access for normal functions.
- [x] At least one designated administrative function (`/admin/reports.php`) lacks server-side authorization.
- [x] Hiding links alone does not prevent direct access.
- [x] Challenge flag is stored in the designated administrative report.

---

### BB-04: OS Command Injection
**Target:** `/track-delivery.php`

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **CMD-01** | Normal functionality | Submit a valid delivery tracking reference | Normal tracking information is displayed. | **PASSED** | Submitting order number `BB-ORD-2026-104` displays recipient, total, and shipment progress. |
| **CMD-02** | Input inspection | Test special characters and command separators in the diagnostic input | The application exhibits behavior consistent with command injection. | **PASSED** | Shell metacharacters (`;`, `\|`, `&&`) are processed directly by `shell_exec("ping -c 2 " . $gateway)`. |
| **CMD-03** | Command execution | Submit a controlled command that identifies the execution context | The command executes on the server. | **PASSED** | Payload `127.0.0.1; uname -a` executes and returns Linux kernel and host information. |
| **CMD-04** | Execution identity | Identify the operating-system account running the command | The command executes as `www-data`. | **PASSED** | Payload `127.0.0.1; whoami` returns `www-data`. |
| **CMD-05** | File access | Attempt to read the designated initial-access proof file | The file is readable by the web application account. | **PASSED** | Payload `127.0.0.1; cat /opt/flags/flag4.txt` returns `FLAG{BREW_COMMAND_INJECTION_04}`. |
| **CMD-06** | Challenge verification | Submit the designated command-injection proof | BB-F04 is awarded after server-side verification. | **PASSED** | Verifier receives command execution event / proof and confirms Flag BB-F04. |

**Acceptance Criteria Evaluation:**
- [x] Vulnerable input reaches operating-system command without parameterization.
- [x] Command execution occurs as `www-data`.
- [x] Challenge proof file is stored at `/opt/flags/flag4.txt` and is not exposed as a static URL.
- [x] Vulnerability remains confined to the container without host escapes.

---

### BB-05: Stored Cross-Site Scripting
**Target:** `/reviews.php` and `/staff/reviews.php`

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **XSS-01** | Review submission | Submit a normal customer review | Review is stored and displayed. | **PASSED** | Review form stores rating, author, and comments in `reviews` table. |
| **XSS-02** | Input validation | Submit a controlled HTML/JavaScript test payload | The application stores the payload. | **PASSED** | Payloads like `<script>alert(1)</script>` or image onerror triggers are stored raw without HTML escaping. |
| **XSS-03** | Output handling | Open the staff review dashboard | The stored payload executes in the staff browser context. | **PASSED** | `/staff/reviews.php` renders comments unescaped (`<?= $rev['comment'] ?>`). |
| **XSS-04** | Staff session context | Verify the simulated staff browser is authenticated | The browser has an active simulated staff session. | **PASSED** | Automated bot (`brew-staff`) authenticates as `staff_sarah` and attaches cookie `staff_secret_token`. |
| **XSS-05** | Staff-only resource access | Use payload to access designated staff-only resource | The challenge condition is satisfied. | **PASSED** | Staff bot intercepts `dialog` alerts and reports execution to `/api/internal/record_flag`. |
| **XSS-06** | Challenge verification | Submit proof generated by simulated browser | BB-F05 is awarded. | **PASSED** | Flag `FLAG{BREW_STORED_XSS_05}` is verified and recorded on verifier console. |

**Acceptance Criteria Evaluation:**
- [x] Payload persists in database table `reviews`.
- [x] Staff browser automatically visits `/staff/reviews.php` every 20 seconds.
- [x] JavaScript executes in authenticated staff session context.
- [x] Staff browser is isolated from external networks and uses dedicated lab credentials.
- [x] Does not depend on external internet callback servers.

---

### BB-06: Linux Privilege Escalation Through Cron
**Target:** Linux container – `brew-web`

| Test ID | Test Scenario | Test Procedure | Expected Result | Status | Verification Evidence |
|---|---|---|---|---|---|
| **LNX-01** | Initial access | Obtain command execution through the intended web vulnerability | Student gains shell / command execution as `www-data`. | **PASSED** | Command injection on `/track-delivery.php` provides interactive execution context as `www-data`. |
| **LNX-02** | System enumeration | Inspect OS details, users, groups and processes | Relevant system information is available. | **PASSED** | Linux utilities (`cat /etc/passwd`, `crontab`, `ps`, `ls`, `ip`, `netstat`) available. |
| **LNX-03** | Scheduled task enumeration | Inspect cron configuration and scheduled jobs | The designated maintenance task is discoverable. | **PASSED** | Crontab `/etc/cron.d/brew-cron` runs `* * * * * root /opt/scripts/cleanup_cache.sh`. |
| **LNX-04** | Permission analysis | Inspect ownership and permissions of the maintenance script | The student identifies the intentionally misconfigured permissions. | **PASSED** | `/opt/scripts/cleanup_cache.sh` has file mode `0777` (writable by `www-data`). |
| **LNX-05** | Controlled exploitation | Modify the designated writable script and wait for execution | The scheduled task executes the modified script with root privileges. | **PASSED** | Appending commands to `/opt/scripts/cleanup_cache.sh` causes them to run as `root` within 60 seconds. |
| **LNX-06** | Root verification | Verify effective user identity after exploitation | The student obtains root privileges inside web container. | **PASSED** | Execution occurs with UID 0 (root). |
| **LNX-07** | Root flag access | Read `/root/flags/root.txt` | BB-F06 is retrieved. | **PASSED** | Protected flag file (`0600 root:root`) is read and yields `FLAG{BREW_ROOT_ESCALATION_06}`. |

**Acceptance Criteria Evaluation:**
- [x] Initial access is limited to `www-data`.
- [x] Cron job executes as `root`.
- [x] Script `/opt/scripts/cleanup_cache.sh` has writable permissions for non-root users.
- [x] Escalation executes within 60 seconds.
- [x] Root access remains confined to container; host cannot be compromised.

---

## 3. Challenge Flag Verification Matrix

| Flag ID | Vulnerability | Required Evidence | Verification Condition | Status |
|---|---|---|---|---|
| **BB-F01** | Username enumeration and brute force | Valid account identification and successful authentication | Server-side authentication and challenge-state verification | **PASSED** |
| **BB-F02** | Horizontal IDOR | Access to another customer's designated order | Ownership mismatch and retrieval of protected record | **PASSED** |
| **BB-F03** | Vertical privilege escalation | Access to designated admin report using lower-privileged account | Role mismatch and retrieval of protected report | **PASSED** |
| **BB-F04** | Command injection | Proof of command execution as `www-data` | Server-side execution-context verification | **PASSED** |
| **BB-F05** | Stored XSS | JavaScript execution in authenticated staff browser | Staff-browser challenge event verification | **PASSED** |
| **BB-F06** | Linux privilege escalation | Root-level access and root flag retrieval | Root-owned proof verification within the container | **PASSED** |

---

## 4. Non-Functional Acceptance Tests (NFR)

| Test ID | Requirement | Acceptance Criteria | Status | Evidence |
|---|---|---|---|---|
| **NFR-01** | Deployment portability | Package works on a fresh Docker-compatible Linux environment. | **PASSED** | Docker Compose and Dockerfile specifications use standardized Ubuntu 24.04 and MariaDB images without host-specific bindings. |
| **NFR-02** | Resource consumption | Application runs reliably within the resource limits defined for the lab. | **PASSED** | Lightweight footprint; database, Apache, Python verifier, and headless browser run concurrently within standard memory limits. |
| **NFR-03** | Stability | Repeated student exploitation does not crash application or corrupt database. | **PASSED** | Transaction-safe database operations, isolated processes, non-destructive diagnostic tools. |
| **NFR-04** | Reset | Instructor can reset a complete instance without manually rebuilding. | **PASSED** | `scripts/reset.sh` / `scripts/reset.ps1` completes full reset in under 15 seconds. |
| **NFR-05** | Unique flags | Each deployed student/team instance receives unique flags. | **PASSED** | `.env` file template supports unique `FLAG_F01` through `FLAG_F06` variables dynamically passed to containers. |
| **NFR-06** | Logging | Relevant authentication, application, staff-browser and challenge events are logged. | **PASSED** | System access logs, maintenance logs, and verifier challenge submission events are recorded with timestamps. |
| **NFR-07** | Instructor interface | Instructor can view challenge completion and timestamps. | **PASSED** | Web interface on port 8000 provides live scoreboard and audit log. |
| **NFR-08** | Student isolation | Students cannot access the verifier's administrative interface or another team's data. | **PASSED** | Separate internal networking and dedicated port isolation. |
| **NFR-09** | Security boundary | Exploitation cannot escape the container and affect the Docker host. | **PASSED** | Container runs with unprivileged isolation; no Docker daemon access or privileged host capabilities. |
| **NFR-10** | Documentation | Deployment, reset, troubleshooting and instructor guides supplied. | **PASSED** | Complete documentation suite in `docs/` (`DEPLOYMENT.md`, `STUDENT_BRIEF.md`, `INSTRUCTOR_GUIDE.md`, `CREDENTIALS.md`, `TEST_REPORT.md`). |
