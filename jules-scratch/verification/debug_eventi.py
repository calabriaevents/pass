from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    # Listen for all responses and print the body of failed API calls
    def log_response(response):
        # Check if it's a failed call to our API
        if "api.php" in response.url and not response.ok:
            try:
                print(f"--- FAILED API RESPONSE ---")
                print(f"URL: {response.url}")
                print(f"Status: {response.status}")
                print(f"Body: {response.text()}")
                print(f"--------------------------")
            except Exception as e:
                print(f"Could not read response body for {response.url}: {e}")

    page.on("response", log_response)

    try:
        print("Navigating to /eventi/...")
        # Use default 'load' wait, don't wait for network idle as it may never happen
        page.goto("http://localhost:8000/eventi/", timeout=60000)

        # We still expect this to fail, but the response logger will give us the info we need.
        # We'll wait for a short period to allow API calls to be made.
        print("Waiting for 5 seconds to capture API calls...")
        page.wait_for_timeout(5000)

        print("Debug script finished. Checking captured logs.")

    except Exception as e:
        print(f"An error occurred during verification: {e}")
        # Take a screenshot on error anyway, it might be useful
        page.screenshot(path="jules-scratch/verification/eventi_debug_error.png")
        print("Error screenshot saved.")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)