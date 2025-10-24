import asyncio
from playwright.async_api import async_playwright

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()
        try:
            print("Navigazione verso http://localhost:8000...")
            await page.goto('http://localhost:8000', timeout=60000)
            print("Pagina caricata. Attendo il selettore delle categorie...")
            # Attendi che la sezione delle categorie sia visibile
            await page.wait_for_selector('section:has-text("Esplora per Categoria")', timeout=30000)
            print("Selettore trovato. Catturo lo screenshot...")
            await page.screenshot(path='final_verification.png', full_page=True)
            print("Screenshot salvato con successo come final_verification.png")
        except Exception as e:
            print(f"Si è verificato un errore durante la verifica con Playwright: {e}")
        finally:
            await browser.close()

if __name__ == '__main__':
    asyncio.run(main())
