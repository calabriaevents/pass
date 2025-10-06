from playwright.sync_api import sync_playwright, Page, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # Navigate to the suggestion page
        page.goto("http://localhost:8000/suggerisci.php")

        # Fill out the form
        page.locator("#place_name").fill("Test Place")
        page.locator("#location").fill("Test Location")
        page.locator("#description").fill("Test Description")
        page.locator("#user_name").fill("Jules Verne")
        page.locator("#user_email").fill("jules.verne@example.com")

        # Attach a dummy file
        page.locator("#place_images").set_input_files("jules-scratch/verification/test_image.txt")

        # Submit the form
        page.locator("button[type='submit']").click()

        # Wait for the success message to be visible
        success_message = page.locator("#notification-placeholder .notification.success")
        expect(success_message).to_be_visible(timeout=10000)
        expect(success_message).to_have_text("Grazie per il tuo suggerimento! Il nostro team lo esaminerà al più presto.")

        # Take a screenshot
        page.screenshot(path="jules-scratch/verification/suggestion_success.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)