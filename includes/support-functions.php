<?php
/**
 * Support Functions and Database Migrations Helper
 * Reuses existing DB configuration and connections, runs schema updates, and handles core ticket features.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Gets the PDO database connection, reusing the global $pdo instance if available.
 */
function get_db_connection() {
    global $pdo;
    if (isset($pdo) && $pdo instanceof PDO) {
        return $pdo;
    }
    
    // Parse index.php configuration constants dynamically
    $db_host = 'localhost';
    $db_port = '5432';
    $db_name = 'whatif_db';
    $db_user = 'postgres';
    $db_pass = 'postgres123';
    
    $index_path = dirname(__DIR__) . '/index.php';
    if (file_exists($index_path)) {
        $index_code = file_get_contents($index_path);
        if (preg_match("/define\(\s*['\"]DB_HOST['\"]\s*,\s*['\"](.*?)['\"]\s*\)/i", $index_code, $matches)) {
            $db_host = $matches[1];
        }
        if (preg_match("/define\(\s*['\"]DB_PORT['\"]\s*,\s*['\"](.*?)['\"]\s*\)/i", $index_code, $matches)) {
            $db_port = $matches[1];
        }
        if (preg_match("/define\(\s*['\"]DB_NAME['\"]\s*,\s*['\"](.*?)['\"]\s*\)/i", $index_code, $matches)) {
            $db_name = $matches[1];
        }
        if (preg_match("/define\(\s*['\"]DB_USER['\"]\s*,\s*['\"](.*?)['\"]\s*\)/i", $index_code, $matches)) {
            $db_user = $matches[1];
        }
        if (preg_match("/define\(\s*['\"]DB_PASS['\"]\s*,\s*['\"](.*?)['\"]\s*\)/i", $index_code, $matches)) {
            $db_pass = $matches[1];
        }
    }
    
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
    try {
        $conn = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $conn;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Runs all database migrations self-healingly.
 */
function run_migrations($pdo) {
    if (!$pdo) return;
    
    try {
        // 1. Alter users table to add role & address columns if they don't exist
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'Customer'");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT");
        
        // 2. Alter restaurants table to add owner_id & address columns
        $pdo->exec("ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS owner_id BIGINT REFERENCES users(id) ON DELETE SET NULL");
        $pdo->exec("ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS address VARCHAR(255)");
        
        // 3. Alter menu_items table to add created_at
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT NOW()");
        
        // 4. Create support_tickets table
        $pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
            id SERIAL PRIMARY KEY,
            customer_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            order_id INTEGER REFERENCES orders(id) ON DELETE SET NULL,
            subject VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            priority VARCHAR(20) DEFAULT 'Medium',
            message TEXT NOT NULL,
            status VARCHAR(30) DEFAULT 'Open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // 5. Create support_replies table
        $pdo->exec("CREATE TABLE IF NOT EXISTS support_replies (
            id SERIAL PRIMARY KEY,
            ticket_id INTEGER NOT NULL REFERENCES support_tickets(id) ON DELETE CASCADE,
            user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            admin_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // 6. Create deliveries table
        $pdo->exec("CREATE TABLE IF NOT EXISTS deliveries (
            id SERIAL PRIMARY KEY,
            order_id BIGINT REFERENCES orders(id) ON DELETE CASCADE,
            delivery_partner VARCHAR(255) NOT NULL,
            status VARCHAR(50) DEFAULT 'Assigned',
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            delivered_at TIMESTAMP DEFAULT NULL
        )");
        
        // 7. Create export_logs table
        $pdo->exec("CREATE TABLE IF NOT EXISTS export_logs (
            id SERIAL PRIMARY KEY,
            admin_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            export_type VARCHAR(100),
            exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45)
        )");
        
        // Seed default Admin user if not present
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@whatif.com'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $ins = $pdo->prepare("INSERT INTO users (name, phone, email, role) VALUES ('Admin User', '9999999999', 'admin@whatif.com', 'Admin')");
            $ins->execute();
        }
        
        // Seed default Restaurant Owner if not present
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'owner@whatif.com'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $ins = $pdo->prepare("INSERT INTO users (name, phone, email, role) VALUES ('Restaurant Owner', '8888888888', 'owner@whatif.com', 'Restaurant Owner')");
            $ins->execute();
        }
        
        // Connect seeded Restaurant Owner to existing restaurants and add default addresses if empty
        $stmt_owner = $pdo->prepare("SELECT id FROM users WHERE email = 'owner@whatif.com'");
        $stmt_owner->execute();
        $owner_id = $stmt_owner->fetchColumn();
        
        if ($owner_id) {
            $pdo->prepare("UPDATE restaurants SET owner_id = :owner_id WHERE owner_id IS NULL")->execute([':owner_id' => $owner_id]);
        }
        
        $pdo->exec("UPDATE restaurants SET address = '12th Main Rd, Koramangala, Bangalore' WHERE address IS NULL");
        
    } catch (Exception $e) {
        // Silently capture issues in auto-migrations
    }
}

/**
 * Validate customer role or redirect
 */
function require_customer_auth() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../../index.php");
        exit;
    }
}

/**
 * Validate admin role or redirect
 */
function require_admin_auth() {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'Admin')) {
        header("Location: ../../index.php");
        exit;
    }
}

/**
 * Generates and returns a CSRF token for forms
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the CSRF token
 */
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Runs HTML safe escaping
 */
function sanitize_html($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
