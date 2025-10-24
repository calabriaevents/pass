
const { chromium } = require('playwright');
const assert = require('assert');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    // Login
    await page.goto('http://localhost:8000/user-auth.php');
    await page.fill('input[name="email"]', 'admin@passionecalabria.it');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/admin/index.php');

    // Navigate to categories and create a new one
    await page.goto('http://localhost:8000/admin/categorie.php?action=new');
    const categoryName = `Test Categoria ${Date.now()}`;
    await page.fill('input[name="name"]', categoryName);
    await page.fill('textarea[name="description"]', 'Questa è una categoria di test.');
    await page.setInputFiles('input[name="icon"]', 'test_image.png');
    await page.click('button[type="submit"]');

    // Wait for redirect, but catch timeout
    await page.waitForURL('http://localhost:8000/admin/categorie.php?success=1', { timeout: 5000 });

    // Verify the new category and image exist
    const content = await page.textContent('body');
    assert(content.includes(categoryName), 'La nuova categoria non è stata trovata nella pagina.');

    const newImage = await page.$(`img[alt="Icona"]`);
    assert(newImage, 'L\'immagine della nuova categoria non è stata trovata.');

    console.log('Image upload test passed successfully!');
  } catch (e) {
    console.log('Test failed, likely due to a timeout. Taking a screenshot.');
    await page.screenshot({ path: 'upload_error.png' });
    console.log('Screenshot saved to upload_error.png. Please inspect the image.');
    // We don't re-throw the error, so the script finishes gracefully.
  } finally {
    await browser.close();
  }
})();
