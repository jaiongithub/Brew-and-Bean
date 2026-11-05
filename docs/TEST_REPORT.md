# BREW & BEAN – Penetration Testing Verification & Test Report
> **LAB ACCEPTANCE & QA VERIFICATION REPORT**

## 1. Executive Summary
This document provides formal testing and verification evidence for the **BREW & BEAN** vulnerable web application cyber range target. All six target vulnerabilities, corresponding verification flags, container isolation boundaries, and reset mechanisms were systematically evaluated and confirmed functional.

---

## 2. Test Environment Matrix
- **Testing Date**: November 2026
- **Architecture**: Multi-container Docker Compose deployment
- **Services Evaluated**:
  - `brew-web` (Ubuntu 24.04, Apache 2.4, PHP 8.3)
  - `brew-db` (MariaDB 10.11)
  - `brew-staff` (Playwright Chromium Staff Simulation Bot)
  - `brew-verifier` (Python/Flask Verification Engine & Instructor Dashboard)

---

## 3. Vulnerability Verification Details

### 3.1 BB-F01: Username Enumeration & Password Brute Force
- **Endpoint**: `POST /login.php`
- **Test Procedure**:
  1. Sent POST request with non-existent username `ghost_user`. Verified response returned: `"Account verification failed: No user found with username 'ghost_user'."`
  2. Sent POST request with seeded username `alex_morgan` and invalid password. Verified response returned: `"Authentication failed: Invalid password provided for registered user 'alex_morgan'."`
  3. Audited password dictionary; authenticated with `coffee123`.
- **Result**: PASSED
- **Flag Verified**: `FLAG{BREW_USER_ENUMERATION_01}`

---

### 3.2 BB-F02: Horizontal Privilege Escalation (IDOR)
- **Endpoint**: `GET /order-details.php?id=104`
- **Test Procedure**:
  1. Authenticated as user `alex_morgan` (user_id = 1).
  2. Navigated to order #104 belonging to customer `michael_b` (user_id = 3).
  3. Verified that application returned order details and disclosed the confidential catering notes.
  4. Verified that accessing order #104 while logged in as `michael_b` did not disclose the flag callout, ensuring the verification requirement.
- **Result**: PASSED
- **Flag Verified**: `FLAG{BREW_IDOR_02}`

---

### 3.3 BB-F03: Vertical Privilege Escalation
- **Endpoint**: `GET /admin/reports.php`
- **Test Procedure**:
  1. Verified that direct unauthenticated access to `/admin/reports.php` redirects to `/login.php`.
  2. Authenticated as standard customer `alex_morgan`.
  3. Directly browsed to `/admin/reports.php`.
  4. Verified that report data and confidential audit records were rendered without administrative role enforcement.
- **Result**: PASSED
- **Flag Verified**: `FLAG{BREW_VERTICAL_ACCESS_03}`

---

### 3.4 BB-F04: OS Command Injection (Initial Access)
- **Endpoint**: `POST /track-delivery.php`
- **Test Procedure**:
  1. Submitted diagnostic parameter: `127.0.0.1; whoami`
  2. Verified output: `www-data`
  3. Submitted diagnostic parameter: `127.0.0.1; cat /opt/flags/flag4.txt`
  4. Verified that proof file was successfully read and output was displayed on screen.
- **Result**: PASSED
- **Flag Verified**: `FLAG{BREW_COMMAND_INJECTION_04}`

---

### 3.5 BB-F05: Stored Cross-Site Scripting (XSS)
- **Endpoint**: `POST /reviews.php` -> `GET /staff/reviews.php`
- **Test Procedure**:
  1. Submitted customer review containing stored XSS payload:
     `<script>alert('XSS_VERIFIED')</script>`
  2. Verified that review was stored in database without HTML encoding.
  3. Observed automated Playwright staff bot loading `/staff/reviews.php` in authenticated session.
  4. Verified that dialog was intercepted by bot and verification event was registered at `brew-verifier`.
  5. Verified that cookie `staff_secret_token` contained the challenge flag.
- **Result**: PASSED
- **Flag Verified**: `FLAG{BREW_STORED_XSS_05}`

---

### 3.6 BB-F06: Linux Privilege Escalation via Cron
- **Target File**: `/opt/scripts/cleanup_cache.sh` & `/root/flags/root.txt`
- **Test Procedure**:
  1. Inspected crontab file `/etc/cron.d/brew-cron` and identified that `/opt/scripts/cleanup_cache.sh` is executed every minute as root.
  2. Verified permissions on `/opt/scripts/cleanup_cache.sh`: writable by `www-data`.
  3. Verified `/root/flags/root.txt` permissions: `0600` owned by `root:root` (unreadable by `www-data`).
  4. Appended payload to `/opt/scripts/cleanup_cache.sh`:
     `cat /root/flags/root.txt > /tmp/root_flag.txt && chmod 666 /tmp/root_flag.txt`
  5. Waited 60 seconds for cron execution.
  6. Verified that `/tmp/root_flag.txt` was created and contained the root flag.
- **Result**: PASSED
- **Flag Verified**: `FLAG{BREW_ROOT_ESCALATION_06}`

---

## 4. Acceptance Criteria Compliance Matrix

| Section | Acceptance Criterion | Status | Evidence |
|---|---|---|---|
| 12.1 | Docker Deployment | **PASSED** | Multi-container stack builds and launches with single command |
| 12.2 | Website Accessibility | **PASSED** | HTTP web interface accessible on standard port 80 |
| 12.3 | Business Functionality | **PASSED** | Catalog, cart, checkout, order tracking, and reviews functional |
| 12.4 | Username Enumeration | **PASSED** | Differentiated login errors for valid/invalid accounts |
| 12.5 | Password Auditing | **PASSED** | Weak account `alex_morgan` crackable via `rockyou.txt` |
| 12.6 | IDOR | **PASSED** | Sequential order parameter allows cross-customer data access |
| 12.7 | Vertical Access Control | **PASSED** | Restricted `/admin/reports.php` accessible by non-admin accounts |
| 12.8 | OS Command Injection | **PASSED** | Shell execution confirmed under `www-data` on delivery diagnostic tool |
| 12.9 | Stored XSS | **PASSED** | Automated headless staff bot triggers stored script execution |
| 12.10 | Initial Access | **PASSED** | Server execution context established via command injection |
| 12.11 | Cron Vulnerability | **PASSED** | Root-owned script `/opt/scripts/cleanup_cache.sh` writable by `www-data` |
| 12.12 | Privilege Escalation | **PASSED** | Escalation to root inside container verified via scheduled task |
| 12.13 | Flag Verification | **PASSED** | All 6 independent flags confirmed and validated by verifier service |
| 12.14 | Container Isolation | **PASSED** | Target container isolation preserved; database not exposed to host |
| 12.15 | Reset & Reproducibility | **PASSED** | `scripts/reset.sh` restores pristine vulnerable state |

