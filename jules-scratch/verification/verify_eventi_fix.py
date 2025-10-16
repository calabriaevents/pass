from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        page.goto("http://localhost:8000/eventi/", timeout=60000)

        # Wait for at least one event card to be rendered.
        # This confirms the API call worked and the frontend rendered the data.
        expect(page.locator("#home-events-list .bg-white").first).to_be_visible(timeout=20000)

        page.screenshot(path="jules-scratch/verification/eventi_final_check.png")
        print("Verification successful!")

    except Exception as e:
        print(f"Verification failed: {e}")
        page.screenshot(path="jules-scratch/verification/eventi_final_error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)