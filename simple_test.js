const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  const page = await browser.newPage();
  let loginSuccess = false;

  try {
    console.log('Navigating to login page...');
    await page.goto('http://localhost:8000/user-auth.php', { waitUntil: 'networkidle' });

    console.log('Filling in login form with new credentials...');
    await page.fill('input[name="email"]', 'info@passionecalabria.it');
    await page.fill('input[name="password"]', 'barboncino692@@');

    console.log('Clicking login button...');
    await page.click('button[type="submit"]');

    // Wait for a short period to allow the server to process the login.
    await page.waitForTimeout(2000);

    console.log('Attempting to navigate directly to admin dashboard...');
    await page.goto('http://localhost:8000/admin/index.php', { waitUntil: 'networkidle' });

    // Check if we are on the dashboard
    const pageTitle = await page.title();
    if (pageTitle.includes('Dashboard')) {
        loginSuccess = true;
        console.log('Login successful! Capturing dashboard screenshot...');
        await page.screenshot({ path: 'admin_dashboard_after_fix.png', fullPage: true });
        console.log('Dashboard screenshot saved to admin_dashboard_after_fix.png');

        console.log('Navigating to articles page...');
        await page.goto('http://localhost:8000/admin/articoli.php', { waitUntil: 'networkidle' });
        console.log('Capturing articles page screenshot...');
        await page.screenshot({ path: 'admin_articles_after_fix.png', fullPage: true });
        console.log('Admin articles screenshot saved to admin_articles_after_fix.png');

    } else {
        console.log('Login seemed to fail, capturing screenshot of current page.');
        await page.screenshot({ path: 'admin_page_load_failure.png', fullPage: true });
        throw new Error('Direct navigation to dashboard failed.');
    }

  } catch (error) {
    console.error('An error occurred:', error.message);
    if (!loginSuccess) {
      await page.screenshot({ path: 'login_failure_with_new_creds.png', fullPage: true });
      console.log('Failure screenshot saved to login_failure_with_new_creds.png');
    }
  } finally {
    await browser.close();
  }
})();
