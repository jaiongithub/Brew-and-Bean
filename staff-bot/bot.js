const { chromium } = require('playwright');

const BASE_URL = process.env.WEB_INTERNAL_URL || 'http://brew-web';
const VERIFIER_URL = process.env.VERIFIER_INTERNAL_URL || 'http://brew-verifier:8000';
const STAFF_USER = process.env.STAFF_USER || 'staff_sarah';
const STAFF_PASS = process.env.STAFF_PASS || 'S@r@hSt@ff2026!';
const FLAG_F05 = process.env.FLAG_F05 || 'FLAG{BREW_STORED_XSS_05}';
const CHECK_INTERVAL = parseInt(process.env.STAFF_BOT_INTERVAL || '20', 10) * 1000;

async function notifyVerifier(triggerReason) {
    try {
        console.log(`[+] Alerting verifier container: Stored XSS demonstrated (${triggerReason})`);
        const res = await fetch(`${VERIFIER_URL}/api/internal/record_flag`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                flag_id: 'BB-F05',
                source: 'staff_simulation_browser',
                reason: triggerReason,
                timestamp: new Date().toISOString()
            })
        });
        const data = await res.json();
        console.log('[+] Verifier response:', data);
    } catch (err) {
        console.warn('[-] Could not notify verifier:', err.message);
    }
}

async function runStaffSimulation() {
    console.log('[*] Starting BREW & BEAN Staff Browser Simulation Service...');
    console.log(`[*] Target URL: ${BASE_URL} | Interval: ${CHECK_INTERVAL / 1000}s`);

    let browser;
    try {
        browser = await chromium.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
        });
    } catch (err) {
        console.error('[-] Failed to launch Chromium browser:', err);
        setTimeout(runStaffSimulation, 10000);
        return;
    }

    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/StaffReviewBot'
    });

    // Set staff flag cookie in context
    await context.addCookies([
        {
            name: 'staff_secret_token',
            value: FLAG_F05,
            domain: new URL(BASE_URL).hostname,
            path: '/',
            httpOnly: false
        }
    ]);

    const page = await context.newPage();

    // Monitor for XSS execution via dialogs (alert, confirm, prompt)
    page.on('dialog', async dialog => {
        console.log(`[!] [XSS ALERT TRIGGERED] Dialog Type: ${dialog.type()}, Content: "${dialog.message()}"`);
        await notifyVerifier(`Dialog payload: ${dialog.message()}`);
        await dialog.accept();
    });

    // Monitor for console errors or logs indicating exploitation
    page.on('console', async msg => {
        const text = msg.text();
        if (text.includes('FLAG') || text.includes('XSS') || text.includes('xss') || text.includes('exploit')) {
            console.log(`[!] [XSS CONSOLE LOG] ${text}`);
            await notifyVerifier(`Console log: ${text}`);
        }
    });

    async function loginStaff() {
        console.log(`[*] Authenticating staff session as "${STAFF_USER}"...`);
        try {
            await page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle', timeout: 15000 });
            await page.fill('input[name="username"]', STAFF_USER);
            await page.fill('input[name="password"]', STAFF_PASS);
            await page.click('button[type="submit"]');
            await page.waitForTimeout(2000);
            console.log('[+] Staff login completed.');
            return true;
        } catch (err) {
            console.warn('[-] Staff login attempt failed (server may still be starting):', err.message);
            return false;
        }
    }

    // Main polling cycle
    async function cycle() {
        try {
            // Ensure logged in
            const currentUrl = page.url();
            if (!currentUrl.includes('/staff/')) {
                const loggedIn = await loginStaff();
                if (!loggedIn) {
                    setTimeout(cycle, 10000);
                    return;
                }
            }

            console.log(`[*] [${new Date().toLocaleTimeString()}] Staff visiting /staff/reviews.php to inspect pending feedback...`);
            await page.goto(`${BASE_URL}/staff/reviews.php`, { waitUntil: 'load', timeout: 15000 });

            // Allow 6 seconds for any injected scripts/images to execute
            await page.waitForTimeout(6000);
            console.log('[*] Review moderation cycle finished successfully.');
        } catch (err) {
            console.warn('[-] Error in staff simulation cycle:', err.message);
        }

        setTimeout(cycle, CHECK_INTERVAL);
    }

    // Initial wait to let brew-web start
    setTimeout(cycle, 5000);
}

runStaffSimulation();

