
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

    // Click the login button
    await page.click('button[type="submit"]');

    // Wait for a moment to let the page react
    await page.waitForTimeout(2000);

    // Capture a screenshot of the current page
    await page.screenshot({ path: 'after_login_attempt.png' });

    // Wait for navigation to the admin dashboard
    await page.waitForURL('http://localhost:8000/admin/index.php');

    // Capture a screenshot of the dashboard
    await page.screenshot({ path: 'admin_dashboard.png' });

    // Capture a screenshot of the admin menu
    const menu = await page.$('.bg-gray-900');
    if (menu) {
      await menu.screenshot({ path: 'admin_menu.png' });
    } else {
      console.log('Admin menu not found.');
    }

    console.log('Login successful, screenshots captured.');
  } catch (error) {
    console.error('An error occurred during verification:', error);
    await page.screenshot({ path: 'error.png' });
  } finally {
    await browser.close();
  }
})();
