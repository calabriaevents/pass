const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    // Navigate to the login page
    await page.goto('http://localhost:8000/user-auth.php');

    // Fill in the login form
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');

    // Submit the form
    await page.click('button[type="submit"]');

    // Wait for navigation to the admin dashboard
    await page.waitForURL('http://localhost:8000/admin/index.php');

    // Capture a screenshot of the dashboard
    await page.screenshot({ path: 'admin_dashboard_after_fix.png' });

    // Navigate to the articles page
    await page.click('a[href="articoli.php"]');
    await page.waitForURL('http://localhost:8000/admin/articoli.php');

    // Capture a screenshot of the articles page
    await page.screenshot({ path: 'admin_articles_after_fix.png' });

    console.log('Screenshots captured successfully.');

  } catch (error) {
    console.error('An error occurred during the test:', error);
    await page.screenshot({ path: 'login_error.png' });
  } finally {
    await browser.close();
  }
})();
