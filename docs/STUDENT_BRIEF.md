# PENETRATION TESTING ENGAGEMENT BRIEF
## Target: BREW & BEAN Specialty Roasters Platform

### 1. Engagement Overview
You have been commissioned by the board of directors of **BREW & BEAN**—a rapidly growing specialty coffee roastery based in Seattle, Washington—to perform an authorized black-box web application and infrastructure penetration test.

The organization recently launched a customer ordering and barista fulfillment web portal. Management is concerned about the exposure of customer order privacy, administrative controls, and the security posture of their Linux production container deployment.

---

### 2. Scope of Assessment
- **Target URL**: `http://<TARGET_IP>:80/`
- **Application Type**: Online Coffee Roastery & Delivery Fulfillment Platform
- **Scope Boundaries**:
  - All web application routes under the web application domain.
  - The underlying Linux container host context accessible through application-level vulnerabilities.
  - Verification flags submission portal: `http://<TARGET_IP>:8000/`

---

### 3. Rules of Engagement & Constraints
1. **Testing Boundaries**: All assessment activity must be confined to the containerized environment. Do not attempt attacks against the underlying physical virtualization host or Docker daemon.
2. **Denial of Service**: Do not perform volumetric Denial-of-Service (DoS) or resource exhaustion floods against the web server or database.
3. **Evidence Collection**: Record all exploitation vectors, request/response headers, injection payloads, and proof files for inclusion in your final assessment report.
4. **Flag Capture Verification**: The target environment includes 6 individual challenge verification flags corresponding to critical security flaws across the attack chain.

---

### 4. Penetration Testing Objectives
Your engagement is structured along a progressive penetration testing lifecycle:

1. **Reconnaissance & Account Enumeration**:
   - Map public and customer-accessible functionality.
   - Investigate authentication interfaces for information disclosure or enumeration weaknesses.
   - Evaluate password auditing resilience against standard credential lists.

2. **Access Control & Authorization Analysis**:
   - Evaluate horizontal boundaries between registered customer accounts and order histories.
   - Analyze administrative consoles and endpoints for broken function-level authorization.

3. **Application Injection Assessment**:
   - Assess input sanitation and output encoding across customer feedback and review systems.
   - Test operational logistics and diagnostic utilities for command execution vulnerabilities.

4. **Initial Access & System Enumeration**:
   - Establish initial command execution on the Linux target under the service pool account.
   - Conduct thorough Linux system enumeration (users, permissions, cron jobs, file systems).

5. **Privilege Escalation**:
   - Identify misconfigured system services or scheduled tasks running with elevated privileges.
   - Escalate privileges to the `root` administrative context and retrieve the system proof file.

---

### 5. Verification Flag Format
All valid flags conform to the standard syntax:
```
FLAG{BREW_...}
```
Submit discovered flags at the verification terminal (`http://<TARGET_IP>:8000`) to record evidence of exploitation.

