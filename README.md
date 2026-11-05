# BREW & BEAN – Cyber Range Penetration Testing Lab

**BREW & BEAN** is a self-contained, realistic, multi-container Docker penetration testing cyber range target representing an artisanal specialty coffee roastery and online ordering platform.

The environment simulates an end-to-end penetration testing engagement spanning web application reconnaissance, authentication auditing, access control flaws, client-side injection, operating system command injection, initial server access, and container Linux privilege escalation to root.

---

## Architecture Overview

```
                      +---------------------------------------+
                      |             Docker Host               |
                      +---------------------------------------+
                             |                         |
               Host Port 80  |                         | Host Port 8000
                             v                         v
                   +-------------------+     +--------------------+
                   |     brew-web      |     |   brew-verifier    |
                   | (Apache + PHP 8.3)|     |  (Python / Flask)  |
                   | Target Web Server |     | Instructor Console |
                   +-------------------+     +--------------------+
                             |                         ^
              Internal Net   |                         | Internal API
                             v                         |
                   +-------------------+     +--------------------+
                   |      brew-db      |     |     brew-staff     |
                   | (MariaDB 10.11)   |     |    (Playwright     |
                   | Seed Data & Orders|     |    Chromium Bot)   |
                   +-------------------+     +--------------------+
```

---

## The 6 Target Vulnerabilities & Flags

| Flag ID | Logical Flag String | Vulnerability & Endpoint | Exploitation Objective |
|---|---|---|---|
| **BB-F01** | `FLAG{BREW_USER_ENUMERATION_01}` | **Username Enumeration & Password Brute-Force** (`/login.php`) | Differentiated login errors reveal registered accounts; audit weak account `alex_morgan` (`coffee123`). |
| **BB-F02** | `FLAG{BREW_IDOR_02}` | **Horizontal IDOR** (`/order-details.php?id=`) | Unvalidated order ID parameter discloses confidential executive catering orders. |
| **BB-F03** | `FLAG{BREW_VERTICAL_ACCESS_03}` | **Broken Function-Level Authorization** (`/admin/reports.php`) | Bypassed administrative role checks disclose classified corporate audit reports. |
| **BB-F04** | `FLAG{BREW_COMMAND_INJECTION_04}` | **OS Command Injection** (`/track-delivery.php`) | Delivery gateway diagnostic tool executes arbitrary shell commands under `www-data`. |
| **BB-F05** | `FLAG{BREW_STORED_XSS_05}` | **Stored Cross-Site Scripting (XSS)** (`/reviews.php` -> `/staff/reviews.php`) | Injected script executes inside automated headless staff browser session reviewing customer feedback. |
| **BB-F06** | `FLAG{BREW_ROOT_ESCALATION_06}` | **Linux Privilege Escalation via Cron** | Misconfigured permissions on root maintenance script (`/opt/scripts/cleanup_cache.sh`) allow `www-data` to hijack execution. |

---

## Quick Start Deployment

### 1. Launch Environment
```bash
# On Linux / macOS / Kali:
chmod +x scripts/deploy.sh scripts/reset.sh
./scripts/deploy.sh

# On Windows PowerShell:
powershell -ExecutionPolicy Bypass -File scripts\deploy.ps1
```

Or using Docker Compose directly:
```bash
docker compose up -d --build
```

### 2. Access Lab Endpoints
- **Coffee Shop Web Application**: [http://localhost:80](http://localhost:80)
- **Flag Verifier & Instructor Portal**: [http://localhost:8000](http://localhost:8000)

### 3. Reset Environment
```bash
# Linux / macOS:
./scripts/reset.sh

# Windows:
powershell -ExecutionPolicy Bypass -File scripts\reset.ps1
```

---

## Documentation Index

- [Student Penetration Testing Brief](docs/STUDENT_BRIEF.md): Engagement scope, objectives, and rules of engagement (no spoilers).
- [Instructor Exploitation Guide](docs/INSTRUCTOR_GUIDE.md): Technical walkthroughs, payloads, and solutions for all 6 flags.
- [Seeded Credentials Sheet](docs/CREDENTIALS.md): List of pre-seeded test accounts and passwords.
- [Deployment & Operations Guide](docs/DEPLOYMENT.md): Detailed installation, configuration, and troubleshooting notes.
- [Test & Verification Report](docs/TEST_REPORT.md): Acceptance testing results and evidence.

