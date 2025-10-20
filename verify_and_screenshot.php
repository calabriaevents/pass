<?php
require_once 'vendor/autoload.php';
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter;
use Playwright\Playwright;

// Main verification script
$playwright = new Playwright('node '. __DIR__. '/node_modules/playwright/cli.js', 'chromium');
$browser = $playwright->chromium->launch([
    'headless' => true, // Use true for CI/CD environments
]);
$context = $browser->newContext();
$page = $context->newPage();

try {
    // 1. Go to the login page
    echo "Navigating to login page...\n";
    $page->goto('http://localhost:8000/user-auth.php', ['waitUntil' => 'networkidle']);

    // Take a screenshot of the login page
    $page->screenshot(['path' => 'admin_login_page.png']);
    echo "Screenshot of login page saved as admin_login_page.png\n";

    // 2. Fill in the login form
    echo "Filling in login form...\n";
    $page->fill('input[name="email"]', 'info@passionecalabria.it');
    $page->fill('input[name="password"]', 'barboncino6_92@@');

    // 3. Click the login button
    echo "Submitting login form...\n";
    $page->click('button[type="submit"]');

    // 4. Wait for navigation to the admin dashboard
    echo "Waiting for navigation to admin dashboard...\n";
    $page->waitForNavigation(['url' => 'http://localhost:8000/admin/index.php', 'waitUntil' => 'networkidle', 'timeout' => 15000]);

    // 5. Take a screenshot of the admin dashboard
    echo "Taking screenshot of admin dashboard...\n";
    $page->screenshot(['path' => 'admin_dashboard.png']);
    echo "Screenshot of dashboard saved as admin_dashboard.png\n";

    // 6. Navigate to the articles page and take a screenshot
    echo "Navigating to articles page...\n";
    $page->goto('http://localhost:8000/admin/articoli.php', ['waitUntil' => 'networkidle']);
    $page->screenshot(['path' => 'admin_articles.png']);
    echo "Screenshot of articles page saved as admin_articles.png\n";

    // 7. Navigate to the city management page and take a screenshot
    echo "Navigating to city management page...\n";
    $page->goto('http://localhost:8000/admin/citta.php', ['waitUntil' => 'networkidle']);
    $page->screenshot(['path' => 'admin_citta.png']);
    echo "Screenshot of city management page saved as admin_citta.png\n";

    // 8. Navigate to the province management page and take a screenshot
    echo "Navigating to province management page...\n";
    $page->goto('http://localhost:8000/admin/province.php', ['waitUntil' => 'networkidle']);
    $page->screenshot(['path' => 'admin_province.png']);
    echo "Screenshot of province management page saved as admin_province.png\n";

    echo "Verification successful!\n";

} catch (Exception $e) {
    echo "An error occurred: ". $e->getMessage(). "\n";
    $page->screenshot(['path' => 'login_error.png']);
    echo "Screenshot of error page saved as login_error.png\n";
} finally {
    $browser->close();
}
?>