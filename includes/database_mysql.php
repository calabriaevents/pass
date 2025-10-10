<?php
// Database MySQL Class per Passione Calabria
// Versione unificata e completa
// 🚀 VERSIONE CORRETTA - ERRORE 500 RISOLTO

// Load database configuration
require_once __DIR__ . '/db_config.php';

class Database {
    public $pdo;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $connection_error = false;
    private $error_message = '';

    public function __construct() {
        // Load database configuration from secure config file
        $config = getDatabaseConfig();
        $this->host = $config['host'];
        $this->dbname = $config['dbname'];
        $this->username = $config['username'];
        $this->password = $config['password'];

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            $this->connection_error = true;
            $this->error_message = 'Errore connessione database MySQL: ' . $e->getMessage();
            // Log the error but don't throw exception to prevent 500 errors
            error_log($this->error_message);
            $this->pdo = null;
        }
    }

    public function isConnected() {
        return !$this->connection_error && $this->pdo !== null;
    }

    public function getConnectionError() {
        return $this->error_message;
    }

    private function checkConnection() {
        if (!$this->isConnected()) {
            return false;
        }
        return true;
    }

    // Metodi per Categorie
    public function getCategories() {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM categories ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategoryById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createCategory($name, $description, $icon) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)');
        $stmt->execute([$name, $description, $icon]);
        return $this->pdo->lastInsertId();
    }

    public function updateCategory($id, $name, $description, $icon) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?');
        $stmt->execute([$name, $description, $icon, $id]);
        return $stmt->rowCount();
    }

    public function deleteCategory($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    // Metodi per Province
    public function getProvinces() {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM provinces ORDER BY name');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProvinceById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT * FROM provinces WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createProvince($name, $description, $image_path = null) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('INSERT INTO provinces (name, description, image_path) VALUES (?, ?, ?)');
        return $stmt->execute([$name, $description, $image_path]);
    }

    public function updateProvince($id, $name, $description, $image_path = null) {
        if (!$this->isConnected()) { return false; }
        $params = ['name' => $name, 'description' => $description, 'id' => $id];
        $sql = 'UPDATE provinces SET name = :name, description = :description';
        if ($image_path !== null) {
            $sql .= ', image_path = :image_path';
            $params['image_path'] = $image_path;
        }
        $sql .= ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteProvince($id) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('DELETE FROM provinces WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Metodi per Città
    public function getCities() {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT c.*, p.name as province_name FROM cities c LEFT JOIN provinces p ON c.province_id = p.id ORDER BY c.name');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCitiesByProvince($provinceId) {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM cities WHERE province_id = ? ORDER BY name');
        $stmt->execute([$provinceId]);
        return $stmt->fetchAll();
    }

    public function getCitiesFiltered($provinceId = null, $searchQuery = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT c.*, p.name as province_name FROM cities c LEFT JOIN provinces p ON c.province_id = p.id WHERE 1=1';
        $params = [];
        if ($provinceId) {
            $sql .= ' AND c.province_id = ?';
            $params[] = $provinceId;
        }
        if ($searchQuery) {
            $sql .= ' AND (c.name LIKE ? OR c.description LIKE ?)';
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }
        $sql .= ' ORDER BY c.name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPendingEventSuggestionsCount() {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM events WHERE source = "user_submission" AND status = "pending"');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getPendingPlaceSuggestionsCount() {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM place_suggestions WHERE status = "pending"');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getPendingCommentsCount() {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM comments WHERE status = "pending"');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getPendingBusinessesCount() {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM businesses WHERE status = "pending"');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getPendingUserUploadsCount() {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM user_uploads WHERE status = "pending"');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getCityById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT c.*, p.name as province_name, p.id as province_id FROM cities c LEFT JOIN provinces p ON c.province_id = p.id WHERE c.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createCity($name, $province_id, $description = '', $latitude = null, $longitude = null) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('INSERT INTO cities (name, province_id, description, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        return $stmt->execute([$name, $province_id, $description, $latitude, $longitude]);
    }

    public function updateCity($id, $name, $province_id, $description = '', $latitude = null, $longitude = null) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('UPDATE cities SET name = ?, province_id = ?, description = ?, latitude = ?, longitude = ? WHERE id = ?');
        return $stmt->execute([$name, $province_id, $description, $latitude, $longitude, $id]);
    }

    // Nuovi metodi per gestire campi estesi delle città (hero_image, Maps_link, gallery_images)
    public function createCityExtended($name, $province_id, $description = '', $latitude = null, $longitude = null, $hero_image = null, $google_maps_link = null, $gallery_images = null) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('INSERT INTO cities (name, province_id, description, latitude, longitude, hero_image, Maps_link, gallery_images, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        return $stmt->execute([$name, $province_id, $description, $latitude, $longitude, $hero_image, $google_maps_link, $gallery_images]);
    }

    public function updateCityExtended($id, $name, $province_id, $description = '', $latitude = null, $longitude = null, $hero_image = null, $google_maps_link = null, $gallery_images = null) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('UPDATE cities SET name = ?, province_id = ?, description = ?, latitude = ?, longitude = ?, hero_image = ?, Maps_link = ?, gallery_images = ? WHERE id = ?');
        return $stmt->execute([$name, $province_id, $description, $latitude, $longitude, $hero_image, $google_maps_link, $gallery_images, $id]);
    }

    public function deleteCity($id) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('DELETE FROM cities WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // Metodi per Articoli
    public function getArticles($limit = null, $offset = 0, $onlyPublished = true) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.logo, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name, p.name as province_name, ci.name as city_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN provinces p ON a.province_id = p.id LEFT JOIN cities ci ON a.city_id = ci.id';
        $params = [];
        if ($onlyPublished) {
            $sql .= ' WHERE a.status = ?';
            $params[] = 'published';
        }
        $sql .= ' ORDER BY a.created_at DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getArticleById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name, p.name as province_name, ci.name as city_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN provinces p ON a.province_id = p.id LEFT JOIN cities ci ON a.city_id = ci.id WHERE a.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getArticlesByCity($cityId, $limit = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name, p.name as province_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN provinces p ON a.province_id = p.id WHERE a.city_id = ? AND a.status = ? ORDER BY a.created_at DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cityId, 'published']);
        return $stmt->fetchAll();
    }

    public function getArticleCountByCity($cityId) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM articles WHERE city_id = ? AND status = ?');
        $stmt->execute([$cityId, 'published']);
        $result = $stmt->fetch();
        return $result ? $result['count'] : 0;
    }

