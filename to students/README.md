# BREW & BEAN – Penetration Testing Cyber Range
## Practical Assessment & Hands-On Examination Manual

Welcome to the **BREW & BEAN** Penetration Testing Examination. This assessment evaluates your practical offensive security skills across a multi-tier enterprise web application and its underlying Linux environment.

---

## 1. Quick Start Guide

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / macOS) or Docker Engine (Linux) installed and running.

### Launching the Lab Environment

#### Option A: Using the Launcher Script (Recommended)
- **Windows:** Double-click `start-lab.bat` (or run `./start-lab.bat` in PowerShell/CMD).
- **macOS / Linux:** Run `chmod +x start-lab.sh && ./start-lab.sh` in Terminal.

#### Option B: Using a Single Docker Command
```bash
docker run -d --name brewandbean -p 80:80 -p 8000:8000 jaiongithub/brewandbean:latest
```

Once running, the lab exposes two web interfaces:
1. **Target Web Application:** [http://localhost](http://localhost) (Port 80)
2. **Verification & Proof Portal:** [http://localhost:8000](http://localhost:8000) (Port 8000)

---

## 2. Examination Workflow

```
┌─────────────────────────┐          ┌─────────────────────────┐
│  Verification Portal    │          │   Target Application    │
│  http://localhost:8000  │          │   http://localhost      │
│                         │          │                         │
│ 1. Enter Name & Roll #  │          │ 2. Audit & Exploit      │
│ 4. Submit Proof Flags   │ ◄─────── │    using wordlists      │
│ 5. Generate PDF Report  │          │ 3. Capture Flags        │
└─────────────────────────┘          └─────────────────────────┘
```

1. **Step 1: Student Identification**
   - Navigate to [http://localhost:8000](http://localhost:8000).
   - Enter your **Student Name** and **Roll Number** to initialize your examination session.

2. **Step 2: Penetration Testing**
   - Access the target web application at [http://localhost](http://localhost).
   - Use the provided wordlists in the `wordlists/` folder (`usernames.txt` and `passwords.txt`) to audit authentication mechanisms.
   - Investigate access controls, administrative portals, server diagnostics, customer feedback channels, and scheduled maintenance jobs.

3. **Step 3: Submitting Proof Flags**
   - For every compromised objective, copy the captured flag in format `FLAG{BREW_...}`.
   - Paste and verify the flag into the **Verification Portal** at [http://localhost:8000](http://localhost:8000).
   - *Note:* The portal verifies actual server-side exploitation evidence. Flags must be obtained through live execution against the application.

4. **Step 4: Hints Policy**
   - If you are stuck, you can unlock progressive hints on [http://localhost:8000](http://localhost:8000).
   - Opened hints are recorded and tallied in your final assessment audit report.

5. **Step 5: Report Generation & Submission**
   - When all objectives are achieved (or when your allotted examination time expires), click **"Generate Report"**.
   - Your official assessment report will open in a new tab with an embedded cryptographic verification signature.
   - Use your browser's Print function (`Ctrl + P` or `Cmd + P`) to **"Save as PDF"**.
   - Submit the PDF file to your instructor.

---

## 3. Stopping and Resetting the Lab

- **Resetting the Lab to Fresh State:** Click the red **"Reset Lab"** button on the Verification Portal or in the web application footer. This resets all databases and sessions back to initial state.
- **Stopping the Container:**
  - Run `stop-lab.bat` (Windows) or `./stop-lab.sh` (Linux/macOS), or run:
  ```bash
  docker stop brewandbean && docker rm brewandbean
  ```

---

## 4. Provided Assets & Wordlists
The `wordlists/` folder contains:
- `usernames.txt` — Candidate usernames for authentication auditing (Flag 1).
- `passwords.txt` — Candidate passwords for authentication auditing (Flag 1).
