from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    context = browser.new_context()
    page = context.new_page()

    # Login
    page.goto("http://localhost:8000/user-auth.php")
    page.fill('input[name="email"]', "info@passionecalabria.it")
    page.fill('input[name="password"]', "Barboncino692@@")
    page.click('button[type="submit"]')
    page.wait_for_url("http://localhost:8000/admin/index.php")

    # Take screenshot
    page.screenshot(path="jules-scratch/verification/admin_menu.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
