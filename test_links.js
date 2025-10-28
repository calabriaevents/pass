
const { chromium } = require('playwright');
const assert = require('assert');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  const linksToTest = [
    '/',
    '/chi-siamo.php',
    '/collabora.php',
    '/contatti.php',
    '/province.php',
    '/categorie.php'
  ];

  for (const link of linksToTest) {
    const response = await page.goto(`http://localhost:8000${link}`);
    console.log(`Checking ${link} - Status: ${response.status()}`);
    assert.strictEqual(response.status(), 200, `Link ${link} failed with status ${response.status()}`);
  }

  await browser.close();
  console.log('All public links checked successfully!');
})();
