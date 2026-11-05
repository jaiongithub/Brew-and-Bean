# BREW & BEAN – Deployment and Operations Guide

## 1. System Requirements
- **Host OS**: Linux (Ubuntu 22.04/24.04, Debian 12), macOS, or Windows 10/11 with WSL2 / Docker Desktop.
- **Docker Engine**: Version 24.0 or higher.
- **Docker Compose**: Version 2.20 or higher (`docker compose`).
- **Network Ports Available on Host**:
  - `80/TCP`: HTTP Web Application
  - `8000/TCP`: Flag Verifier and Instructor Dashboard

---

## 2. Quick Start Deployment

### On Linux / macOS / Kali:
```bash
# 1. Clone or navigate to the repository directory
cd "2. Brew and Bean"

# 2. Run the deployment script
chmod +x scripts/deploy.sh scripts/reset.sh
./scripts/deploy.sh
```

### On Windows PowerShell:
```powershell
# Navigate to the project root directory
Set-Location "2. Brew and Bean"

# Execute the deployment PowerShell script
powershell -ExecutionPolicy Bypass -File scripts\deploy.ps1
```

### Alternatively, using standard Docker Compose directly:
```bash
# Copy default environment configuration
cp .env.example .env

# Launch all 4 services in the background
docker compose up -d --build
```

---

## 3. Container Topology & Port Allocation

| Service Name | Container Name | Internal IP / Host | Host Port | Purpose |
|---|---|---|---|---|
| `brew-web` | `brew-web` | `http://brew-web:80` | `80:80` | Apache 2.4 + PHP 8.3 target web app & Linux attack surface |
| `brew-db` | `brew-db` | `brew-db:3306` | *None (Internal)* | MariaDB database holding seed data and orders |
| `brew-staff` | `brew-staff` | `brew-staff` | *None (Internal)* | Headless Playwright Chromium simulating staff interactions |
| `brew-verifier` | `brew-verifier` | `http://brew-verifier:8000` | `8000:8000` | Isolated challenge validation engine & instructor portal |

---

## 4. Environment Configuration (`.env`)

Custom settings can be adjusted in `.env`:
```ini
# Port Bindings
WEB_PORT=80
VERIFIER_PORT=8000

# Staff Bot Inspection Interval (seconds)
STAFF_BOT_INTERVAL=20

# Challenge Flag Customization
FLAG_F01=FLAG{BREW_USER_ENUMERATION_01}
FLAG_F02=FLAG{BREW_IDOR_02}
FLAG_F03=FLAG{BREW_VERTICAL_ACCESS_03}
FLAG_F04=FLAG{BREW_COMMAND_INJECTION_04}
FLAG_F05=FLAG{BREW_STORED_XSS_05}
FLAG_F06=FLAG{BREW_ROOT_ESCALATION_06}
```

---

## 5. Environment Reset and Teardown

To reset the lab to its pristine vulnerable state:

### Linux / Kali:
```bash
./scripts/reset.sh
```

### Windows:
```powershell
powershell -ExecutionPolicy Bypass -File scripts\reset.ps1
```

To completely remove all containers, images, and networks:
```bash
docker compose down -v --rmi all
```

