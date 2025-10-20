from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        page.goto("http://localhost:8000/user-auth.php")
        page.fill('input[name="email"]', "info@passionecalabria.it")
        page.fill('input[name="password"]', "barboncino692@@")
        page.click('button[type="submit"]')
        page.wait_for_url("http://localhost:8000/admin/index.php")
        page.screenshot(path="admin_dashboard.png")
        browser.close()

run()
