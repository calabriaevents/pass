from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    try:
        # 1. Arrange: Go to the contact page.
        page.goto("http://localhost:8000/contatti.php")

        # 2. Act: Fill out the form.
        page.get_by_label("Nome").fill("Test User")
        page.get_by_label("Email").fill("test@example.com")
        page.get_by_label("Messaggio").fill("This is a test message to verify the spinner.")

        # 3. Trigger the spinner and wait for it to appear.
        # Click the submit button. This will trigger the form submission and the spinner.
        page.get_by_role("button", name="Invia Messaggio").click()

        # 4. Assert: Wait for the loading spinner overlay to be visible.
        # This is the key verification step. We're checking that the global JS is working.
        spinner_overlay = page.locator(".form-loading-overlay")
        expect(spinner_overlay).to_be_visible(timeout=5000) # Wait up to 5 seconds

        # 5. Screenshot: Capture the result for visual verification.
        page.screenshot(path="jules-scratch/verification/spinner_verification.png")
        print("Screenshot taken successfully of the loading spinner.")

    except Exception as e:
        print(f"An error occurred: {e}")
        # Take a screenshot on error for debugging
        page.screenshot(path="jules-scratch/verification/error_screenshot.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)