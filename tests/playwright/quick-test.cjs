const { chromium } = require('playwright');
const BASE = 'http://localhost:8080/smart-kantin-iteba/public';
const PASS = 'password';

async function test() {
    const browser = await chromium.launch({ headless: true });

    async function runTests(label, email, isPenjual) {
        const ctx = await browser.newContext();
        const page = await ctx.newPage();
        let ok = 0, fail = 0;
        const errors = [];
        page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
        page.on('response', r => { if (r.status() >= 400) errors.push(r.status() + ' ' + r.url()); });
        function check(name, cond) {
            if (cond) { ok++; console.log('  ? [' + label + '] ' + name); }
            else { fail++; console.log('  ? [' + label + '] ' + name); }
        }

        try {
            // Login
            await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
            await page.fill('input[type=email]', email);
            await page.fill('input[type=password]', PASS);
            await page.click('button[type=submit]');
            await page.waitForTimeout(2000);
            check('Login redirects', !page.url().includes('/login'));

            // Wait for page to settle
            await page.waitForTimeout(3000);

            // Check HTML content
            const html = await page.content();
            check('HTML renders', html.length > 500);

            // Check 404/500 errors from network
            const httpErrors = errors.filter(e => e.includes(' 404 ') || e.includes(' 500 '));
            check('No 404/500 network errors', httpErrors.length === 0);
            if (httpErrors.length > 0) {
                console.log('     HTTP errors:');
                httpErrors.slice(0, 5).forEach(e => console.log('     - ' + e));
            }

            // Check console errors (excluding favicon)
            const consoleErrors = errors.filter(e => !e.includes('favicon') && !e.includes('404'));
            check('No JS console errors', consoleErrors.length === 0);
            if (consoleErrors.length > 0) {
                console.log('     Console:');
                consoleErrors.slice(0, 3).forEach(e => console.log('     - ' + e));
            }

            // Test laporan page for penjual
            if (isPenjual) {
                await page.goto(BASE + '/penjual/laporan', { waitUntil: 'networkidle' });
                check('Laporan page title', (await page.title()).includes('Laporan'));
                await page.waitForTimeout(2000);
                const laporanErrors = errors.filter(e => e.includes(' 404 ') || e.includes(' 500 '));
                check('Laporan no 404/500', laporanErrors.length === 0);
            }

        } catch (e) {
            console.log('  ? [' + label + '] FATAL: ' + e.message);
            fail++;
        }
        await ctx.close();
        return { ok, fail };
    }

    console.log('--- Mahasiswa ---');
    const m = await runTests('mahasiswa', 'mahasiswa@test.com', false);
    console.log('--- Penjual ---');
    const p = await runTests('penjual', 'gembus@test.com', true);

    const total = { ok: m.ok + p.ok, fail: m.fail + p.fail };
    console.log('');
    console.log('=== TOTAL: ' + total.ok + ' passed, ' + total.fail + ' failed ===');
    process.exit(total.fail > 0 ? 1 : 0);
}
test();
