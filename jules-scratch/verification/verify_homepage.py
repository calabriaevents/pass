from playwright.sync_api import sync_playwright

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        try:
            page.goto("http://localhost:8000", timeout=10000)
            page.wait_for_selector('body', state='visible', timeout=5000)
            page.screenshot(path="jules-scratch/verification/homepage_fixed.png")
        except Exception as e:
            print(f"An error occurred: {e}")
        finally:
            browser.close()

if __name__ == "__main__":
    run()
