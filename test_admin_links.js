
const { chromium } = require('playwright');
const assert = require('assert');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  // Login first
  await page.goto('http://localhost:8000/user-auth.php');
  await page.fill('input[name="email"]', 'admin@passionecalabria.it');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('http://localhost:8000/admin/index.php');

  const adminLinksToTest = [
    '/admin/index.php',
    '/admin/articoli.php',
    '/admin/categorie.php',
    '/admin/province.php',
    '/admin/citta.php',
    '/admin/commenti.php',
    '/admin/utenti.php',
    '/admin/impostazioni.php'
  ];

  for (const link of adminLinksToTest) {
    const response = await page.goto(`http://localhost:8000${link}`);
    console.log(`Checking ${link} - Status: ${response.status()}`);
    assert.strictEqual(response.status(), 200, `Admin link ${link} failed with status ${response.status()}`);
  }

  await browser.close();
  console.log('All admin links checked successfully!');
})();