public function createArticle($title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $author = 'Admin', $featured_image = null, $gallery_images = null, $hero_image = null, $logo = null, $json_data = null) {
    if (!$this->isConnected()) { return false; }
    $sql = "INSERT INTO articles (title, slug, content, excerpt, category_id, province_id, city_id, status, author, featured_image, gallery_images, hero_image, logo, json_data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $author, $featured_image, $gallery_images, $hero_image, $logo, $json_data]);
    return $this->pdo->lastInsertId();
}

public function updateArticle($id, $title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $featured_image = null, $gallery_images = null, $hero_image = null, $logo = null, $json_data = null) {
    if (!$this->isConnected()) { return 0; }
    $sql = "UPDATE articles SET title = ?, slug = ?, content = ?, excerpt = ?, category_id = ?, province_id = ?, city_id = ?, status = ?, featured_image = ?, gallery_images = ?, hero_image = ?, logo = ?, json_data = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $featured_image, $gallery_images, $hero_image, $logo, $json_data, $id]);
    return $stmt->rowCount();
}

    public function deleteArticle($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function getFeaturedArticles($limit = 6) {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.featured = 1 AND a.status = ? ORDER BY a.views DESC LIMIT ?');
        $stmt->execute(['published', $limit]);
        return $stmt->fetchAll();
    }

    public function getArticlesByCategory($categoryId, $limit = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.logo, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name, p.name as province_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN provinces p ON a.province_id = p.id WHERE a.category_id = ? AND a.status = ? ORDER BY a.created_at DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId, 'published']);
        return $stmt->fetchAll();
    }

    public function getArticlesByProvince($provinceId, $limit = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name, p.name as province_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id LEFT JOIN provinces p ON a.province_id = p.id WHERE a.province_id = ? AND a.status = ? ORDER BY a.created_at DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$provinceId, 'published']);
        return $stmt->fetchAll();
    }

