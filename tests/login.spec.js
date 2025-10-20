// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Admin Login', () => {
  test('should allow admin to log in and see the admin dashboard', async ({ page }) => {
    // Navigate to the login page
    await page.goto('http://localhost:8000/user-auth.php');

    // Fill in the login form
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');

    // Click the login button
    await page.click('button[type="submit"]');

    // Take a screenshot to see the result of the login attempt
    await page.screenshot({ path: 'after_login_attempt.png' });

    // Wait for navigation to the admin dashboard
    await page.waitForURL('http://localhost:8000/admin/index.php');

    // Check if the dashboard contains a specific element
    const welcomeMessage = await page.textContent('h1');
    expect(welcomeMessage).toContain('Dashboard');

    // Take a screenshot of the admin dashboard
    await page.screenshot({ path: 'admin_dashboard.png' });

    // Take a screenshot of the admin menu
    const menu = await page.$('nav');
    if (menu) {
      await menu.screenshot({ path: 'admin_menu.png' });
    }
  });
});
