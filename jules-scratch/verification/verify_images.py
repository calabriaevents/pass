from playwright.sync_api import sync_playwright
import os

def run_verification():
    # Assicurati che la directory esista
    output_dir = "jules-scratch/verification"
    os.makedirs(output_dir, exist_ok=True)

    # Definisci i percorsi per gli screenshot
    index_screenshot_path = os.path.join(output_dir, "index.png")
    categorie_screenshot_path = os.path.join(output_dir, "categorie.png")

    # URL di base dell'applicazione
    # Assumiamo che l'applicazione sia servita sulla porta 8000
    base_url = "http://localhost:8000"

    with sync_playwright() as p:
        try:
            browser = p.chromium.launch(headless=True)
            page = browser.new_page()

            # Verifica index.php
            print(f"Navigazione verso {base_url}/index.php...")
            page.goto(f"{base_url}/index.php", wait_until="networkidle")
            # Scroll per assicurarsi che le sezioni siano caricate
            page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
            page.wait_for_timeout(1000) # Attendi un secondo per il rendering
            print(f"Salvataggio screenshot in {index_screenshot_path}...")
            page.screenshot(path=index_screenshot_path, full_page=True)
            print("Screenshot di index.php salvato.")

            # Verifica categorie.php
            print(f"Navigazione verso {base_url}/categorie.php...")
            page.goto(f"{base_url}/categorie.php", wait_until="networkidle")
            page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
            page.wait_for_timeout(1000) # Attendi un secondo per il rendering
            print(f"Salvataggio screenshot in {categorie_screenshot_path}...")
            page.screenshot(path=categorie_screenshot_path, full_page=True)
            print("Screenshot di categorie.php salvato.")

            browser.close()
            print("Verifica completata con successo.")

        except Exception as e:
            print(f"Si è verificato un errore durante la verifica con Playwright: {e}")
            print("Assicurati che il server PHP sia in esecuzione su http://localhost:8000")
            print("Puoi avviarlo con il comando: php -S localhost:8000")

if __name__ == "__main__":
    run_verification()