public function getArticleBySlug($slug) {
    if (!$this->isConnected()) { return null; }
    $stmt = $this->pdo->prepare('
        SELECT
            a.*,
            c.name as category_name,
            p.name as province_name,
            ci.name as city_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN provinces p ON a.province_id = p.id
        LEFT JOIN cities ci ON a.city_id = ci.id
        WHERE a.slug = ?
    ');
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

    public function getArticleCountByCategory($categoryId) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM articles WHERE category_id = ? AND status = ?');
        $stmt->execute([$categoryId, 'published']);
        $result = $stmt->fetch();
        return $result ? $result['count'] : 0;
    }

    public function getArticleCountByProvince($provinceId) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM articles WHERE province_id = ? AND status = ?');
        $stmt->execute([$provinceId, 'published']);
        $result = $stmt->fetch();
        return $result ? $result['count'] : 0;
    }

    // 🚀 METODO CORRETTO: Rimossi campi inesistenti che causavano errore 500
    public function searchArticles($query, $provinceId = null) {
        if (!$this->isConnected()) { return []; }
        
        // ✅ ERRORE 500 RISOLTO: Rimossi campi inesistenti a.hero_image, a.logo, a.json_data
        $sql = 'SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.province_id, a.city_id, a.status, a.author, a.featured_image, a.gallery_images, a.created_at, a.updated_at, a.views, a.featured, c.name as category_name, p.name as province_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN provinces p ON a.province_id = p.id 
                WHERE (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?) AND a.status = ?';
        
        $params = ["%$query%", "%$query%", "%$query%", 'published'];
        
        if ($provinceId) {
            $sql .= ' AND a.province_id = ?';
            $params[] = $provinceId;
        }
        
        $sql .= ' ORDER BY a.created_at DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function incrementArticleViews($id) {
        if (!$this->isConnected()) { return; }
        $stmt = $this->pdo->prepare('UPDATE articles SET views = views + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Metodi per Utenti
    public function getUsers() {
        if (!$this->checkConnection()) {
            return [];
        }
        $stmt = $this->pdo->prepare('SELECT id, email, name, role, status, last_login FROM users ORDER BY created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function authenticateUserByEmail($email) {
        if (!$this->checkConnection()) {
            return false;
        }
        $stmt = $this->pdo->prepare('
            SELECT u.id, u.email, u.password, u.name, u.role, b.id as business_id, b.status as business_status
            FROM users u
            LEFT JOIN businesses b ON u.email = b.email
            WHERE u.email = ?
        ');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function authenticateBusinessUser($email) {
        if (!$this->checkConnection()) {
            return false;
        }
        $stmt = $this->pdo->prepare('
            SELECT u.*, b.id as business_id, b.name as business_name, b.status as business_status
            FROM users u
            LEFT JOIN businesses b ON u.email = b.email
            WHERE u.email = ? AND u.role = "business"
        ');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function updateUserLastLogin($userId) {
        if (!$this->checkConnection()) {
            return false;
        }
        $stmt = $this->pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    public function getUserBusinessData($userId) {
        if (!$this->checkConnection()) {
            return false;
        }
        $stmt = $this->pdo->prepare('
            SELECT u.*, b.name as business_name, b.email, b.phone, b.website, 
                   b.description, b.status as business_status, b.created_at as business_created
            FROM users u
            LEFT JOIN businesses b ON u.email = b.email
            WHERE u.id = ?
        ');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getCurrentSubscription($businessId) {
        if (!$this->checkConnection()) {
            return false;
        }
        $stmt = $this->pdo->prepare('
            SELECT s.*, bp.name as package_name, bp.description as package_description, 
                   bp.price as package_price, bp.features, bp.package_type,
                   CASE 
                       WHEN s.status = "active" AND s.end_date < NOW() THEN "expired"
                       WHEN s.status = "active" AND s.end_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND s.end_date > NOW() THEN "expiring"
                       ELSE s.status
                   END as computed_status
            FROM subscriptions s
            LEFT JOIN business_packages bp ON s.package_id = bp.id
            WHERE s.business_id = ? AND s.status IN ("active", "expired")
            ORDER BY s.created_at DESC LIMIT 1
        ');
        $stmt->execute([$businessId]);
        return $stmt->fetch();
    }

    public function getAvailablePackages() {
        if (!$this->checkConnection()) {
            return [];
        }
        $stmt = $this->pdo->prepare('
            SELECT * FROM business_packages 
            WHERE is_active = 1 AND package_type = "subscription"
            ORDER BY id ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function upgradeSubscription($businessId, $newPackageId) {
        if (!$this->checkConnection()) {
            return false;
        }
        try {
            // Begin transaction
            $this->pdo->beginTransaction();
            
            // Get current subscription
            $stmt = $this->pdo->prepare('
                SELECT * FROM subscriptions 
                WHERE business_id = ? AND status = "active" 
                ORDER BY created_at DESC LIMIT 1
            ');
            $stmt->execute([$businessId]);
            $currentSubscription = $stmt->fetch();
            
            if ($currentSubscription) {
                // Update current subscription to cancelled
                $stmt = $this->pdo->prepare('UPDATE subscriptions SET status = "cancelled" WHERE id = ?');
                $stmt->execute([$currentSubscription['id']]);
            }
            
            // Create new subscription
            $stmt = $this->pdo->prepare('
                INSERT INTO subscriptions (business_id, package_id, status, start_date, end_date, created_at)
                VALUES (?, ?, "active", NOW(), DATE_ADD(NOW(), INTERVAL 12 MONTH), NOW())
            ');
            $result = $stmt->execute([$businessId, $newPackageId]);
            
            // Commit transaction
            $this->pdo->commit();
            
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log('Error in upgradeSubscription: ' . $e->getMessage());
            return false;
        }
    }

    public function getUserById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT id, email, name, first_name, last_name, role, status FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createUser($email, $password, $name, $role, $status) {
        if (!$this->isConnected()) { return false; }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, name, role, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email, $hashed_password, $name, $role, $status]);
        return $this->pdo->lastInsertId();
    }

    public function updateUser($id, $email, $password, $name, $role, $status) {
        if (!$this->isConnected()) { return 0; }
        $sql = "UPDATE users SET email = ?, name = ?, role = ?, status = ?";
        $params = [$email, $name, $role, $status];
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $params[] = $hashed_password;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function deleteUser($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    // Metodi per Commenti
    public function getComments($status = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT c.*, a.title as article_title FROM comments c LEFT JOIN articles a ON c.article_id = a.id';
        $params = [];
        if ($status) {
            $sql .= ' WHERE c.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY c.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCommentById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateCommentStatus($id, $status) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('UPDATE comments SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        return $stmt->rowCount();
    }

    public function updateCommentContent($id, $content) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('UPDATE comments SET content = ? WHERE id = ?');
        $stmt->execute([$content, $id]);
        return $stmt->rowCount();
    }

    public function getApprovedCommentsByArticleId($article_id) {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE article_id = ? AND status = "approved" ORDER BY created_at DESC');
        $stmt->execute([$article_id]);
        return $stmt->fetchAll();
    }

    public function createComment($article_id, $author_name, $author_email, $content, $rating) {
        if (!$this->isConnected()) { return false; }
        // For now, all comments are pending. In a real app, you might check for user roles.
        $status = 'pending';
        $sql = "INSERT INTO comments (article_id, author_name, author_email, content, rating, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$article_id, $author_name, $author_email, $content, $rating, $status]);
    }

    public function deleteComment($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    // Metodi per Commenti Città
    public function getCityComments($cityId = null, $status = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT c.*, ci.name as city_name FROM comments c LEFT JOIN cities ci ON c.city_id = ci.id WHERE c.article_id IS NULL';
        $params = [];
        if ($cityId) {
            $sql .= ' AND c.city_id = ?';
            $params[] = $cityId;
        }
        if ($status) {
            $sql .= ' AND c.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY c.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getApprovedCommentsByCityId($city_id) {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE city_id = ? AND status = "approved" AND article_id IS NULL ORDER BY created_at DESC');
        $stmt->execute([$city_id]);
        return $stmt->fetchAll();
    }

    public function createCityComment($city_id, $author_name, $author_email, $content, $rating = null) {
        if (!$this->isConnected()) { return false; }
        $status = 'pending';
        $sql = "INSERT INTO comments (city_id, article_id, author_name, author_email, content, rating, status, created_at) VALUES (?, NULL, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$city_id, $author_name, $author_email, $content, $rating, $status]);
    }

    // Metodi per User Uploads
    public function getUserUploads($status = null, $type = null) {
        if (!$this->isConnected()) { return []; }
        
        // JOIN con articles e cities
        $sql = 'SELECT u.*, a.title as article_title, ci.name as city_name FROM user_uploads u
                LEFT JOIN articles a ON u.article_id = a.id
                LEFT JOIN cities ci ON u.city_id = ci.id';
        $params = [];
        $conditions = [];
        
        if ($status) {
            $conditions[] = 'u.status = ?';
            $params[] = $status;
        }
        
        if ($type === 'article') {
            $conditions[] = 'u.article_id IS NOT NULL';
        } elseif ($type === 'city') {
            $conditions[] = 'u.city_id IS NOT NULL';
        }
        
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        
        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUserUploadById($id) {
        if (!$this->isConnected()) { return null; }
        // JOIN con articles e cities
        $stmt = $this->pdo->prepare('SELECT u.*, a.title as article_title, ci.name as city_name FROM user_uploads u
                                    LEFT JOIN articles a ON u.article_id = a.id 
                                    LEFT JOIN cities ci ON u.city_id = ci.id
                                    WHERE u.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getApprovedCityPhotos($city_id) {
        if (!$this->isConnected()) { return []; }
        // Recupera foto approvate per una specifica città
        $stmt = $this->pdo->prepare('SELECT * FROM user_uploads WHERE city_id = ? AND status = "approved" ORDER BY created_at DESC');
        $stmt->execute([$city_id]);
        return $stmt->fetchAll();
    }

    public function createCityPhotoUpload($city_id, $user_name, $user_email, $image_path, $original_filename, $description = null) {
        if (!$this->isConnected()) { return false; }
        $status = 'pending';
        // Salva foto con city_id per associarla alla città
        $sql = "INSERT INTO user_uploads (article_id, city_id, user_name, user_email, image_path, original_filename, description, status, created_at) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$city_id, $user_name, $user_email, $image_path, $original_filename, $description, $status]);
    }

    public function updateUserUploadStatus($id, $status, $admin_notes = null) {
        if (!$this->isConnected()) { return 0; }
        $sql = "UPDATE user_uploads SET status = ?";
        $params = [$status];
        if ($admin_notes !== null) {
            $sql .= ", admin_notes = ?";
            $params[] = $admin_notes;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function deleteUserUpload($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM user_uploads WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    // Metodi per Sezioni Home
    public function getHomeSections() {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM home_sections WHERE is_visible = 1 ORDER BY sort_order');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateHomeSection($sectionName, $data) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('UPDATE home_sections SET title = ?, subtitle = ?, description = ?, image_path = ?, custom_data = ?, updated_at = NOW() WHERE section_name = ?');
        return $stmt->execute([$data['title'] ?? '', $data['subtitle'] ?? '', $data['description'] ?? '', $data['image_path'] ?? '', $data['custom_data'] ?? '', $sectionName]);
    }

    // Metodi per Eventi
    public function getUpcomingEvents($limit = 10) {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT e.*, c.name as category_name, p.name as province_name FROM events e LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN provinces p ON e.province_id = p.id WHERE e.start_date >= NOW() AND e.status = ? ORDER BY e.start_date ASC LIMIT ?');
        $stmt->execute(['active', $limit]);
        return $stmt->fetchAll();
    }

    public function createEventSuggestion($title, $description, $start_date, $end_date, $location, $category_id, $province_id, $organizer, $contact_email, $contact_phone = null, $website = null, $price = 0) {
        if (!$this->isConnected()) { return false; }
        $stmt = $this->pdo->prepare('INSERT INTO events (title, description, start_date, end_date, location, category_id, province_id, organizer, contact_email, contact_phone, website, price, status, source, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", "user_submission", NOW())');
        return $stmt->execute([$title, $description, $start_date, $end_date ?: null, $location, $category_id ?: null, $province_id ?: null, $organizer, $contact_email, $contact_phone, $website, $price]);
    }

    public function getEventSuggestions($status = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT e.*, c.name as category_name, p.name as province_name FROM events e LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN provinces p ON e.province_id = p.id WHERE e.source = ?';
        $params = ['user_submission'];
        if ($status) {
            $sql .= ' AND e.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY e.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getEventSuggestionById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT e.*, c.name as category_name, p.name as province_name FROM events e LEFT JOIN categories c ON e.category_id = c.id LEFT JOIN provinces p ON e.province_id = p.id WHERE e.id = ? AND e.source = ?');
        $stmt->execute([$id, 'user_submission']);
        return $stmt->fetch();
    }

    public function updateEventSuggestion($id, $title, $description, $start_date, $end_date, $location, $category_id, $province_id, $organizer, $contact_email, $contact_phone, $website, $price, $status) {
        if (!$this->isConnected()) { return 0; }
        $sql = "UPDATE events SET title = ?, description = ?, start_date = ?, end_date = ?, location = ?, category_id = ?, province_id = ?, organizer = ?, contact_email = ?, contact_phone = ?, website = ?, price = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$title, $description, $start_date, $end_date ?: null, $location, $category_id ?: null, $province_id ?: null, $organizer, $contact_email, $contact_phone, $website, $price, $status, $id]);
        return $stmt->rowCount();
    }

    public function deleteEventSuggestion($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM events WHERE id = ? AND source = ?');
        $stmt->execute([$id, 'user_submission']);
        return $stmt->rowCount();
    }

    // Metodi per Business
    public function getBusinesses($limit = null, $onlyApproved = true) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT b.*, c.name as category_name, p.name as province_name FROM businesses b LEFT JOIN categories c ON b.category_id = c.id LEFT JOIN provinces p ON b.province_id = p.id';
        $params = [];
        if ($onlyApproved) {
            $sql .= ' WHERE b.status = ?';
            $params[] = 'approved';
        }
        $sql .= ' ORDER BY b.name';
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getBusinessById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT * FROM businesses WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createBusiness($name, $email, $phone, $website, $description, $category_id, $province_id, $city_id, $address, $status) {
        if (!$this->isConnected()) { return false; }
        $sql = "INSERT INTO businesses (name, email, phone, website, description, category_id, province_id, city_id, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $website, $description, $category_id, $province_id, $city_id, $address, $status]);
        return $this->pdo->lastInsertId();
    }

    public function updateBusiness($id, $name, $email, $phone, $website, $description, $category_id, $province_id, $city_id, $address, $status) {
        if (!$this->isConnected()) { return 0; }
        $sql = "UPDATE businesses SET name = ?, email = ?, phone = ?, website = ?, description = ?, category_id = ?, province_id = ?, city_id = ?, address = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $website, $description, $category_id, $province_id, $city_id, $address, $status, $id]);
        return $stmt->rowCount();
    }

    public function deleteBusiness($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM businesses WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    // Metodi per Impostazioni
    public function getSettings() {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('SELECT * FROM settings ORDER BY `key`');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSetting($key) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : null;
    }

    public function setSetting($key, $value, $type = 'text') {
        if (!$this->isConnected()) { return; }
        $stmt = $this->pdo->prepare('INSERT INTO settings (`key`, value, type, updated_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE value = VALUES(value), type = VALUES(type), updated_at = NOW()');
        $stmt->execute([$key, $value, $type]);
    }

    // Metodi per Suggerimenti
    public function createPlaceSuggestion($name, $description, $location, $suggested_by_name, $suggested_by_email, $images_json = null) {
        if (!$this->isConnected()) { return false; }
        $sql = "INSERT INTO place_suggestions (name, description, address, suggested_by_name, suggested_by_email, images, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $description, $location, $suggested_by_name, $suggested_by_email, $images_json]);
        return $this->pdo->lastInsertId();
    }

    public function getPlaceSuggestions($status = null) {
        if (!$this->isConnected()) { return []; }
        $sql = 'SELECT * FROM place_suggestions';
        $params = [];
        if ($status) {
            $sql .= ' WHERE status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPlaceSuggestionById($id) {
        if (!$this->isConnected()) { return null; }
        $stmt = $this->pdo->prepare('SELECT * FROM place_suggestions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updatePlaceSuggestionStatus($id, $status, $admin_notes = null) {
        if (!$this->isConnected()) { return 0; }
        $sql = "UPDATE place_suggestions SET status = ?";
        $params = [$status];
        if ($admin_notes !== null) {
            $sql .= ", admin_notes = ?";
            $params[] = $admin_notes;
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function deletePlaceSuggestion($id) {
        if (!$this->isConnected()) { return 0; }
        $stmt = $this->pdo->prepare('DELETE FROM place_suggestions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    // Metodi per Statistiche
    public function getDatabaseHealth() {
        if (!$this->isConnected()) {
            return [
                'database' => ['path' => "mysql://{$this->host}/{$this->dbname}", 'size' => 'N/A'],
                'counts' => [],
                'statistics' => [],
                'health' => ['checks' => ['databaseAccessible' => false]]
            ];
        }
        $tables = ['articles', 'categories', 'provinces', 'cities', 'comments', 'users', 'businesses', 'events', 'user_uploads', 'business_packages', 'settings', 'home_sections', 'static_pages'];
        $counts = [];
        foreach ($tables as $table) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM `$table`");
                $stmt->execute();
                $counts[$table] = $stmt->fetch()['count'];
            } catch (PDOException $e) {
                $counts[$table] = 'N/A';
            }
        }

        $featuredArticles = $this->pdo->query('SELECT COUNT(*) as count FROM articles WHERE featured = 1')->fetchColumn();
        $publishedArticles = $this->pdo->query('SELECT COUNT(*) as count FROM articles WHERE status = "published"')->fetchColumn();
        $totalViews = $this->pdo->query('SELECT SUM(views) as total FROM articles')->fetchColumn() ?: 0;

        $stmt = $this->pdo->prepare("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?");
        $stmt->execute([$this->dbname]);
        $sizeInfo = $stmt->fetch();
        $sizeMB = $sizeInfo['size_mb'] ?? 0;

        return [
            'database' => ['path' => "mysql://{$this->host}/{$this->dbname}", 'size' => $sizeMB . ' MB'],
            'counts' => $counts,
            'statistics' => ['articles' => ['total' => $counts['articles'], 'published' => $publishedArticles, 'featured' => $featuredArticles, 'totalViews' => $totalViews]],
            'health' => ['checks' => ['databaseAccessible' => true, 'integrityOk' => true, 'hasCategories' => ($counts['categories'] ?? 0) > 0, 'hasProvinces' => ($counts['provinces'] ?? 0) > 0, 'hasCities' => ($counts['cities'] ?? 0) > 0]]
        ];
    }

    // Metodi per Backup
    public function createBackup() {
        if (!$this->isConnected()) { return false; }
        $backupDir = dirname(__DIR__) . '/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . "/passione_calabria_mysql_backup_$timestamp.sql";
        $command = sprintf('mysqldump --host=%s --user=%s --password=%s %s > %s', escapeshellarg($this->host), escapeshellarg($this->username), escapeshellarg($this->password), escapeshellarg($this->dbname), escapeshellarg($backupFile));
        @exec($command, $output, $return_var);
        return ($return_var === 0 && file_exists($backupFile) && filesize($backupFile) > 0) ? $backupFile : false;
    }

    public function getBackups() {
        if (!$this->isConnected()) { return []; }
        $backupDir = dirname(__DIR__) . '/backups';
        if (!is_dir($backupDir)) return [];
        $backups = [];
        $files = glob($backupDir . '/*mysql*.sql') ?: [];
        foreach ($files as $file) {
            $backups[] = ['filename' => basename($file), 'size' => filesize($file), 'created' => date('c', filemtime($file)), 'sizeFormatted' => number_format(filesize($file) / (1024 * 1024), 2) . ' MB'];
        }
        usort($backups, fn($a, $b) => strtotime($b['created']) - strtotime($a['created']));
        return $backups;
    }
    
    // NUOVE FUNZIONI PER CREDITI
    public function getConsumptionPackages() {
        if (!$this->checkConnection()) {
            return [];
        }
        $stmt = $this->pdo->prepare('
            SELECT * FROM business_packages 
            WHERE is_active = 1 AND package_type = "consumption"
            ORDER BY id ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBusinessCreditBalance($businessId) {
        if (!$this->checkConnection()) {
            return 0;
        }
        $stmt = $this->pdo->prepare('
            SELECT SUM(credits_remaining) as total_credits 
            FROM consumption_purchases 
            WHERE business_id = ? AND status = "completed" AND (expires_at IS NULL OR expires_at > NOW())
        ');
        $stmt->execute([$businessId]);
        $result = $stmt->fetch();
        return $result['total_credits'] ?? 0;
    }

    public function purchaseCreditPackage($businessId, $packageId) {
        if (!$this->checkConnection()) {
            return false;
        }
        try {
            // Get package details
            $stmt = $this->pdo->prepare('SELECT * FROM business_packages WHERE id = ? AND package_type = "consumption"');
            $stmt->execute([$packageId]);
            $package = $stmt->fetch();

            if (!$package) {
                throw new Exception("Pacchetto crediti non trovato.");
            }

            // Inserisci l'acquisto (simulato, senza scadenza per ora)
            $stmt = $this->pdo->prepare('
                INSERT INTO consumption_purchases (business_id, package_id, credits_purchased, credits_remaining, amount_paid, status, purchased_at)
                VALUES (?, ?, ?, ?, ?, "completed", NOW())
            ');
            return $stmt->execute([
                $businessId,
                $packageId,
                $package['consumption_credits'],
                $package['consumption_credits'], // All credits are remaining initially
                $package['price']
            ]);
        } catch (Exception $e) {
            error_log('Errore acquisto crediti: ' . $e->getMessage());
            return false;
        }
    }

    public function getCreditUsageHistory($businessId, $limit = 50) {
        if (!$this->checkConnection()) {
            return [];
        }
        $stmt = $this->pdo->prepare('
            SELECT 
                cu.*, 
                cp.package_id,
                bp.name as package_name,
                cp.credits_remaining as current_remaining_credits,
                cp.credits_purchased as original_credits,
                cp.purchased_at as package_purchased_at,
                b.name as business_name
            FROM credit_usage cu
            LEFT JOIN consumption_purchases cp ON cu.purchase_id = cp.id
            LEFT JOIN business_packages bp ON cp.package_id = bp.id
            LEFT JOIN businesses b ON cu.business_id = b.id
            WHERE cu.business_id = ?
            ORDER BY cu.used_at DESC
            LIMIT ?
        ');
        $stmt->execute([$businessId, $limit]);
        return $stmt->fetchAll();
    }

    public function getDetailedCreditUsageForAdmin($businessId, $limit = 100) {
        if (!$this->checkConnection()) {
            return [];
        }
        $stmt = $this->pdo->prepare('
            SELECT 
                cu.*, 
                cp.package_id,
                cp.id as purchase_id,
                bp.name as package_name,
                cp.credits_remaining as current_remaining_credits,
                cp.credits_purchased as original_credits,
                cp.purchased_at as package_purchased_at,
                cp.amount_paid,
                b.name as business_name,
                b.email as business_email
            FROM credit_usage cu
            LEFT JOIN consumption_purchases cp ON cu.purchase_id = cp.id
            LEFT JOIN business_packages bp ON cp.package_id = bp.id
            LEFT JOIN businesses b ON cu.business_id = b.id
            WHERE cu.business_id = ?
            ORDER BY cu.used_at DESC
            LIMIT ?
        ');
        $stmt->execute([$businessId, $limit]);
        return $stmt->fetchAll();
    }


    // NUOVA FUNZIONE PER CALCOLARE GUADAGNI PER PERIODO
    public function getRevenueForPeriod($startDate, $endDate) {
        if (!$this->isConnected()) { return 0; }
        
        // Guadagni da abbonamenti nel periodo
        $stmt_subs = $this->pdo->prepare(
            'SELECT SUM(amount) as total FROM subscriptions 
             WHERE status IN ("active", "expired", "cancelled") AND created_at BETWEEN ? AND ?'
        );
        $stmt_subs->execute([$startDate, $endDate]);
        $subscriptionRevenue = $stmt_subs->fetch()['total'] ?: 0;

        // Guadagni da crediti nel periodo
        $stmt_credits = $this->pdo->prepare(
            'SELECT SUM(amount_paid) as total FROM consumption_purchases 
             WHERE status = "completed" AND purchased_at BETWEEN ? AND ?'
        );
        $stmt_credits->execute([$startDate, $endDate]);
        $consumptionRevenue = $stmt_credits->fetch()['total'] ?: 0;

        // Guadagni da comuni nel periodo
        $stmt_comuni = $this->pdo->prepare(
            'SELECT SUM(importo_pagato) as total FROM comuni
             WHERE data_pagamento BETWEEN ? AND ?'
        );
        $stmt_comuni->execute([$startDate, $endDate]);
        $comuniRevenue = $stmt_comuni->fetch()['total'] ?: 0;

        return $subscriptionRevenue + $consumptionRevenue + $comuniRevenue;
    }

    // NUOVE FUNZIONI PER MAPPE CON MARKER DEGLI ARTICOLI
    public function getAllArticlesWithCoordinates() {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('
            SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.latitude, a.longitude, 
                   c.name as category_name, c.icon as category_icon,
                   p.name as province_name, ci.name as city_name
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN provinces p ON a.province_id = p.id 
            LEFT JOIN cities ci ON a.city_id = ci.id 
            WHERE a.status = "published" AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL 
                  AND a.latitude != 0 AND a.longitude != 0
            ORDER BY a.created_at DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getArticlesWithCoordinatesByProvince($provinceId) {
        if (!$this->isConnected()) { return []; }
        $stmt = $this->pdo->prepare('
            SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.latitude, a.longitude, 
                   c.name as category_name, c.icon as category_icon,
                   p.name as province_name, ci.name as city_name
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN provinces p ON a.province_id = p.id 
            LEFT JOIN cities ci ON a.city_id = ci.id 
            WHERE a.status = "published" AND a.province_id = ? 
                  AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL 
                  AND a.latitude != 0 AND a.longitude != 0
            ORDER BY a.created_at DESC
        ');
        $stmt->execute([$provinceId]);
        return $stmt->fetchAll();
    }

    public function __destruct() {
        $this->pdo = null;
    }
}
?>