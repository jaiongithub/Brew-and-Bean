# BREW & BEAN – Instructor Credentials Reference Sheet
> **CONFIDENTIAL – INSTRUCTOR / EVALUATOR EYES ONLY**

This document contains seeded credentials, account roles, and database records configured within the BREW & BEAN laboratory environment.

---

## 1. Seeded User Accounts

| ID | Username | Role | Password | Notes & Vulnerability Context |
|---|---|---|---|---|
| 1 | `alex_morgan` | Customer | `coffee123` | **BB-F01 Target**: Deliberately weak password present in standard `rockyou.txt`. Logs in and unlocks BB-F01 flag. |
| 2 | `emma_watson` | Customer | `Emm@Secure2026!` | Regular customer account with normal orders. |
| 3 | `michael_b` | Customer | `M!ch@elB3an2026` | **BB-F02 Target**: Owner of confidential Order #104 containing the IDOR challenge flag. |
| 4 | `sophia_chen` | Customer | `S0ph!aCh3n#99` | Customer account with completed orders. |
| 5 | `david_kim` | Customer | `D@v!dK1m_C0ff33` | Customer account with pending orders. |
| 6 | `rachel_green` | Customer | `R@ch3lGr33n$77` | Customer account with past reviews. |
| 7 | `staff_sarah` | Staff (Barista) | `S@r@hSt@ff2026!` | **BB-F05 Target**: Account used by the automated headless browser (`brew-staff`) to review customer feedback. |
| 8 | `staff_liam` | Staff (Barista) | `L!@mB@r!st@88` | Active staff barista account. |
| 9 | `staff_olivia` | Staff (Barista) | `0l!v!a_St@ff#44` | Active staff counter operations account. |
| 10 | `mgr_robert` | Manager | `M@n@g3rR0b#2026` | Store general manager account. |
| 11 | `admin_arthur` | Administrator | `Art#ur_Adm!n2026$X` | **BB-F03 Context**: Creator of confidential reports. Full access to admin console. |

---

## 2. Database Service Credentials (Internal)

- **Database Host**: `brew-db` (Port `3306` inside internal network `brew-internal`)
- **Database Name**: `brewandbean`
- **Application User**: `brewuser`
- **Application Password**: `BrewBeanSecurePass2026!`
- **Root Password**: `RootBrewPass2026!`

---

## 3. Challenge Flag Mapping

| Flag ID | Logical Flag String | Location / Exploitation Trigger |
|---|---|---|
| **BB-F01** | `FLAG{BREW_USER_ENUMERATION_01}` | Released upon successful login of `alex_morgan` (`coffee123`) via `/login.php`. |
| **BB-F02** | `FLAG{BREW_IDOR_02}` | Displayed on `/order-details.php?id=104` when accessed by any authenticated user other than `michael_b`. |
| **BB-F03** | `FLAG{BREW_VERTICAL_ACCESS_03}` | Displayed inside `/admin/reports.php` when accessed by a non-admin authenticated user. |
| **BB-F04** | `FLAG{BREW_COMMAND_INJECTION_04}` | Stored on filesystem at `/opt/flags/flag4.txt` (readable by `www-data` via command injection on `/track-delivery.php`). |
| **BB-F05** | `FLAG{BREW_STORED_XSS_05}` | Injected via `/reviews.php` and executed by automated staff browser visiting `/staff/reviews.php` (stored in staff cookie `staff_secret_token` and verified via `/api/staff-verify.php`). |
| **BB-F06** | `FLAG{BREW_ROOT_ESCALATION_06}` | Stored at `/root/flags/root.txt` with permissions `0600` (readable only by root after hijacking `/opt/scripts/cleanup_cache.sh`). |

