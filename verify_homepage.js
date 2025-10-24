
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto('http://localhost:8000');
  await page.screenshot({ path: 'homepage_ok.png' });
  await browser.close();
  console.log('Screenshot "homepage_ok.png" scattato con successo.');
})();
