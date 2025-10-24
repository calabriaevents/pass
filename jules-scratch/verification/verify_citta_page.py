import re
from playwright.sync_api import sync_playwright, Page, expect

def run_verification(page: Page):
    """
    Navigates to the citta.php page, verifies that images are loaded
    through the image-proxy.php script, and takes a screenshot.
    """
    try:
        # 1. Go to the citta.php page.
        # Assuming a local web server is running.
        page.goto("http://localhost/citta.php", timeout=15000)

        # 2. Find the first city image.
        # It's inside a grid, within a group, and is an img tag.
        first_image = page.locator("#cities-grid .group img").first

        # 3. Assert that the image is visible and uses the proxy.
        # We'll wait for it to be visible.
        expect(first_image).to_be_visible(timeout=10000)

        # We expect the 'src' attribute to match the pattern 'image-proxy.php?file=...'
        expect(first_image).to_have_attribute("src", re.compile(r"image-proxy\.php\?file="))

        # 4. Take a screenshot for visual confirmation.
        screenshot_path = "jules-scratch/verification/verification.png"
        page.screenshot(path=screenshot_path)

        print(f"Screenshot successfully saved to {screenshot_path}")

    except Exception as e:
        print(f"An error occurred during verification: {e}")
        # Take a screenshot on error to help debug
        page.screenshot(path="jules-scratch/verification/error_screenshot.png")
        # Re-raise the exception to indicate failure
        raise

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        try:
            run_verification(page)
            print("Frontend verification script executed successfully.")
        except Exception as e:
            print(f"Frontend verification script failed.")
        finally:
            browser.close()