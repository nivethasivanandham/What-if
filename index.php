<?php

/**
 * --- POSTGRESQL LOCAL SETUP INSTRUCTIONS ---
 * 1. Download & install PostgreSQL from https://www.postgresql.org/download/windows/
 * 2. During install: set password for 'postgres' user, keep port 5432
 * 3. Open XAMPP php.ini -> uncomment: extension=pdo_pgsql and extension=pgsql
 * 4. Restart Apache from XAMPP Control Panel
 * 5. Open pgAdmin or psql and create database: CREATE DATABASE whatif_db;
 * 6. Visit index.php - tables and seed data will auto-create
 */

session_start();

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'whatif_db');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres123'); // change this to your actual PostgreSQL password
define('DB_DSN',  'pgsql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME);

// Helper to return JSON responses
function respond_json($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Connect to Database and Run Schema Migrations
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Show a beautifully styled HTML error page with step-by-step setup instructions
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error – What If</title>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
            :root {
                --red: #e23744;
                --red-dark: #c0392b;
                --bg: #0f0a0a;
                --card-bg: #1e1313;
                --text: #ffffff;
                --muted: #a3a3a3;
                --border: #3d2525;
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'DM Sans', sans-serif;
                background: linear-gradient(135deg, #0f0a0a 0%, #201010 100%);
                color: var(--text);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }
            .error-container {
                max-width: 650px;
                width: 100%;
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 20px 50px rgba(0,0,0,0.5);
                text-align: center;
            }
            .icon-wrapper {
                width: 70px;
                height: 70px;
                background: rgba(226, 55, 68, 0.15);
                border: 2px solid var(--red);
                color: var(--red);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                margin: 0 auto 24px;
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(226, 55, 68, 0.4); }
                70% { box-shadow: 0 0 0 15px rgba(226, 55, 68, 0); }
                100% { box-shadow: 0 0 0 0 rgba(226, 55, 68, 0); }
            }
            h1 {
                font-family: 'Nunito', sans-serif;
                font-weight: 900;
                font-size: 28px;
                margin-bottom: 12px;
                letter-spacing: -0.5px;
            }
            .error-msg {
                font-size: 14px;
                color: var(--red);
                background: rgba(226, 55, 68, 0.08);
                border: 1px dashed rgba(226, 55, 68, 0.3);
                padding: 12px;
                border-radius: 10px;
                margin-bottom: 30px;
                font-family: monospace;
                word-break: break-all;
            }
            .instructions-title {
                text-align: left;
                font-family: 'Nunito', sans-serif;
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 16px;
                border-bottom: 1px solid var(--border);
                padding-bottom: 8px;
                color: var(--red);
            }
            ol {
                text-align: left;
                list-style-position: inside;
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-bottom: 30px;
            }
            li {
                font-size: 14.5px;
                line-height: 1.5;
                color: var(--muted);
            }
            li strong {
                color: var(--text);
            }
            .code-snippet {
                background: #000000;
                padding: 4px 8px;
                border-radius: 4px;
                font-family: monospace;
                font-size: 13px;
                color: #ffb4b4;
            }
            .btn {
                display: inline-block;
                padding: 12px 28px;
                background: var(--red);
                color: #ffffff;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 700;
                font-size: 14px;
                transition: 0.2s;
                border: none;
                cursor: pointer;
            }
            .btn:hover {
                background: var(--red-dark);
                transform: translateY(-1px);
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="icon-wrapper">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>PostgreSQL Connection Failed</h1>
            <p style="color: var(--muted); font-size: 15px; margin-bottom: 20px;">Could not connect to the database. Please review the setup context below.</p>
            <div class="error-msg"><?php echo htmlspecialchars($e->getMessage()); ?></div>
            
            <h3 class="instructions-title"><i class="fas fa-tools"></i> PostgreSQL Setup Instructions</h3>
            <ol>
                <li>Download & install PostgreSQL from <a href="https://www.postgresql.org/download/windows/" target="_blank" style="color: var(--red);">postgresql.org</a></li>
                <li>During install: set password for <strong>'postgres'</strong> user, keep port <strong>5432</strong></li>
                <li>Open XAMPP <strong>php.ini</strong> &rarr; uncomment: <code class="code-snippet">extension=pdo_pgsql</code> and <code class="code-snippet">extension=pgsql</code></li>
                <li><strong>Restart Apache</strong> from XAMPP Control Panel</li>
                <li>Open pgAdmin or psql and create database: <code class="code-snippet">CREATE DATABASE whatif_db;</code></li>
                <li>Refresh or visit <strong>index.php</strong> — tables and seed data will auto-create</li>
            </ol>
            
            <button class="btn" onclick="window.location.reload()"><i class="fas fa-redo"></i> Retry Connection</button>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($pdo) {
    // 1. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id BIGSERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) UNIQUE,
        email VARCHAR(255) UNIQUE,
        otp VARCHAR(10) DEFAULT NULL,
        otp_expires TIMESTAMP DEFAULT NULL,
        created_at TIMESTAMP DEFAULT NOW()
    )");

    // 2. Restaurants Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurants (
        id BIGSERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        cuisine VARCHAR(255) NOT NULL,
        image TEXT NOT NULL,
        rating NUMERIC(2, 1) NOT NULL DEFAULT 4.0,
        delivery_time INT NOT NULL DEFAULT 30,
        min_order INT NOT NULL DEFAULT 0,
        delivery_fee INT NOT NULL DEFAULT 29,
        is_open BOOLEAN DEFAULT TRUE,
        offer_text VARCHAR(255) DEFAULT NULL
    )");

    // 3. Menu Items Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id BIGSERIAL PRIMARY KEY,
        restaurant_id BIGINT REFERENCES restaurants(id) ON DELETE CASCADE,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price INT NOT NULL,
        image TEXT DEFAULT NULL,
        type VARCHAR(3) NOT NULL DEFAULT 'veg',
        category VARCHAR(100) NOT NULL,
        is_available BOOLEAN DEFAULT TRUE
    )");

    // 4. Orders Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id BIGSERIAL PRIMARY KEY,
        user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
        restaurant_id BIGINT REFERENCES restaurants(id) ON DELETE CASCADE,
        status VARCHAR(30) NOT NULL DEFAULT 'Placed',
        total INT NOT NULL,
        subtotal INT NOT NULL,
        gst INT NOT NULL,
        delivery_fee INT NOT NULL,
        discount INT DEFAULT 0,
        address TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT NOW()
    )");

    // 5. Order Items Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id BIGSERIAL PRIMARY KEY,
        order_id BIGINT REFERENCES orders(id) ON DELETE CASCADE,
        item_id BIGINT,
        name TEXT NOT NULL,
        price INT NOT NULL,
        qty INT NOT NULL DEFAULT 1
    )");

    // 6. Coupons Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id BIGSERIAL PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        discount_type VARCHAR(10) NOT NULL,
        discount_value INT NOT NULL,
        min_order INT NOT NULL DEFAULT 0,
        max_uses INT NOT NULL DEFAULT 9999,
        used_count INT NOT NULL DEFAULT 0,
        expires_at TIMESTAMP DEFAULT NULL
    )");

    // 7. Addresses Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS addresses (
        id BIGSERIAL PRIMARY KEY,
        user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
        label VARCHAR(20) NOT NULL,
        full_address TEXT NOT NULL,
        lat NUMERIC(10, 7) DEFAULT NULL,
        lng NUMERIC(10, 7) DEFAULT NULL
    )");

    // 8. Reviews Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id BIGSERIAL PRIMARY KEY,
        user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
        restaurant_id BIGINT REFERENCES restaurants(id) ON DELETE CASCADE,
        order_id BIGINT REFERENCES orders(id) ON DELETE CASCADE,
        rating INT NOT NULL,
        delivery_rating INT NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT NOW()
    )");
}

// Function to auto-seed database with premium Bangalore food data if empty
function seed_database_if_empty($pdo) {
    if (!$pdo) return;
     // Seed Restaurants
    $stmt = $pdo->query("SELECT COUNT(*) FROM restaurants");
    if ($stmt->fetchColumn() == 0) {
        $restaurants = [
            ['name' => 'Truffles', 'cuisine' => 'Burgers, American, Desserts', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 'rating' => 4.8, 'delivery_time' => 25, 'min_order' => 100, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => '60% OFF up to ₹120'],
            ['name' => 'Social', 'cuisine' => 'Continental, North Indian, Drinks', 'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80', 'rating' => 4.6, 'delivery_time' => 35, 'min_order' => 150, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => 'FLAT ₹125 OFF'],
            ['name' => 'Malgudi Café', 'cuisine' => 'South Indian, Filter Coffee, Breakfast', 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=600&q=80', 'rating' => 4.7, 'delivery_time' => 20, 'min_order' => 50, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => '20% OFF'],
            ['name' => 'Pizza Hut', 'cuisine' => 'Pizzas, Pastas, Garlic Breads', 'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80', 'rating' => 4.2, 'delivery_time' => 30, 'min_order' => 200, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => 'BUY 1 GET 1'],
            ['name' => 'Natural Ice Cream', 'cuisine' => 'Ice Cream, Desserts, Shakes', 'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=300&q=80', 'rating' => 4.9, 'delivery_time' => 15, 'min_order' => 80, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => 'PRO DISCOUNTS'],
            ['name' => 'Meghana Foods', 'cuisine' => 'Biryani, Kebabs, North Indian', 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=600&q=80', 'rating' => 4.5, 'delivery_time' => 40, 'min_order' => 100, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => '30% OFF up to ₹75'],
            ['name' => 'The Hole in the Wall Café', 'cuisine' => 'Waffles, Continental, Beverages', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80', 'rating' => 4.4, 'delivery_time' => 30, 'min_order' => 150, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => 'FLAT ₹100 OFF'],
            ['name' => 'Vasudev Adigas', 'cuisine' => 'South Indian, Chaat, Snacks', 'image' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80', 'rating' => 4.3, 'delivery_time' => 20, 'min_order' => 50, 'delivery_fee' => 29, 'is_open' => true, 'offer_text' => 'EXTRA ₹50 OFF']
        ];
        
        $ins = $pdo->prepare("INSERT INTO restaurants (name, cuisine, image, rating, delivery_time, min_order, delivery_fee, is_open, offer_text) VALUES (:name, :cuisine, :image, :rating, :delivery_time, :min_order, :delivery_fee, :is_open, :offer_text)");
        foreach ($restaurants as $r) {
            $ins->execute($r);
        }

        // Get seeded restaurant IDs
        $r_ids = [];
        $stmt = $pdo->query("SELECT id, name FROM restaurants");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $r_ids[$row['name']] = $row['id'];
        }

        // Seed Menu Items
        $menu_items = [
            // Truffles Menu
            ['rest' => 'Truffles', 'name' => 'Classic Cheeseburger', 'desc' => 'Chicken patty, melting cheddar, lettuce, tomato, special dressing', 'price' => 349.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80'],
            ['rest' => 'Truffles', 'name' => 'Mushroom Swiss Burger', 'desc' => 'Sautéed butter mushrooms, swiss cheese, rich garlic aioli', 'price' => 299.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1550317138-10000687a72b?w=300&q=80'],
            ['rest' => 'Truffles', 'name' => 'Truffles Special Brownie', 'desc' => 'Warm chocolate fudge brownie loaded with chocolate syrup', 'price' => 199.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1607920591413-4ec007e70023?w=300&q=80'],
            ['rest' => 'Truffles', 'name' => 'Loaded Nachos', 'desc' => 'Crunchy tortilla chips with hot melted cheese dip, jalapeños, salsa', 'price' => 249.00, 'type' => 'veg', 'category' => '🍟 Sides & Starters', 'image' => null],
            ['rest' => 'Truffles', 'name' => 'Chicken Wings (6 pcs)', 'desc' => 'Crispy fried wings tossed in authentic fiery buffalo sauce', 'price' => 319.00, 'type' => 'nv', 'category' => '🍟 Sides & Starters', 'image' => null],
            ['rest' => 'Truffles', 'name' => 'Cold Coffee', 'desc' => 'Rich creamy blend of gourmet espresso & dynamic chilled milk', 'price' => 149.00, 'type' => 'veg', 'category' => '🥤 Beverages', 'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=300&q=80'],
            ['rest' => 'Truffles', 'name' => 'Mango Smoothie', 'desc' => 'Sweet fresh Alphonso mangoes blended with dynamic yogurt', 'price' => 169.00, 'type' => 'veg', 'category' => '🥤 Beverages', 'image' => null],

            // Malgudi Café Menu
            ['rest' => 'Malgudi Café', 'name' => 'Masala Dosa', 'desc' => 'Super crispy rice crepe loaded with butter potato masala & pure ghee', 'price' => 149.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=300&q=80'],
            ['rest' => 'Malgudi Café', 'name' => 'Filter Coffee', 'desc' => 'Traditional strong South Indian frothy milk filter coffee', 'price' => 79.00, 'type' => 'veg', 'category' => '🥤 Beverages', 'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=300&q=80'],
            ['rest' => 'Malgudi Café', 'name' => 'Idli Sambar (2 Pcs)', 'desc' => 'Softest steamed rice idlis served with aromatic red lentil sambar', 'price' => 89.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => null],

            // Pizza Hut Menu
            ['rest' => 'Pizza Hut', 'name' => 'Margherita Pizza', 'desc' => 'Gooey mozzarella cheese, classic premium tomato base, fresh basil oil', 'price' => 349.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=300&q=80'],
            ['rest' => 'Pizza Hut', 'name' => 'Pepperoni Pizza', 'desc' => 'Premium spicy pork pepperoni, heavy mozzarella, signature herb crust', 'price' => 449.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=300&q=80'],

            // Meghana Foods Menu
            ['rest' => 'Meghana Foods', 'name' => 'Chicken Biryani', 'desc' => 'Famous rich basmati rice slowly steam-cooked with spicy Andhra spices and chicken', 'price' => 299.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=300&q=80'],
            ['rest' => 'Meghana Foods', 'name' => 'Mutton Biryani', 'desc' => 'Tender local lamb chunks cooked in special Mughlai aromatic herbs & premium basmati rice', 'price' => 399.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=300&q=80'],

            // Natural Ice Cream Menu
            ['rest' => 'Natural Ice Cream', 'name' => 'Gulab Jamun Pack', 'desc' => 'Warm, sweet, soft gulab jamuns dipped in rose cardamon syrup', 'price' => 99.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=300&q=80'],
            ['rest' => 'Natural Ice Cream', 'name' => 'Tender Coconut Ice Cream', 'desc' => 'Signature rich ice cream churned with actual fresh sweet coconut chunks', 'price' => 129.00, 'type' => 'veg', 'category' => '🍨 Ice Creams', 'image' => null],

            // Social Menu
            ['rest' => 'Social', 'name' => 'Paneer Tikka', 'desc' => 'Tandoor cooked spicy marinated fresh malai cottage cheese cubes', 'price' => 349.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=80'],
            ['rest' => 'Social', 'name' => 'Chicken Wings', 'desc' => 'Social special crispy chicken wings glazed in barbecue sweet and spicy herbs', 'price' => 319.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80'],

            // The Hole in the Wall Menu
            ['rest' => 'The Hole in the Wall Café', 'name' => 'Belgian Waffle', 'desc' => 'Golden grid butter waffle dusted with powdered sugar and premium maple syrup', 'price' => 249.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=80'],

            // Vasudev Adigas Menu
            ['rest' => 'Vasudev Adigas', 'name' => 'Idli Vada Combo', 'desc' => 'Super duo combo containing one softest idli and one crispy medu vada', 'price' => 119.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=300&q=80']
        ];

        $ins_item = $pdo->prepare("INSERT INTO menu_items (restaurant_id, name, description, price, image, type, category) VALUES (:restaurant_id, :name, :description, :price, :image, :type, :category)");
        foreach ($menu_items as $mi) {
            if (isset($r_ids[$mi['rest']])) {
                $ins_item->execute([
                    ':restaurant_id' => $r_ids[$mi['rest']],
                    ':name' => $mi['name'],
                    ':description' => $mi['desc'],
                    ':price' => $mi['price'],
                    ':image' => $mi['image'],
                    ':type' => $mi['type'],
                    ':category' => $mi['category']
                ]);
            }
        }
    }

    // Seed Coupons
    $stmt = $pdo->query("SELECT COUNT(*) FROM coupons");
    if ($stmt->fetchColumn() == 0) {
        $coupons = [
            ['code' => 'WELCOME40', 'discount_type' => 'pct', 'discount_value' => 40.00, 'min_order' => 0.00, 'max_uses' => 1000],
            ['code' => 'FREEDEL', 'discount_type' => 'flat', 'discount_value' => 29.00, 'min_order' => 299.00, 'max_uses' => 1000],
            ['code' => 'HDFC20', 'discount_type' => 'pct', 'discount_value' => 20.00, 'min_order' => 400.00, 'max_uses' => 1000],
            ['code' => 'FLASH30', 'discount_type' => 'pct', 'discount_value' => 30.00, 'min_order' => 0.00, 'max_uses' => 1000],
            ['code' => 'WEEKEND15', 'discount_type' => 'pct', 'discount_value' => 15.00, 'min_order' => 250.00, 'max_uses' => 1000],
            ['code' => 'GOPRO', 'discount_type' => 'flat', 'discount_value' => 50.00, 'min_order' => 199.00, 'max_uses' => 1000]
        ];

        $ins_c = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses) VALUES (:code, :discount_type, :discount_value, :min_order, :max_uses)");
        foreach ($coupons as $c) {
            $ins_c->execute($c);
        }
    }

    // Self-healing check for TOP PICKS foods to ensure they always exist in the database
    $required_picks = [
        ['rest' => 'Truffles', 'name' => 'Chicken Burger', 'desc' => 'Crispy breaded chicken patty, spicy mayo, crisp lettuce, seeded bun', 'price' => 249, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80'],
        ['rest' => 'Vasudev Adigas', 'name' => 'Pav Bhaji', 'desc' => 'Thick vegetable curry (bhaji) served with soft butter-toasted bread rolls (pav)', 'price' => 149, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=300&q=80'],
        ['rest' => 'Natural Ice Cream', 'name' => 'Gulab Jamun', 'desc' => 'Two soft melt-in-the-mouth cardamon infused warm milk dumplings', 'price' => 99, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=300&q=80']
    ];

    foreach ($required_picks as $rp) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE name = :name");
        $check->execute([':name' => $rp['name']]);
        if ($check->fetchColumn() == 0) {
            $r_stmt = $pdo->prepare("SELECT id FROM restaurants WHERE name = :rest");
            $r_stmt->execute([':rest' => $rp['rest']]);
            $r_id = $r_stmt->fetchColumn();
            if ($r_id) {
                $ins = $pdo->prepare("INSERT INTO menu_items (restaurant_id, name, description, price, image, type, category) VALUES (:restaurant_id, :name, :description, :price, :image, :type, :category)");
                $ins->execute([
                    ':restaurant_id' => $r_id,
                    ':name' => $rp['name'],
                    ':description' => $rp['desc'],
                    ':price' => $rp['price'],
                    ':image' => $rp['image'],
                    ':type' => $rp['type'],
                    ':category' => $rp['category']
                ]);
            }
        }
    }
}

// Exec seeding
seed_database_if_empty($pdo);

// ── BACKEND AJAX ROUTER ──
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($action)) {
    // 1. send/verify OTP (login_send_otp)
    if ($action === 'login_send_otp') {
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $expiry = date('Y-m-d H:i:s', time() + 300); // 5 mins
        
        if ($pdo) {
            $identifier = !empty($phone) ? $phone : $email;
            $field = !empty($phone) ? 'phone' : 'email';
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE $field = :id");
            $stmt->execute([':id' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                // Auto-create user inline (as Swiggy/Zomato does for seamless OTP login)
                $name = "Food Lover " . substr($identifier, -4);
                $ins = $pdo->prepare("INSERT INTO users (name, phone, email, otp, otp_expires) VALUES (:name, :phone, :email, :otp, :otp_expires)");
                $ins->execute([
                    ':name' => $name,
                    ':phone' => !empty($phone) ? $phone : null,
                    ':email' => !empty($email) ? $email : null,
                    ':otp' => $otp,
                    ':otp_expires' => $expiry
                ]);
            } else {
                $upd = $pdo->prepare("UPDATE users SET otp = :otp, otp_expires = :otp_expires WHERE id = :id");
                $upd->execute([
                    ':otp' => $otp,
                    ':otp_expires' => $expiry,
                    ':id' => $user['id']
                ]);
            }
            respond_json(['success' => true, 'otp' => $otp, 'msg' => 'OTP generated successfully!']);
        } else {
            // Mock mode success
            respond_json(['success' => true, 'otp' => $otp, 'msg' => 'Mock OTP generated!']);
        }
    }

    // 2. login_verify_otp
    if ($action === 'login') {
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
        
        if (!$pdo) {
            // Mock Login
            $name = !empty($email) ? explode('@', $email)[0] : "User_" . substr($phone, -4);
            $_SESSION['user'] = [
                'id' => 999,
                'name' => ucfirst($name),
                'email' => !empty($email) ? $email : "user@example.com",
                'phone' => !empty($phone) ? $phone : "9876543210"
            ];
            respond_json(['success' => true, 'user' => $_SESSION['user']]);
        }
        
        $identifier = !empty($phone) ? $phone : $email;
        $field = !empty($phone) ? 'phone' : 'email';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE $field = :id");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            respond_json(['success' => false, 'msg' => 'User account not found.'], 400);
        }
        
        if ($user['otp'] !== $otp || strtotime($user['otp_expires']) < time()) {
            respond_json(['success' => false, 'msg' => 'Incorrect or expired OTP.'], 400);
        }
        
        // Success
        $pdo->prepare("UPDATE users SET otp = NULL, otp_expires = NULL WHERE id = :id")->execute([':id' => $user['id']]);
        
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone']
        ];
        
        respond_json(['success' => true, 'user' => $_SESSION['user']]);
    }

    // 3. register_send_otp
    if ($action === 'register_send_otp') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        
        if (empty($name) || empty($email) || empty($phone)) {
            respond_json(['success' => false, 'msg' => 'All fields are strictly required.'], 400);
        }
        
        if ($pdo) {
            // Check if phone or email already registered
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR phone = :phone");
            $stmt->execute([':email' => $email, ':phone' => $phone]);
            if ($stmt->fetch()) {
                respond_json(['success' => false, 'msg' => 'Email or mobile number is already registered.'], 400);
            }
        }
        
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $_SESSION['pending_register'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'otp' => $otp,
            'otp_expires' => time() + 300
        ];
        
        respond_json(['success' => true, 'otp' => $otp, 'msg' => 'OTP sent successfully!']);
    }

    // 4. register_verify_otp & create user
    if ($action === 'register') {
        $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
        
        if (!isset($_SESSION['pending_register'])) {
            respond_json(['success' => false, 'msg' => 'No registration session active.'], 400);
        }
        
        $p = $_SESSION['pending_register'];
        
        if ($p['otp'] !== $otp || $p['otp_expires'] < time()) {
            respond_json(['success' => false, 'msg' => 'Incorrect or expired registration OTP.'], 400);
        }
        
        if ($pdo) {
            $ins = $pdo->prepare("INSERT INTO users (name, email, phone) VALUES (:name, :email, :phone) RETURNING id");
            $ins->execute([
                ':name' => $p['name'],
                ':email' => $p['email'],
                ':phone' => $p['phone']
            ]);
            $userId = $ins->fetchColumn();
            
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $p['name'],
                'email' => $p['email'],
                'phone' => $p['phone']
            ];
        } else {
            $_SESSION['user'] = [
                'id' => 999,
                'name' => $p['name'],
                'email' => $p['email'],
                'phone' => $p['phone']
            ];
        }
        
        unset($_SESSION['pending_register']);
        respond_json(['success' => true, 'user' => $_SESSION['user']]);
    }

    // 5. logout
    if ($action === 'logout') {
        unset($_SESSION['user']);
        session_destroy();
        respond_json(['success' => true]);
    }

    // 6. get_restaurants with dynamic prepared statement query
    if ($action === 'get_restaurants') {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $cuisine = isset($_GET['cuisine']) ? trim($_GET['cuisine']) : '';
        $rating = isset($_GET['rating']) ? floatval($_GET['rating']) : 0.0;
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
        
        if (!$pdo) {
            $res = [];
            foreach ($preloaded_restaurants as $r) {
                // Filter by search query q
                if (!empty($q)) {
                    if (stripos($r['name'], $q) === false && stripos($r['cuisine'], $q) === false) {
                        continue;
                    }
                }
                // Filter by cuisine tab
                if (!empty($cuisine)) {
                    if (stripos($r['cuisine'], $cuisine) === false) {
                        continue;
                    }
                }
                // Filter by minimum rating
                if ($rating > 0) {
                    if ($r['rating'] < $rating) {
                        continue;
                    }
                }
                $res[] = $r;
            }
            
            // Apply mock sorting
            if ($sort === 'Rating') {
                usort($res, function($a, $b) { return $b['rating'] <=> $a['rating']; });
            } elseif ($sort === 'Time') {
                usort($res, function($a, $b) { return $a['delivery_time'] <=> $b['delivery_time']; });
            }
            
            respond_json($res);
        }
        
        $sql = "SELECT * FROM restaurants WHERE 1=1";
        $params = [];
        
        if (!empty($q)) {
            $sql .= " AND (name ILIKE :q OR cuisine ILIKE :q2 OR EXISTS (
                SELECT 1 FROM menu_items m 
                WHERE m.restaurant_id = restaurants.id 
                  AND (m.name ILIKE :q3 OR m.description ILIKE :q4)
            ))";
            $params[':q'] = "%$q%";
            $params[':q2'] = "%$q%";
            $params[':q3'] = "%$q%";
            $params[':q4'] = "%$q%";
        }
        if (!empty($cuisine)) {
            $sql .= " AND cuisine ILIKE :cuisine";
            $params[':cuisine'] = "%$cuisine%";
        }
        if ($rating > 0) {
            $sql .= " AND rating >= :rating";
            $params[':rating'] = $rating;
        }
        
        // Sorting logic
        if ($sort === 'Cost Low') {
            $sql .= " ORDER BY COALESCE(substring(offer_text from '\d+')::integer, 0) ASC, name ASC"; 
        } elseif ($sort === 'Cost High') {
            $sql .= " ORDER BY COALESCE(substring(offer_text from '\d+')::integer, 0) DESC, name ASC";
        } elseif ($sort === 'Rating') {
            $sql .= " ORDER BY rating DESC";
        } elseif ($sort === 'Time') {
            $sql .= " ORDER BY delivery_time ASC";
        } else {
            $sql .= " ORDER BY rating DESC"; // Default relevance is highest rated
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        respond_json($res);
    }

    // 7. get_menu items
    if ($action === 'get_menu') {
        $restaurantId = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : 0;
        if (!$pdo) {
            $fallback_menu_items = [
                // Truffles (ID 1)
                1 => [
                    ['name' => 'Classic Cheeseburger', 'description' => 'Chicken patty, melting cheddar, lettuce, tomato, special dressing', 'price' => 349.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80'],
                    ['name' => 'Mushroom Swiss Burger', 'description' => 'Sautéed butter mushrooms, swiss cheese, rich garlic aioli', 'price' => 299.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1550317138-10000687a72b?w=300&q=80'],
                    ['name' => 'Truffles Special Brownie', 'description' => 'Warm chocolate fudge brownie loaded with chocolate syrup', 'price' => 199.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1607920591413-4ec007e70023?w=300&q=80'],
                    ['name' => 'Loaded Nachos', 'description' => 'Crunchy tortilla chips with hot melted cheese dip, jalapeños, salsa', 'price' => 249.00, 'type' => 'veg', 'category' => '🍟 Sides & Starters', 'image' => null],
                    ['name' => 'Chicken Wings (6 pcs)', 'description' => 'Crispy fried wings tossed in authentic fiery buffalo sauce', 'price' => 319.00, 'type' => 'nv', 'category' => '🍟 Sides & Starters', 'image' => null],
                    ['name' => 'Cold Coffee', 'description' => 'Rich creamy blend of gourmet espresso & dynamic chilled milk', 'price' => 149.00, 'type' => 'veg', 'category' => '🥤 Beverages', 'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=300&q=80'],
                    ['name' => 'Mango Smoothie', 'description' => 'Sweet fresh Alphonso mangoes blended with dynamic yogurt', 'price' => 169.00, 'type' => 'veg', 'category' => '🥤 Beverages', 'image' => null]
                ],
                // Social (ID 2)
                2 => [
                    ['name' => 'Paneer Tikka', 'description' => 'Tandoor cooked spicy marinated fresh malai cottage cheese cubes', 'price' => 349.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=80'],
                    ['name' => 'Chicken Wings', 'description' => 'Social special crispy chicken wings glazed in barbecue sweet and spicy herbs', 'price' => 319.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80']
                ],
                // Malgudi Café (ID 3)
                3 => [
                    ['name' => 'Masala Dosa', 'description' => 'Super crispy rice crepe loaded with butter potato masala & pure ghee', 'price' => 149.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=300&q=80'],
                    ['name' => 'Filter Coffee', 'description' => 'Traditional strong South Indian frothy milk filter coffee', 'price' => 79.00, 'type' => 'veg', 'category' => '🥤 Beverages', 'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=300&q=80'],
                    ['name' => 'Idli Sambar (2 Pcs)', 'description' => 'Softest steamed rice idlis served with aromatic red lentil sambar', 'price' => 89.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => null]
                ],
                // Pizza Hut (ID 4)
                4 => [
                    ['name' => 'Margherita Pizza', 'description' => 'Gooey mozzarella cheese, classic premium tomato base, fresh basil oil', 'price' => 349.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=300&q=80'],
                    ['name' => 'Pepperoni Pizza', 'description' => 'Premium spicy pork pepperoni, heavy mozzarella, signature herb crust', 'price' => 449.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=300&q=80']
                ],
                // Natural Ice Cream (ID 5)
                5 => [
                    ['name' => 'Gulab Jamun Pack', 'description' => 'Warm, sweet, soft gulab jamuns dipped in rose cardamon syrup', 'price' => 99.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=300&q=80'],
                    ['name' => 'Tender Coconut Ice Cream', 'description' => 'Signature rich ice cream churned with actual fresh sweet coconut chunks', 'price' => 129.00, 'type' => 'veg', 'category' => '🍨 Ice Creams', 'image' => null]
                ],
                // Meghana Foods (ID 6)
                6 => [
                    ['name' => 'Chicken Biryani', 'description' => 'Famous rich basmati rice slowly steam-cooked with spicy Andhra spices and chicken', 'price' => 299.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=300&q=80'],
                    ['name' => 'Mutton Biryani', 'description' => 'Tender local lamb chunks cooked in special Mughlai aromatic herbs & premium basmati rice', 'price' => 399.00, 'type' => 'nv', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=300&q=80']
                ],
                // The Hole in the Wall Café (ID 7)
                7 => [
                    ['name' => 'Belgian Waffle', 'description' => 'Golden grid butter waffle dusted with powdered sugar and premium maple syrup', 'price' => 249.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=80']
                ],
                // Vasudev Adigas (ID 8)
                8 => [
                    ['name' => 'Idli Vada Combo', 'description' => 'Super duo combo containing one softest idli and one crispy medu vada', 'price' => 119.00, 'type' => 'veg', 'category' => '🔥 Bestsellers', 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=300&q=80']
                ]
            ];
            
            $items = isset($fallback_menu_items[$restaurantId]) ? $fallback_menu_items[$restaurantId] : [];
            $grouped = [];
            foreach ($items as $item) {
                $grouped[$item['category']][] = $item;
            }
            respond_json($grouped);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = :restaurant_id AND is_available = TRUE");
        $stmt->execute([':restaurant_id' => $restaurantId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by category for cleaner display
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item['category']][] = $item;
        }
        
        respond_json($grouped);
    }

    // 8. place_order
    if ($action === 'place_order') {
        if (!isset($_SESSION['user'])) {
            respond_json(['success' => false, 'msg' => 'Please sign in to order.'], 401);
        }
        
        $restaurantId = isset($_POST['restaurant_id']) ? intval($_POST['restaurant_id']) : 0;
        $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
        $deliveryFee = isset($_POST['delivery_fee']) ? floatval($_POST['delivery_fee']) : 29;
        $gst = isset($_POST['gst']) ? floatval($_POST['gst']) : 0;
        $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
        $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
        $items = isset($_POST['items']) ? $_POST['items'] : []; // JSON decoded array
        
        if (empty($address) || empty($items) || $restaurantId == 0) {
            respond_json(['success' => false, 'msg' => 'Invalid order details.'], 400);
        }
        
        if ($pdo) {
            try {
                $pdo->beginTransaction();
                
                // If coupon applied, increment its count
                if (!empty($couponCode)) {
                    $upd_c = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = :code");
                    $upd_c->execute([':code' => $couponCode]);
                }
                
                // Insert order
                $ins_o = $pdo->prepare("INSERT INTO orders (user_id, restaurant_id, status, subtotal, delivery_fee, gst, discount, total, address) VALUES (:user_id, :restaurant_id, 'Placed', :subtotal, :delivery_fee, :gst, :discount, :total, :address) RETURNING id");
                $ins_o->execute([
                    ':user_id' => $_SESSION['user']['id'],
                    ':restaurant_id' => $restaurantId,
                    ':subtotal' => $subtotal,
                    ':delivery_fee' => $deliveryFee,
                    ':gst' => $gst,
                    ':discount' => $discount,
                    ':total' => $total,
                    ':address' => $address
                ]);
                $orderId = $ins_o->fetchColumn();
                
                // Insert items
                $ins_i = $pdo->prepare("INSERT INTO order_items (order_id, item_id, name, price, qty) VALUES (:order_id, :item_id, :name, :price, :qty)");
                foreach ($items as $item) {
                    $ins_i->execute([
                        ':order_id' => $orderId,
                        ':item_id' => isset($item['id']) ? intval($item['id']) : null,
                        ':name' => $item['name'],
                        ':price' => floatval($item['price']),
                        ':qty' => intval($item['qty'])
                    ]);
                }
                
                $pdo->commit();
                respond_json(['success' => true, 'order_id' => $orderId, 'msg' => 'Order placed successfully!']);
            } catch (Exception $e) {
                $pdo->rollBack();
                respond_json(['success' => false, 'msg' => 'Failed to place order: ' . $e->getMessage()], 500);
            }
        } else {
            // Mock success order id
            $orderId = mt_rand(100000, 999999);
            respond_json(['success' => true, 'order_id' => $orderId]);
        }
    }

    // 9. get_orders
    if ($action === 'get_orders') {
        if (!isset($_SESSION['user'])) {
            respond_json([]);
        }
        
        if (!$pdo) {
            respond_json([]);
        }
        
        $stmt = $pdo->prepare("SELECT o.*, r.name AS restName, r.image AS restImg FROM orders o JOIN restaurants r ON o.restaurant_id = r.id WHERE o.user_id = :user_id ORDER BY o.id DESC");
        $stmt->execute([':user_id' => $_SESSION['user']['id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch items for each order
        $stmt_i = $pdo->prepare("SELECT name, price, qty FROM order_items WHERE order_id = :order_id");
        
        foreach ($orders as &$order) {
            $stmt_i->execute([':order_id' => $order['id']]);
            $order['items'] = $stmt_i->fetchAll(PDO::FETCH_ASSOC);
        }
        
        respond_json($orders);
    }

    // 9.5. update_order_status
    if ($action === 'update_order_status') {
        if (!isset($_SESSION['user'])) {
            respond_json(['success' => false, 'msg' => 'Unauthorized'], 401);
        }
        $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        
        $allowed_statuses = ['Placed', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
        if ($orderId <= 0 || !in_array($status, $allowed_statuses)) {
            respond_json(['success' => false, 'msg' => 'Invalid details'], 400);
        }
        
        if ($pdo) {
            $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $orderId]);
            respond_json(['success' => true, 'order_id' => $orderId, 'status' => $status]);
        } else {
            respond_json(['success' => true, 'mock' => true]);
        }
    }

    // 10. validate_coupon
    if ($action === 'validate_coupon') {
        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
        
        if (!$pdo) {
            // Mock validation
            if ($code === 'WELCOME40') {
                respond_json(['success' => true, 'discount_type' => 'pct', 'discount_value' => 40, 'max' => 120]);
            }
            respond_json(['success' => false, 'msg' => 'Invalid coupon code.'], 400);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = :code AND (expires_at IS NULL OR expires_at > NOW()) AND used_count < max_uses");
        $stmt->execute([':code' => $code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            respond_json(['success' => false, 'msg' => 'Coupon code is invalid or expired.'], 400);
        }
        
        if ($subtotal < $coupon['min_order']) {
            respond_json(['success' => false, 'msg' => 'Minimum order amount for this coupon is ₹' . intval($coupon['min_order'])], 400);
        }
        
        respond_json([
            'success' => true,
            'discount_type' => $coupon['discount_type'],
            'discount_value' => floatval($coupon['discount_value']),
            'max' => $coupon['discount_type'] === 'pct' ? 120.00 : null
        ]);
    }

    // 11. submit_review
    if ($action === 'submit_review') {
        if (!isset($_SESSION['user'])) {
            respond_json(['success' => false, 'msg' => 'Unauthorized.'], 401);
        }
        
        $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
        $deliveryRating = isset($_POST['delivery_rating']) ? intval($_POST['delivery_rating']) : 5;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        
        if (!$pdo || $orderId == 0) {
            respond_json(['success' => true]);
        }
        
        // Find order restaurant_id
        $stmt = $pdo->prepare("SELECT restaurant_id FROM orders WHERE id = :id");
        $stmt->execute([':id' => $orderId]);
        $restId = $stmt->fetchColumn();
        
        if ($restId) {
            $ins = $pdo->prepare("INSERT INTO reviews (user_id, restaurant_id, order_id, rating, delivery_rating, comment) VALUES (:user_id, :restaurant_id, :order_id, :rating, :delivery_rating, :comment)");
            $ins->execute([
                ':user_id' => $_SESSION['user']['id'],
                ':restaurant_id' => $restId,
                ':order_id' => $orderId,
                ':rating' => $rating,
                ':delivery_rating' => $deliveryRating,
                ':comment' => $comment
            ]);
            
            // Recalculate average restaurant rating
            $stmt_avg = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE restaurant_id = :restaurant_id");
            $stmt_avg->execute([':restaurant_id' => $restId]);
            $newRating = round(floatval($stmt_avg->fetchColumn()), 1);
            if ($newRating > 0) {
                $upd_r = $pdo->prepare("UPDATE restaurants SET rating = :rating WHERE id = :id");
                $upd_r->execute([':rating' => $newRating, ':id' => $restId]);
            }
        }
        
        respond_json(['success' => true]);
    }

    // 12. get_profile
    if ($action === 'get_profile') {
        if (!isset($_SESSION['user'])) {
            respond_json(['success' => false], 401);
        }
        
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $_SESSION['user']['id']]);
            $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $addresses = [];
        }
        
        respond_json([
            'success' => true,
            'user' => $_SESSION['user'],
            'addresses' => $addresses
        ]);
    }
}

// Initial page data preloading via JSON
$preloaded_restaurants = [];
$preloaded_coupons = [];
if ($pdo) {
    $preloaded_restaurants = $pdo->query("SELECT * FROM restaurants ORDER BY rating DESC")->fetchAll(PDO::FETCH_ASSOC);
    $preloaded_coupons = $pdo->query("SELECT * FROM coupons")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fail-safe static mock data to render premium page perfectly even if DB connection fails
    $preloaded_restaurants = [
        ['id' => 1, 'name' => 'Truffles', 'cuisine' => 'Burgers, American, Desserts', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80', 'rating' => 4.8, 'delivery_time' => 25, 'min_order' => 100, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => '60% OFF up to ₹120'],
        ['id' => 2, 'name' => 'Social', 'cuisine' => 'Continental, North Indian, Drinks', 'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80', 'rating' => 4.6, 'delivery_time' => 35, 'min_order' => 150, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => 'FLAT ₹125 OFF'],
        ['id' => 3, 'name' => 'Malgudi Café', 'cuisine' => 'South Indian, Filter Coffee, Breakfast', 'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=600&q=80', 'rating' => 4.7, 'delivery_time' => 20, 'min_order' => 50, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => '20% OFF'],
        ['id' => 4, 'name' => 'Pizza Hut', 'cuisine' => 'Pizzas, Pastas, Garlic Breads', 'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80', 'rating' => 4.2, 'delivery_time' => 30, 'min_order' => 200, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => 'BUY 1 GET 1'],
        ['id' => 5, 'name' => 'Natural Ice Cream', 'cuisine' => 'Ice Cream, Desserts, Shakes', 'image' => 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=600&q=80', 'rating' => 4.9, 'delivery_time' => 15, 'min_order' => 80, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => 'PRO DISCOUNTS'],
        ['id' => 6, 'name' => 'Meghana Foods', 'cuisine' => 'Biryani, Kebabs, North Indian', 'image' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=600&q=80', 'rating' => 4.5, 'delivery_time' => 40, 'min_order' => 100, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => '30% OFF up to ₹75'],
        ['id' => 7, 'name' => 'The Hole in the Wall Café', 'cuisine' => 'Waffles, Continental, Beverages', 'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80', 'rating' => 4.4, 'delivery_time' => 30, 'min_order' => 150, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => 'FLAT ₹100 OFF'],
        ['id' => 8, 'name' => 'Vasudev Adigas', 'cuisine' => 'South Indian, Chaat, Snacks', 'image' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80', 'rating' => 4.3, 'delivery_time' => 20, 'min_order' => 50, 'delivery_fee' => 29, 'is_open' => 1, 'offer_text' => 'EXTRA ₹50 OFF']
    ];
    $preloaded_coupons = [
        ['code' => 'WELCOME40', 'discount_type' => 'pct', 'discount_value' => 40.00, 'min_order' => 0.00, 'max_uses' => 1000],
        ['code' => 'FREEDEL', 'discount_type' => 'flat', 'discount_value' => 29.00, 'min_order' => 299.00, 'max_uses' => 1000],
        ['code' => 'HDFC20', 'discount_type' => 'pct', 'discount_value' => 20.00, 'min_order' => 400.00, 'max_uses' => 1000],
        ['code' => 'FLASH30', 'discount_type' => 'pct', 'discount_value' => 30.00, 'min_order' => 0.00, 'max_uses' => 1000],
        ['code' => 'WEEKEND15', 'discount_type' => 'pct', 'discount_value' => 15.00, 'min_order' => 250.00, 'max_uses' => 1000],
        ['code' => 'GOPRO', 'discount_type' => 'flat', 'discount_value' => 50.00, 'min_order' => 199.00, 'max_uses' => 1000]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>What If – Discover the best food & drinks</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- ── STYLES ── -->
<style>
:root {
  --red: #e23744;
  --red-dark: #c0392b;
  --red-light: #fff0f1;
  --green: #3d9b6e;
  --orange: #f97316;
  --gold: #f59e0b;
  --bg: #ffffff;
  --bg-soft: #f8f8f8;
  --dark: #1c1c1c;
  --mid: #3d3d3d;
  --muted: #6b7280;
  --faint: #9ca3af;
  --border: #e5e7eb;
  --shadow-xs: 0 1px 3px rgba(0,0,0,.08);
  --shadow-sm: 0 2px 8px rgba(0,0,0,.1);
  --shadow-md: 0 4px 20px rgba(0,0,0,.13);
  --shadow-lg: 0 8px 40px rgba(0,0,0,.18);
  --shadow-xl: 0 16px 60px rgba(0,0,0,.22);
  --r-sm: 8px; --r-md: 14px; --r-lg: 20px; --r-xl: 28px;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;color:var(--dark);background:var(--bg);overflow-x:hidden;}

/* SCROLLBAR */
::-webkit-scrollbar{width:6px;height:6px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:#ddd;border-radius:3px;}

/* NAVBAR WITH HAMBURGER */
.navbar{position:fixed;top:0;left:0;right:0;z-index:500;height:62px;background:rgba(255,255,255,.97);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:clamp(10px, 2vw, 16px);padding:0 32px;box-shadow:var(--shadow-xs);transition:0.3s;}
.nav-logo{display:flex;align-items:center;gap:8px;cursor:pointer;text-decoration:none;}
.nav-logo-text{font-family:'Nunito',sans-serif;font-size:21px;font-weight:900;color:var(--red);letter-spacing:-.5px;}
.nav-loc{display:flex;align-items:center;gap:7px;background:#f5f5f5;border:1.5px solid var(--border);border-radius:10px;padding:7px 13px;cursor:pointer;transition:.2s;min-width:200px;max-width:240px;}
.nav-loc:hover{border-color:var(--red);background:var(--red-light);}
.nav-loc i{color:var(--red);font-size:13px;}
.nav-loc-text{font-size:13.5px;font-weight:600;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.nav-search{flex:1;display:flex;align-items:center;gap:8px;background:#f5f5f5;border:1.5px solid var(--border);border-radius:10px;padding:8px 14px;transition:.2s;max-width:440px;}
.nav-search:focus-within{border-color:var(--red);background:#fff;box-shadow:0 0 0 3px rgba(226,55,68,.08);}
.nav-search input{border:none;background:transparent;outline:none;font-size:13.5px;color:var(--dark);width:100%;font-family:'DM Sans',sans-serif;}
.nav-actions{display:flex;align-items:center;gap:clamp(8px, 1.5vw, 16px);margin-left:auto;}
.nav-btn{padding:8px 17px;border-radius:9px;font-size:13.5px;font-weight:700;cursor:pointer;transition:.2s;border:none;font-family:'DM Sans',sans-serif;white-space:nowrap;}
.nav-btn-ghost{background:transparent;color:var(--dark);border:1.5px solid var(--border);}
.nav-btn-ghost:hover{border-color:var(--red);color:var(--red);}
.nav-btn-red{background:var(--red);color:#fff;}
.nav-btn-red:hover{background:var(--red-dark);transform:translateY(-1px);box-shadow:0 4px 14px rgba(226,55,68,.3);}
.nav-cart{position:relative;display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;border:1.5px solid var(--border);cursor:pointer;font-size:13.5px;font-weight:700;background:#fff;transition:.2s;}
.nav-cart:hover{border-color:var(--red);color:var(--red);}
.cart-badge{position:absolute;top:-7px;right:-7px;background:var(--red);color:#fff;border-radius:50%;width:19px;height:19px;font-size:10px;font-weight:800;display:none;align-items:center;justify-content:center;border:2px solid #fff;}
.cart-badge.on{display:flex;}

/* MOBILE MENU HAMBURGER */
.hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;background:none;border:none;}
.hamburger span{width:22px;height:2.5px;background:var(--dark);border-radius:10px;transition:0.3s;}

/* SMOOTH PAGE TRANSITIONS */
.page{display:none;min-height:100vh;padding-top:62px;opacity:0;transform:translateY(12px);transition:opacity 0.35s ease, transform 0.35s ease;}
.page.active{display:block;}
.page.fade-in{opacity:1;transform:translateY(0);}

/* HERO */
.hero{background:linear-gradient(135deg,#1a0a0a 0%,#2d1111 45%,#1a0a0a 100%);min-height:calc(100vh - 62px);position:relative;overflow:hidden;display:flex;align-items:center;padding:clamp(20px, 4vw, 60px);}
.hero-particles{position:absolute;inset:0;overflow:hidden;}
.hero-particles span{position:absolute;width:4px;height:4px;background:rgba(226,55,68,.4);border-radius:50%;animation:float-up linear infinite;}
@keyframes float-up{0%{transform:translateY(100vh) scale(0);opacity:0;}10%{opacity:1;}90%{opacity:.5;}100%{transform:translateY(-10vh) scale(1);opacity:0;}}
.hero-content{position:relative;z-index:2;padding:20px;max-width:640px;}
.hero-tag{display:inline-flex;align-items:center;gap:7px;background:rgba(226,55,68,.15);border:1px solid rgba(226,55,68,.3);border-radius:50px;padding:7px 15px;color:#ff9a9a;font-size:12.5px;font-weight:600;margin-bottom:24px;}
.hero-h1{font-family:'Nunito',sans-serif;font-size:clamp(32px, 5vw, 56px);font-weight:900;color:#fff;line-height:1.1;letter-spacing:-1px;margin-bottom:18px;}
.hero-h1 em{color:var(--red);font-style:normal;}
.hero-p{font-size:clamp(14px, 2vw, 17px);color:rgba(255,255,255,.6);line-height:1.65;margin-bottom:36px;}
.hero-searchbar{background:#fff;border-radius:14px;display:flex;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.45);}
.hero-loc-btn{display:flex;align-items:center;gap:8px;padding:16px 18px;border-right:1px solid var(--border);cursor:pointer;transition:.2s;min-width:140px;}
.hero-loc-btn:hover{background:#f9f9f9;}
.hero-loc-btn i{color:var(--red);}
.hero-loc-btn span{font-size:14px;font-weight:600;}
.hero-search-wrap{flex:1;display:flex;align-items:center;gap:8px;padding:16px 18px;}
.hero-search-wrap input{flex:1;border:none;outline:none;font-size:14px;font-family:'DM Sans',sans-serif;}
.hero-go{background:var(--red);color:#fff;border:none;padding:16px 26px;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;}
.hero-go:hover{background:var(--red-dark);}
.hero-stats{display:flex;gap:clamp(16px, 4vw, 36px);margin-top:40px;}
.hero-stat-val{font-family:'Nunito',sans-serif;font-size:26px;font-weight:900;color:#fff;}
.hero-stat-lbl{font-size:12px;color:rgba(255,255,255,.5);margin-top:2px;}
.hero-right{position:absolute;right:0;top:0;bottom:0;width:50%;display:flex;align-items:center;justify-content:center;pointer-events:none;}
.hero-imgs{display:grid;grid-template-columns:1fr 1fr;gap:14px;transform:rotate(-8deg);}
.hero-img-card{border-radius:18px;overflow:hidden;height:190px;width:180px;box-shadow:0 20px 60px rgba(0,0,0,.5);animation:imgFloat 5s ease-in-out infinite;}
.hero-img-card:nth-child(2){animation-delay:1.2s;margin-top:28px;}
.hero-img-card:nth-child(3){animation-delay:2.4s;}
.hero-img-card:nth-child(4){animation-delay:3.6s;margin-top:28px;}
.hero-img-card img{width:100%;height:100%;object-fit:cover;}
@keyframes imgFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);}}

/* SECTION */
.section{padding:52px clamp(20px, 4vw, 40px);}
.section-alt{background:var(--bg-soft);}
.sec-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;}
.sec-title{font-family:'Nunito',sans-serif;font-size:24px;font-weight:900;color:var(--dark);}
.sec-sub{font-size:13px;color:var(--muted);margin-top:3px;}
.sec-link{color:var(--red);font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px;white-space:nowrap;margin-top:6px;}
.sec-link:hover{text-decoration:underline;}

/* CUISINE GRID */
.cuisine-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:16px;}
.cuisine-card{display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 10px;border-radius:var(--r-md);border:1.5px solid var(--border);background:#fff;cursor:pointer;transition:.25s;text-align:center;}
.cuisine-card:hover{border-color:var(--red);box-shadow:var(--shadow-md);transform:translateY(-4px);}
.cuis-em{font-size:32px;}
.cuis-name{font-size:12.5px;font-weight:700;color:var(--mid);}

/* FILTER TABS */
.filter-row{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:24px;}
.ftab{padding:7px 16px;border-radius:50px;border:1.5px solid var(--border);background:#fff;font-size:12.5px;font-weight:600;color:var(--mid);cursor:pointer;transition:.2s;white-space:nowrap;font-family:'DM Sans',sans-serif;}
.ftab:hover{border-color:var(--red);color:var(--red);}
.ftab.on{background:var(--red);color:#fff;border-color:var(--red);}

/* RESTAURANT GRID (Responsive layout) */
.rest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:22px;}
.rest-card{border-radius:var(--r-lg);overflow:hidden;background:#fff;border:1px solid var(--border);cursor:pointer;transition:.3s;box-shadow:var(--shadow-xs);}
.rest-card:hover{box-shadow:var(--shadow-lg);transform:translateY(-6px);}
.rest-img-wrap{position:relative;height:185px;overflow:hidden;}
.rest-img-wrap img{width:100%;height:100%;object-fit:cover;transition:.4s;}
.rest-card:hover .rest-img-wrap img{transform:scale(1.07);}
.rest-offer{position:absolute;bottom:10px;left:10px;background:rgba(226,55,68,.92);color:#fff;padding:4px 9px;border-radius:6px;font-size:10.5px;font-weight:800;backdrop-filter:blur(4px);}
.rest-promo{position:absolute;bottom:10px;right:10px;background:rgba(0,0,0,.72);color:#fff;padding:4px 9px;border-radius:6px;font-size:10.5px;font-weight:700;backdrop-filter:blur(4px);}
.rest-wish{position:absolute;top:10px;right:10px;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.92);display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;transition:.2s;}
.rest-wish i{color:var(--faint);font-size:13px;}
.rest-wish.on i{color:var(--red);}
.rest-info{padding:14px;}
.rest-name{font-size:15px;font-weight:800;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rest-cuis{font-size:12.5px;color:var(--muted);margin-bottom:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rest-meta{display:flex;align-items:center;gap:10px;padding-top:10px;border-top:1px solid var(--border);}
.rest-rating{display:flex;align-items:center;gap:3px;background:#3d9b6e;color:#fff;padding:3px 7px;border-radius:6px;font-size:11.5px;font-weight:800;}
.rest-rating.org{background:var(--orange);}
.rest-rating.red{background:var(--red);}
.rest-time{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:3px;}
.rest-price{font-size:12px;color:var(--muted);margin-left:auto;}

/* SKELETON LOADERS */
.skeleton-card {border-radius:var(--r-lg);border:1px solid var(--border);overflow:hidden;background:#fff;height:280px;display:flex;flex-direction:column;}
.skeleton-img {height:185px;width:100%;background:linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);background-size:200% 100%;animation:pulse-loading 1.5s infinite;}
.skeleton-info {padding:14px;flex:1;display:flex;flex-direction:column;gap:8px;}
.skeleton-line {height:12px;background:linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);background-size:200% 100%;animation:pulse-loading 1.5s infinite;border-radius:4px;}
.skeleton-line.title {height:16px;width:70%;}
.skeleton-line.subtitle {width:50%;}
.skeleton-line.meta {width:90%;margin-top:auto;}
@keyframes pulse-loading{0%{background-position:200% 0;}100%{background-position:-200% 0;}}

/* OFFERS GRID */
.offers-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px;}
.offer-card{border-radius:var(--r-md);padding:20px;display:flex;align-items:center;gap:16px;border:1.5px solid var(--border);background:#fff;cursor:pointer;transition:.25s;position:relative;overflow:hidden;}
.offer-card:hover{box-shadow:var(--shadow-md);transform:translateY(-3px);border-color:var(--red);}
.offer-icon{width:54px;height:54px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;}
.offer-title{font-size:15px;font-weight:800;margin-bottom:3px;}
.offer-desc{font-size:12px;color:var(--muted);}
.offer-code{margin-top:7px;display:inline-block;background:var(--red-light);border:1px dashed var(--red);color:var(--red);padding:3px 9px;border-radius:4px;font-size:11.5px;font-weight:800;letter-spacing:.5px;}

/* TOP PICKS */
.tp-scroll{display:flex;gap:14px;overflow-x:auto;padding-bottom:10px;scrollbar-width:none;}
.tp-scroll::-webkit-scrollbar{display:none;}
.tp-card{flex-shrink:0;width:150px;border-radius:var(--r-md);overflow:hidden;cursor:pointer;position:relative;transition:.3s;}
.tp-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-md);}
.tp-img{width:100%;height:110px;object-fit:cover;}
.tp-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.72) 0%,transparent 55%);}
.tp-info{position:absolute;bottom:0;left:0;right:0;padding:10px;}
.tp-name{color:#fff;font-size:12.5px;font-weight:800;line-height:1.3;}
.tp-disc{color:#ffd700;font-size:11px;font-weight:700;margin-top:2px;}

/* COLLECTIONS */
.coll-scroll{display:flex;gap:16px;overflow-x:auto;padding-bottom:10px;scrollbar-width:none;}
.coll-scroll::-webkit-scrollbar{display:none;}
.coll-card{flex-shrink:0;width:220px;border-radius:var(--r-md);overflow:hidden;cursor:pointer;position:relative;transition:.3s;}
.coll-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-md);}
.coll-img{width:100%;height:140px;object-fit:cover;}
.coll-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,transparent 55%);}
.coll-info{position:absolute;bottom:0;left:0;right:0;padding:14px;}
.coll-name{color:#fff;font-size:14px;font-weight:800;}
.coll-cnt{color:rgba(255,255,255,.72);font-size:11.5px;margin-top:2px;}

/* CART DRAWER & MODAL STYLES */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1200;opacity:0;pointer-events:none;transition:opacity .3s;}
.overlay.on{opacity:1;pointer-events:all;}
.cart-drawer{position:fixed;top:0;right:-420px;width:400px;height:100vh;background:#fff;z-index:1250;box-shadow:var(--shadow-xl);display:flex;flex-direction:column;transition:right .35s cubic-bezier(.4,0,.2,1);}
.cart-drawer.on{right:0;}
.cart-hd{padding:20px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.cart-hd-title{font-family:'Nunito',sans-serif;font-size:19px;font-weight:900;}
.cart-hd-close{background:none;border:none;font-size:19px;cursor:pointer;color:var(--faint);transition:.2s;}
.cart-rest-strip{padding:10px 22px;background:var(--bg-soft);border-bottom:1px solid var(--border);font-size:12.5px;color:var(--muted);display:none;}
.cart-rest-strip span{color:var(--dark);font-weight:700;}
.cart-body{flex:1;overflow-y:auto;padding:16px 22px;}
.cart-empty-msg{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:12px;text-align:center;}
.cart-empty-msg i{font-size:48px;color:var(--border);}
.cart-item{display:flex;align-items:center;gap:12px;padding:13px 0;border-bottom:1px solid var(--border);}
.veg-dot{width:15px;height:15px;border-radius:3px;border:2px solid;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
.veg-dot.veg{border-color:#3d9b6e;}
.veg-dot.veg::after{content:'';width:7px;height:7px;border-radius:50%;background:#3d9b6e;display:block;}
.veg-dot.nv{border-color:var(--red);}
.veg-dot.nv::after{content:'';width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-bottom:8px solid var(--red);display:block;}
.ci-name{flex:1;font-size:13.5px;font-weight:600;}
.ci-qty{display:flex;align-items:center;gap:6px;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;}
.cq-btn{width:28px;height:28px;border:none;background:none;font-size:15px;font-weight:800;cursor:pointer;color:var(--red);transition:.2s;}
.cq-btn:hover{background:var(--red-light);}
.cq-val{font-size:13.5px;font-weight:700;min-width:18px;text-align:center;}
.ci-price{font-size:13.5px;font-weight:700;min-width:56px;text-align:right;}
.cart-ft{padding:18px 22px;border-top:1px solid var(--border);}
.cart-row{display:flex;justify-content:space-between;font-size:13px;color:var(--muted);margin-bottom:7px;}
.cart-row.total{font-size:15px;font-weight:800;color:var(--dark);padding-top:10px;border-top:1px solid var(--border);margin-top:5px;margin-bottom:18px;}
.checkout-btn{width:100%;padding:15px;background:var(--red);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:800;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px;}
.checkout-btn:hover{background:var(--red-dark);transform:translateY(-2px);box-shadow:0 8px 24px rgba(226,55,68,.3);}
.coupon-row{display:flex;gap:8px;margin-bottom:14px;}
.coupon-input{flex:1;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;outline:none;}
.coupon-input:focus{border-color:var(--red);}
.coupon-apply{padding:10px 16px;background:var(--red);color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;}
.coupon-applied{font-size:12px;color:var(--green);font-weight:600;margin-bottom:8px;display:none;}
.coupon-applied.on{display:block;}

/* BACK TO TOP & WHATSAPP FLOATING BUTTONS */
.back-to-top{position:fixed;bottom:30px;right:30px;width:46px;height:46px;background:var(--red);color:#fff;border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;opacity:0;visibility:hidden;transform:translateY(15px);transition:0.35s cubic-bezier(0.4, 0, 0.2, 1);z-index:999;box-shadow:var(--shadow-md);}
.back-to-top.on{opacity:1;visibility:visible;transform:translateY(0);}
.back-to-top:hover{background:var(--red-dark);transform:scale(1.1);}
.whatsapp-btn{position:fixed;bottom:30px;left:30px;width:52px;height:52px;background:#25d366;color:#fff;border-radius:50%;border:none;display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;z-index:999;box-shadow:var(--shadow-md);transition:0.3s cubic-bezier(0.4, 0, 0.2, 1);}
.whatsapp-btn:hover{transform:scale(1.1) rotate(6deg);background:#20ba59;box-shadow:var(--shadow-lg);}

/* INLINE FORM VALIDATION ERRORS */
.error-msg{color:var(--red);font-size:12px;font-weight:600;margin-top:4px;display:none;text-align:left;}
.is-invalid{border-color:var(--red) !important;background-color:var(--red-light) !important;}

/* EMPTY RESULTS STATE */
.empty-state{text-align:center;padding:60px 20px;color:var(--faint);}
.empty-state i{font-size:56px;margin-bottom:16px;display:block;opacity:.4;}
.empty-state h3{font-size:18px;font-weight:800;color:var(--dark);margin-bottom:6px;}

/* STEP PROGRESS BAR */
.progress-bar-container{display:flex;justify-content:space-between;margin:28px 0 36px;position:relative;}
.progress-line{position:absolute;top:16px;left:0;right:0;height:4px;background:var(--border);z-index:1;transition:0.4s;}
.progress-line-fill{height:100%;background:var(--green);width:0%;transition:0.4s;}
.progress-step{position:relative;z-index:2;display:flex;flex-direction:column;align-items:center;gap:8px;flex:1;}
.ps-circle{width:34px;height:34px;border-radius:50%;border:2.5px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:var(--muted);transition:0.4s;}
.progress-step.done .ps-circle{border-color:var(--green);background:var(--green);color:#fff;}
.progress-step.active .ps-circle{border-color:var(--red);background:var(--red);color:#fff;box-shadow:0 0 0 5px rgba(226,55,68,0.2);animation:activePulse 1.5s infinite;}
.ps-lbl{font-size:12px;font-weight:700;color:var(--muted);transition:0.4s;text-align:center;}
.progress-step.active .ps-lbl{color:var(--red);}
.progress-step.done .ps-lbl{color:var(--green);}
@keyframes activePulse{0%,100%{box-shadow:0 0 0 0 rgba(226,55,68,0.4);}50%{box-shadow:0 0 0 8px rgba(226,55,68,0);}}

/* MENU MODAL & PAYMENT MODAL */
.modal-wrap{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1300;opacity:0;pointer-events:none;transition:opacity .3s;display:flex;align-items:center;justify-content:center;}
.modal-wrap.on{opacity:1;pointer-events:all;}
.modal-box{background:#fff;border-radius:var(--r-xl);padding:32px;width:500px;max-width:95vw;transform:scale(.9) translateY(20px);transition:.3s;max-height:90vh;overflow-y:auto;}
.modal-wrap.on .modal-box{transform:scale(1) translateY(0);}
.modal-title{font-family:'Nunito',sans-serif;font-size:22px;font-weight:900;margin-bottom:6px;}
.modal-sub{font-size:13.5px;color:var(--faint);margin-bottom:24px;}
.modal-close-btn{float:right;background:none;border:none;font-size:20px;cursor:pointer;color:var(--faint);margin-top:-4px;}

/* PAYMENT & CHECKOUT MODAL STYLES */
.pay-order-summary {
  background: var(--bg-soft);
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  padding: 16px 20px;
  margin: 18px 0;
}
.pay-order-summary h4 {
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  font-weight: 800;
  margin-bottom: 12px;
  color: var(--dark);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.pos-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13.5px;
  color: var(--muted);
  padding: 6px 0;
}
.pos-row.total {
  border-top: 1px dashed var(--border);
  margin-top: 8px;
  padding-top: 10px;
  font-size: 15.5px;
  font-weight: 900;
  color: var(--dark);
}
.pay-methods {
  display: flex;
  gap: 12px;
  margin-top: 10px;
  margin-bottom: 20px;
}
.pay-method {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 12px 8px;
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  cursor: pointer;
  background: #fff;
  transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 13px;
  font-weight: 700;
  color: var(--muted);
}
.pay-method i {
  font-size: 18px;
  transition: transform 0.2s;
}
.pay-method:hover {
  border-color: var(--red);
  background: var(--red-light);
  color: var(--red);
}
.pay-method:hover i {
  transform: scale(1.1);
}
.pay-method.on {
  border-color: var(--red);
  background: var(--red-light);
  color: var(--red);
  box-shadow: 0 0 0 2px rgba(226,55,68,0.1);
}

/* RESPONSIVE DESIGN (MOBILE FIRST) */
@media (max-width:480px){
  .navbar{padding:0 16px;}
  .nav-loc, .nav-search, .nav-actions .nav-btn-ghost{display:none;}
  .hamburger{display:flex;}
  .hero{padding:20px 10px;}
  .hero-loc-btn{display:none;}
  .cart-drawer{width:100% !important;right:-100%;}
  .cart-drawer.on{right:0;}
  .modal-box{width:100% !important;height:100% !important;max-height:100vh !important;border-radius:0 !important;}
  .login-left{display:none !important;}
  .login-page{grid-template-columns:1fr !important;}
}

@media (min-width:481px) and (max-width:768px) {
  .hero-right{display:none !important;}
  .hero-content{max-width:100%;}
  .rest-grid{grid-template-columns:1fr 1fr;}
}

@media (min-width:769px) {
  .rest-grid{grid-template-columns:repeat(3, 1fr);}
}

@media (min-width:1024px) {
  .rest-grid{grid-template-columns:repeat(4, 1fr);}
}

/* FLUID TYPOGRAPHY & CLAMP SPACING */
.hero-h1 {font-size:clamp(32px, 5.5vw, 56px);}
.section {padding:52px clamp(16px, 3.5vw, 40px);}
.rest-grid {gap:clamp(16px, 2.5vw, 24px);}

/* ── MENU MODAL SPECIFIC ── */
.menu-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;opacity:0;pointer-events:none;transition:opacity .3s;display:flex;align-items:flex-end;}
.menu-overlay.on{opacity:1;pointer-events:all;}
.menu-modal{background:#fff;width:100%;max-width:760px;margin:0 auto;border-radius:22px 22px 0 0;max-height:92vh;display:flex;flex-direction:column;transform:translateY(100%);transition:transform .35s cubic-bezier(.4,0,.2,1);}
.menu-overlay.on .menu-modal{transform:translateY(0);}
.menu-handle{width:38px;height:4px;background:var(--border);border-radius:2px;margin:11px auto 0;}
.menu-hd{padding:18px 22px 15px;border-bottom:1px solid var(--border);position:sticky;top:0;background:#fff;z-index:2;}
.menu-rest-row{display:flex;gap:14px;}
.menu-rest-img{width:66px;height:66px;border-radius:12px;object-fit:cover;}
.menu-rest-name{font-family:'Nunito',sans-serif;font-size:19px;font-weight:900;}
.menu-rest-cuis{font-size:12.5px;color:var(--faint);margin:3px 0;}
.menu-rest-meta{display:flex;gap:10px;align-items:center;margin-top:6px;}
.menu-close{position:absolute;top:14px;right:18px;background:none;border:none;font-size:20px;cursor:pointer;color:var(--faint);}
.menu-body{flex:1;overflow-y:auto;padding:0;}
.menu-sec{padding:18px 22px;border-bottom:1px solid var(--border);}
.menu-sec-title{font-size:15px;font-weight:800;margin-bottom:14px;display:flex;align-items:center;gap:6px;color:var(--mid);}
.menu-item{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #f3f4f6;}
.mi-info{flex:1;}
.mi-name{font-size:14px;font-weight:700;margin-bottom:3px;}
.mi-desc{font-size:12px;color:var(--faint);line-height:1.45;}
.mi-price{font-size:14.5px;font-weight:800;margin-right:12px;white-space:nowrap;}
.mi-img{width:85px;height:75px;border-radius:10px;object-fit:cover;flex-shrink:0;}
.add-btn{padding:7px 18px;border-radius:8px;border:1.5px solid var(--red);color:var(--red);background:#fff;font-size:13px;font-weight:800;cursor:pointer;transition:.2s;font-family:'DM Sans',sans-serif;}
.add-btn:hover{background:var(--red);color:#fff;}

/* ── TRACKING LAYOUT ── */
.track-page{padding:40px;}
.track-header{display:flex;align-items:center;gap:16px;margin-bottom:32px;}
.track-back{background:none;border:none;font-size:20px;cursor:pointer;color:var(--mid);}
.track-order-id{font-family:'Nunito',sans-serif;font-size:22px;font-weight:900;}
.track-sub{font-size:13.5px;color:var(--faint);}
.track-layout{display:grid;grid-template-columns:1fr 360px;gap:28px;}
.track-card{background:#fff;border-radius:var(--r-xl);border:1px solid var(--border);padding:28px;box-shadow:var(--shadow-sm);}
.track-eta{text-align:center;padding:24px 0 20px;border-bottom:1px solid var(--border);margin-bottom:28px;}
.track-eta-time{font-family:'Nunito',sans-serif;font-size:52px;font-weight:900;color:var(--red);line-height:1;}
.track-eta-label{font-size:14px;color:var(--faint);margin-top:6px;}
.track-steps{display:flex;flex-direction:column;gap:0;}
.track-step{display:flex;gap:16px;position:relative;}
.track-step:not(:last-child) .ts-line{position:absolute;left:16px;top:34px;bottom:0;width:2px;background:var(--border);z-index:0;}
.track-step.done:not(:last-child) .ts-line{background:var(--green);}
.ts-icon{width:34px;height:34px;border-radius:50%;border:2.5px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;background:#fff;position:relative;z-index:1;transition:.4s;}
.track-step.done .ts-icon{border-color:var(--green);background:var(--green);color:#fff;}
.track-step.active .ts-icon{border-color:var(--red);background:var(--red);color:#fff;}
.ts-body{padding-bottom:24px;}
.ts-title{font-size:14px;font-weight:800;margin-bottom:3px;}
.ts-desc{font-size:12.5px;color:var(--faint);}
.ts-time{font-size:11.5px;color:var(--faint);margin-top:3px;}
.track-rider{background:var(--bg-soft);border-radius:12px;padding:14px 16px;}
.track-rider h4{font-size:13px;font-weight:800;color:var(--faint);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;}
.rider-row{display:flex;align-items:center;gap:14px;}
.rider-avatar{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--orange));display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.rider-name{font-size:15px;font-weight:800;}
.rider-plate{font-size:12px;color:var(--faint);margin-top:2px;}
.rider-actions{display:flex;gap:8px;margin-left:auto;}
.rider-btn{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;transition:.2s;color:var(--mid);}
.rider-btn:hover{border-color:var(--red);color:var(--red);}
.track-order-card{background:#fff;border-radius:var(--r-xl);border:1px solid var(--border);padding:24px;box-shadow:var(--shadow-sm);}
.track-order-card h3{font-family:'Nunito',sans-serif;font-size:17px;font-weight:900;margin-bottom:14px;}
.to-item{display:flex;justify-content:space-between;font-size:13.5px;padding:8px 0;border-bottom:1px solid #f3f4f6;}
.to-bill{margin-top:14px;border-top:1px solid var(--border);padding-top:14px;}
.to-bill-row{display:flex;justify-content:space-between;font-size:13px;color:var(--faint);margin-bottom:6px;}
.to-bill-row.tot{font-size:14px;font-weight:800;color:var(--dark);border-top:1px solid var(--border);padding-top:10px;margin-top:6px;}

/* ── LOGIN/REGISTER FULL PAGE ── */
.login-page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr;padding-top:62px;}
.login-left{background:linear-gradient(145deg,#1a0505 0%,#3d0f0f 50%,#1a0505 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 40px;position:relative;overflow:hidden;}
.login-food-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:36px;}
.login-food-img{border-radius:16px;overflow:hidden;height:160px;box-shadow:0 12px 40px rgba(0,0,0,.5);}
.login-food-img img{width:100%;height:100%;object-fit:cover;}
.login-tagline{color:#fff;text-align:center;}
.login-right{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 40px;background:#fff;}
.login-form-wrap{width:100%;max-width:400px;}
.login-logo{display:flex;align-items:center;gap:10px;margin-bottom:32px;justify-content:center;}
.login-h2{font-family:'Nunito',sans-serif;font-size:24px;font-weight:900;margin-bottom:6px;text-align:center;}
.login-sub{font-size:13.5px;color:var(--faint);margin-bottom:28px;text-align:center;}
.login-tabs{display:flex;gap:0;margin-bottom:24px;background:var(--bg-soft);border-radius:10px;padding:4px;}
.ltab{flex:1;padding:9px;border:none;background:transparent;font-size:13.5px;font-weight:700;cursor:pointer;border-radius:8px;transition:.2s;color:var(--faint);}
.ltab.on{background:#fff;color:var(--red);box-shadow:var(--shadow-xs);}
.otp-sent-info{background:linear-gradient(135deg,#fff8f8,#fff0f1);border:1px solid rgba(226,55,68,.2);border-radius:12px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:var(--mid);display:flex;align-items:center;gap:10px;}
.otp-sent-info i{color:var(--red);}
.otp-channel-row{display:flex;gap:8px;margin-bottom:16px;}
.otp-channel-btn{flex:1;padding:10px;border:1.5px solid var(--border);border-radius:9px;background:#fff;font-size:12.5px;font-weight:700;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:6px;color:var(--mid);}
.otp-channel-btn.on{border-color:var(--red);color:var(--red);background:var(--red-light);}
.login-divider{display:flex;align-items:center;gap:10px;margin:20px 0;}
.login-divider::before,.login-divider::after{content:'';flex:1;height:1px;background:var(--border);}
.login-divider span{font-size:12px;color:var(--faint);font-weight:600;}
.social-login-row{display:flex;gap:10px;}
.social-btn{flex:1;padding:11px;border:1.5px solid var(--border);border-radius:10px;background:#fff;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:.2s;}
.social-btn:hover{border-color:var(--red);color:var(--red);}
.resend-row{display:flex;align-items:center;justify-content:space-between;margin-top:10px;margin-bottom:16px;}
.resend-timer{font-size:12.5px;color:var(--faint);}
.resend-btn{font-size:12.5px;color:var(--red);cursor:pointer;font-weight:700;border:none;background:none;}
.resend-btn:disabled{color:var(--faint);cursor:default;}
.form-label{font-size:12.5px;font-weight:700;color:var(--mid);margin-bottom:6px;display:block;}
.form-input{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;outline:none;transition:.2s;}
.form-input:focus{border-color:var(--red);}
.phone-input-row{display:flex;gap:8px;margin-bottom:14px;}
.country-code{padding:11px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-weight:600;min-width:70px;text-align:center;}
.otp-row{display:flex;gap:8px;margin-bottom:16px;justify-content:center;}
.otp-input{width:48px;height:52px;border:2px solid var(--border);border-radius:10px;text-align:center;font-size:20px;font-weight:800;outline:none;}
.pay-now-btn{width:100%;padding:15px;background:var(--red);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:800;cursor:pointer;transition:.2s;}
.pay-now-btn:hover{background:var(--red-dark);}

/* ── PRO BANNER ── */
.pro-banner{background:linear-gradient(135deg,#1a0a0a 0%,#3d1515 100%);border-radius:var(--r-xl);padding:32px 36px;display:flex;align-items:center;gap:28px;margin:0 40px 40px;position:relative;overflow:hidden;}
.pro-banner::before{content:'🌟';position:absolute;right:36px;top:50%;transform:translateY(-50%);font-size:80px;opacity:.1;}
.pro-tag{display:inline-flex;align-items:center;gap:6px;background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.4);border-radius:50px;padding:4px 12px;color:var(--gold);font-size:12px;font-weight:800;margin-bottom:10px;}
.pro-title{font-family:'Nunito',sans-serif;font-size:22px;font-weight:900;color:#fff;margin-bottom:6px;}
.pro-desc{font-size:13.5px;color:rgba(255,255,255,.65);}
.pro-cta{margin-left:auto;flex-shrink:0;padding:13px 24px;background:var(--gold);color:var(--dark);border:none;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;}
.pro-cta:hover{background:#d97706;color:#fff;}

/* ── FOOTER ── */
footer{background:#111;color:rgba(255,255,255,.65);padding:52px 40px 32px;}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px;}
.footer-logo{display:flex;align-items:center;gap:8px;margin-bottom:14px;}
.footer-logo-text{font-family:'Nunito',sans-serif;font-size:20px;font-weight:900;color:#fff;}
.footer-tagline{font-size:13.5px;line-height:1.65;margin-bottom:18px;}
.footer-social{display:flex;gap:10px;}
.fsb{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.08);border:none;color:#fff;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s;}
.fsb:hover{background:var(--red);}
.footer-col-title{font-size:12px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:1px;margin-bottom:18px;}
.footer-links{list-style:none;display:flex;flex-direction:column;gap:11px;}
.footer-links a{color:rgba(255,255,255,.55);font-size:13.5px;text-decoration:none;}
.footer-links a:hover{color:#fff;}
.footer-bottom{border-top:1px solid rgba(255,255,255,.08);padding-top:24px;display:flex;align-items:center;justify-content:space-between;}
.app-btns{display:flex;gap:10px;}
.app-btn{display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:9px;border:1px solid rgba(255,255,255,.15);cursor:pointer;transition:.2s;text-decoration:none;color:#fff;background:rgba(255,255,255,.04);}
.app-btn-text span{font-size:10px;color:rgba(255,255,255,.55);display:block;}
.app-btn-text strong{font-size:13px;display:block;}

/* ── SEARCH PAGE ── */
.search-page{padding:80px 40px 40px;}
.search-header{display:flex;align-items:center;gap:16px;margin-bottom:28px;}
.search-back-btn{background:none;border:1.5px solid var(--border);border-radius:9px;padding:9px 14px;cursor:pointer;font-size:14px;color:var(--mid);transition:.2s;}
.search-back-btn:hover{border-color:var(--red);color:var(--red);}
.search-query-label{font-family:'Nunito',sans-serif;font-size:22px;font-weight:900;}
.search-count{font-size:13px;color:var(--faint);margin-top:3px;}
.search-filters{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border);}
.search-food-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;}
.food-result-card{border-radius:var(--r-md);border:1px solid var(--border);background:#fff;overflow:hidden;cursor:pointer;transition:.3s;}
.food-result-card:hover{box-shadow:var(--shadow-md);transform:translateY(-4px);}
.frc-img{width:100%;height:160px;object-fit:cover;}
.frc-body{padding:14px;}
.frc-name{font-size:15px;font-weight:800;margin-bottom:3px;}
.frc-rest{font-size:12.5px;color:var(--faint);margin-bottom:10px;display:flex;align-items:center;gap:4px;}
.frc-meta{display:flex;align-items:center;justify-content:space-between;}
.frc-price{font-size:14px;font-weight:800;color:var(--dark);}
.frc-add{padding:6px 16px;border-radius:7px;border:1.5px solid var(--red);color:var(--red);background:#fff;font-size:13px;font-weight:800;cursor:pointer;transition:.2s;}
.frc-add:hover{background:var(--red);color:#fff;}
.search-tabs{display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--border);}
.stab{flex:1;padding:11px;font-size:14px;font-weight:700;cursor:pointer;border:none;background:none;color:var(--faint);border-bottom:2.5px solid transparent;margin-bottom:-2px;transition:.2s;}
.stab.on{color:var(--red);border-bottom-color:var(--red);}

/* ── ORDERS PAGE ── */
.orders-page{padding:40px;max-width:800px;margin:0 auto;}
.orders-page h2{font-family:'Nunito',sans-serif;font-size:26px;font-weight:900;margin-bottom:24px;}
.order-card{border:1px solid var(--border);border-radius:var(--r-lg);padding:20px;margin-bottom:16px;background:#fff;box-shadow:var(--shadow-xs);transition:.2s;}
.order-card:hover{box-shadow:var(--shadow-md);}
.order-card-hd{display:flex;align-items:center;gap:14px;margin-bottom:14px;}
.order-rest-img{width:52px;height:52px;border-radius:10px;object-fit:cover;}
.order-rest-name{font-size:15px;font-weight:800;}
.order-date{font-size:12px;color:var(--faint);margin-top:2px;}
.order-status{margin-left:auto;padding:5px 12px;border-radius:50px;font-size:12px;font-weight:700;}
.status-delivered{background:#dcfce7;color:var(--green);}
.status-active{background:#fee2e2;color:var(--red);}
.order-items-row{font-size:13px;color:var(--faint);margin-bottom:14px;}
.order-actions{display:flex;gap:10px;}
.order-action-btn{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;}
.oab-outline{background:#fff;border:1.5px solid var(--border);color:var(--mid);}
.oab-outline:hover{border-color:var(--red);color:var(--red);}
.oab-red{background:var(--red);color:#fff;border:none;}
.oab-red:hover{background:var(--red-dark);}

/* ── ORDER SUCCESS ── */
.order-success{position:fixed;inset:0;background:#fff;z-index:2000;display:none;align-items:center;justify-content:center;flex-direction:column;gap:16px;}
.order-success.on{display:flex;animation:fadeIn .4s ease;}
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
.success-circle{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#3d9b6e,#2ecc71);display:flex;align-items:center;justify-content:center;font-size:46px;animation:pop .5s cubic-bezier(.34,1.56,.64,1) .2s both;}
@keyframes pop{from{transform:scale(0);}to{transform:scale(1);}}
.success-title{font-family:'Nunito',sans-serif;font-size:28px;font-weight:900;color:var(--dark);}
.success-msg{font-size:15px;color:var(--faint);text-align:center;max-width:320px;line-height:1.6;}
.success-id{font-size:13px;background:var(--bg-soft);padding:8px 20px;border-radius:8px;font-weight:700;color:var(--mid);}
.success-btns{display:flex;gap:12px;margin-top:8px;}

/* ── TOAST ── */
.toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);background:var(--dark);color:#fff;padding:12px 22px;border-radius:50px;font-size:13.5px;font-weight:600;z-index:9999;box-shadow:0 8px 30px rgba(0,0,0,.3);transition:transform .35s cubic-bezier(.4,0,.2,1);display:flex;align-items:center;gap:8px;white-space:nowrap;}
.toast.on{transform:translateX(-50%) translateY(0);}
.toast i{color:#3d9b6e;}
.toast.warn i{color:var(--gold);}

/* ── FEEDBACK / REVIEW ── */
.feedback-page{padding:40px;max-width:640px;margin:0 auto;}
.fb-back{background:none;border:none;font-size:15px;cursor:pointer;color:var(--mid);display:flex;align-items:center;gap:6px;margin-bottom:24px;font-family:'DM Sans',sans-serif;}
.fb-back:hover{color:var(--dark);}
.fb-card{background:#fff;border-radius:var(--r-xl);border:1px solid var(--border);padding:32px;box-shadow:var(--shadow-sm);}
.fb-rest-row{display:flex;align-items:center;gap:14px;margin-bottom:28px;padding-bottom:22px;border-bottom:1px solid var(--border);}
.fb-rest-img{width:60px;height:60px;border-radius:12px;object-fit:cover;}
.fb-rest-name{font-family:'Nunito',sans-serif;font-size:18px;font-weight:900;}
.fb-rest-order{font-size:12.5px;color:var(--faint);margin-top:3px;}
.fb-section{margin-bottom:28px;}
.fb-section-title{font-size:16px;font-weight:800;margin-bottom:14px;}
.stars-row{display:flex;gap:10px;margin-bottom:14px;}
.star-btn{font-size:32px;cursor:pointer;transition:.2s;filter:grayscale(1);opacity:.4;}
.star-btn:hover,.star-btn.on{filter:none;opacity:1;transform:scale(1.2);}
.fb-tags{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;}
.fb-tag{padding:7px 14px;border-radius:50px;border:1.5px solid var(--border);font-size:12.5px;font-weight:600;cursor:pointer;transition:.2s;}
.fb-tag:hover{border-color:var(--red);color:var(--red);}
.fb-tag.on{background:var(--red);color:#fff;border-color:var(--red);}
.fb-textarea{width:100%;padding:13px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;outline:none;resize:vertical;min-height:100px;}
.fb-textarea:focus{border-color:var(--red);}
.fb-submit{width:100%;padding:15px;background:var(--red);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:800;cursor:pointer;}
.fb-submit:hover{background:var(--red-dark);}
.fb-success{text-align:center;padding:48px 20px;}
.fb-success-icon{font-size:64px;margin-bottom:16px;}

/* ── OTP POPUP ── */
.otp-sim-popup{position:fixed;bottom:32px;right:32px;background:#1c1c1c;color:#fff;border-radius:16px;padding:18px 22px;box-shadow:0 20px 60px rgba(0,0,0,.45);z-index:99999;transform:translateY(120px);opacity:0;transition:.4s cubic-bezier(.4,0,.2,1);max-width:320px;}
.otp-sim-popup.on{transform:translateY(0);opacity:1;}
.otp-sim-hd{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.otp-sim-icon{width:36px;height:36px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.otp-sim-title{font-size:14px;font-weight:800;}
.otp-sim-sub{font-size:11.5px;color:rgba(255,255,255,.5);}
.otp-sim-code{font-family:'Nunito',sans-serif;font-size:36px;font-weight:900;letter-spacing:6px;color:var(--gold);text-align:center;padding:10px 0;}
.otp-sim-msg{font-size:11.5px;color:rgba(255,255,255,.5);text-align:center;}

.hidden{display:none!important;}
.spin{animation:spin 1s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}
</style>
</head>
<body>

<!-- ── HTML ── -->

<!-- WhatsApp Support Float Button -->
<button class="whatsapp-btn" onclick="openWhatsAppChat()"><i class="fab fa-whatsapp"></i></button>

<!-- Sticky Back to Top Button -->
<button class="back-to-top" id="backToTop" onclick="scrollToTop()"><i class="fas fa-chevron-up"></i></button>

<!-- ═══ NAVBAR ═══ -->
<nav class="navbar">
  <a class="nav-logo" onclick="showPage('home')">
    <svg width="110" height="36" viewBox="0 0 110 36" xmlns="http://www.w3.org/2000/svg" style="display:block">
      <defs>
        <linearGradient id="wiGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:#e23744"/>
          <stop offset="100%" style="stop-color:#ff6b6b"/>
        </linearGradient>
      </defs>
      <circle cx="18" cy="18" r="16" fill="url(#wiGrad)"/>
      <text x="18" y="24" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="20" fill="#fff">?</text>
      <text x="40" y="13" font-family="Nunito,sans-serif" font-weight="900" font-size="11" fill="#e23744" letter-spacing="1">WHAT</text>
      <text x="40" y="27" font-family="Nunito,sans-serif" font-weight="900" font-size="11" fill="#1c1c1c" letter-spacing="1">IF</text>
      <rect x="40" y="29" width="32" height="2.5" rx="1.25" fill="url(#wiGrad)"/>
    </svg>
  </a>

  <div class="nav-loc" onclick="openLocModal()">
    <i class="fas fa-map-marker-alt"></i>
    <span class="nav-loc-text" id="nav-loc-text">Koramangala, Bangalore</span>
    <i class="fas fa-chevron-down chevron"></i>
  </div>

  <div class="nav-search">
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search for restaurant or dish…" id="nav-search" onkeydown="if(event.key==='Enter')doSearch()">
  </div>

  <button class="hamburger" onclick="toggleMobileMenu()">
    <span></span>
    <span></span>
    <span></span>
  </button>

  <div class="nav-actions" id="navActions">
    <button class="nav-btn nav-btn-ghost" onclick="showPage('orders')"><i class="fas fa-receipt"></i> Orders</button>
    <button class="nav-btn nav-btn-ghost" id="nav-signin-btn" onclick="showLoginPage()">Sign In</button>
    
    <div id="nav-user-pill" style="display:none;align-items:center;gap:8px;padding:6px 14px;border-radius:9px;background:var(--red-light);border:1.5px solid rgba(226,55,68,.3);cursor:pointer;" onclick="logoutUser()">
      <div style="width:28px;height:28px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:800;" id="nav-user-avatar">?</div>
      <span style="font-size:13px;font-weight:700;" id="nav-user-name">User</span>
    </div>

    <div class="nav-cart" onclick="openCart()">
      <i class="fas fa-shopping-bag"></i> Cart
      <span class="cart-badge" id="cart-badge">0</span>
    </div>
  </div>
</nav>

<!-- ═══ HOME PAGE ═══ -->
<div class="page active" id="page-home">
  <!-- HERO -->
  <section class="hero">
    <div class="hero-particles" id="hero-particles"></div>
    <div class="hero-content">
      <div class="hero-tag"><i class="fas fa-bolt"></i> India's Premium Food Platform</div>
      <h1 class="hero-h1">Hungry? We've got<br>you <em>covered.</em></h1>
      <p class="hero-p">Order from top-tier gourmet restaurants across Bangalore. Rapid live tracking & 30-min premium delivery.</p>
      <div class="hero-searchbar">
        <div class="hero-loc-btn" onclick="openLocModal()">
          <i class="fas fa-map-marker-alt"></i>
          <span id="hero-loc-text">Koramangala</span>
        </div>
        <div class="hero-search-wrap">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Search for restaurant, cuisine or dish…" id="hero-search" onkeydown="if(event.key==='Enter')doSearch(this.value)">
        </div>
        <button class="hero-go" onclick="doSearch(document.getElementById('hero-search').value)">Search</button>
      </div>
      <div class="hero-stats">
        <div><div class="hero-stat-val">500+</div><div class="hero-stat-lbl">Premium Kitchens</div></div>
        <div><div class="hero-stat-val">20 min</div><div class="hero-stat-lbl">Fastest Delivery</div></div>
        <div><div class="hero-stat-val">4.8 ★</div><div class="hero-stat-lbl">Top Rated Cuisine</div></div>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-imgs">
        <div class="hero-img-card"><img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&q=80" alt="Pizza"></div>
        <div class="hero-img-card"><img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80" alt="Burger"></div>
        <div class="hero-img-card"><img src="https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400&q=80" alt="Biryani"></div>
        <div class="hero-img-card"><img src="https://images.unsplash.com/photo-1555126634-323283e090fa?w=400&q=80" alt="Dessert"></div>
      </div>
    </div>
  </section>

  <!-- TOP PICKS -->
  <section class="section">
    <div class="sec-hd"><div><div class="sec-title">Top picks for you 🔥</div><div class="sec-sub">Based on popular choices near Bangalore</div></div></div>
    <div class="tp-scroll" id="tp-scroll"></div>
  </section>

  <!-- CUISINES -->
  <section class="section section-alt">
    <div class="sec-hd"><div><div class="sec-title">What's on your mind? 🍽️</div><div class="sec-sub">Choose from verified categories</div></div></div>
    <div class="cuisine-grid" id="cuisine-grid"></div>
  </section>

  <!-- OFFERS -->
  <section class="section">
    <div class="sec-hd"><div><div class="sec-title">Best offers for you 🏷️</div><div class="sec-sub">Exclusive promo codes & banking offers</div></div></div>
    <div class="offers-grid" id="offers-grid"></div>
  </section>

  <!-- PRO BANNER -->
  <div class="pro-banner">
    <div class="pro-info">
      <div class="pro-tag"><i class="fas fa-crown"></i> WHAT IF PRO</div>
      <div class="pro-title">Unlimited free deliveries + exclusive dining perks</div>
      <div class="pro-desc">Save up to ₹3000 every month on all orders above ₹199</div>
    </div>
    <button class="pro-cta" onclick="toast('What If Pro activated successfully!')"><i class="fas fa-bolt"></i> Get Pro for ₹149</button>
  </div>

  <!-- RESTAURANTS -->
  <section class="section section-alt">
    <div class="sec-hd">
      <div>
        <div class="sec-title">Restaurants near you 📍</div>
        <div class="sec-sub" id="rest-count-sub">Gourmet kitchens delivering to Koramangala</div>
      </div>
    </div>
    
    <div class="filter-row" id="filter-row"></div>
    
    <!-- Dynamic Restaurant Cards Container -->
    <div class="rest-grid" id="rest-grid">
      <!-- Skeleton cards loading automatically will be replaced by JS -->
    </div>
  </section>

  <!-- COLLECTIONS -->
  <section class="section">
    <div class="sec-hd"><div><div class="sec-title">Collections 📚</div><div class="sec-sub">Curated guides to top food spaces</div></div></div>
    <div class="coll-scroll" id="coll-scroll"></div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-grid">
      <div>
        <div class="footer-logo">
          <svg width="100" height="32" viewBox="0 0 110 36" xmlns="http://www.w3.org/2000/svg">
            <defs><linearGradient id="wiGrad2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#e23744"/><stop offset="100%" style="stop-color:#ff6b6b"/></linearGradient></defs>
            <circle cx="18" cy="18" r="16" fill="url(#wiGrad2)"/>
            <text x="18" y="24" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="20" fill="#fff">?</text>
            <text x="40" y="13" font-family="Nunito,sans-serif" font-weight="900" font-size="11" fill="#e23744" letter-spacing="1">WHAT</text>
            <text x="40" y="27" font-family="Nunito,sans-serif" font-weight="900" font-size="11" fill="#1c1c1c" letter-spacing="1">IF</text>
            <rect x="40" y="29" width="32" height="2.5" rx="1.25" fill="url(#wiGrad2)"/>
          </svg>
        </div>
        <p class="footer-tagline">Discover the finest culinary structures across India. Delivering smiles with absolute premium technology.</p>
        <div class="footer-social">
          <button class="fsb"><i class="fab fa-facebook-f"></i></button>
          <button class="fsb"><i class="fab fa-twitter"></i></button>
          <button class="fsb"><i class="fab fa-instagram"></i></button>
          <button class="fsb"><i class="fab fa-youtube"></i></button>
        </div>
      </div>
      <div>
        <div class="footer-col-title">About What If</div>
        <ul class="footer-links"><li><a href="#">Who We Are</a></li><li><a href="#">Blog</a></li><li><a href="#">Careers</a></li><li><a href="#">Report Fraud</a></li></ul>
      </div>
      <div>
        <div class="footer-col-title">Services</div>
        <ul class="footer-links"><li><a href="#">What If Tech</a></li><li><a href="#">Feeding India</a></li><li><a href="#">Hyperpure Supply</a></li></ul>
      </div>
      <div>
        <div class="footer-col-title">Help & Support</div>
        <ul class="footer-links"><li><a href="#">Partner Kitchens</a></li><li><a href="#">Terms of Use</a></li><li><a href="#">Privacy Protocol</a></li></ul>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-copy">© 2026 What If Tech Foods Limited. All rights reserved.</div>
      <div class="app-btns">
        <a class="app-btn" href="#"><i class="fab fa-apple"></i><div class="app-btn-text"><span>Download on</span><strong>App Store</strong></div></a>
        <a class="app-btn" href="#"><i class="fab fa-google-play"></i><div class="app-btn-text"><span>Get it on</span><strong>Play Store</strong></div></a>
      </div>
    </div>
  </footer>
</div>

<!-- ═══ LOGIN PAGE ═══ -->
<div class="page" id="page-login">
  <div class="login-page">
    <div class="login-left">
      <div class="login-food-grid">
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80" alt="Burger"></div>
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&q=80" alt="Pizza"></div>
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400&q=80" alt="Biryani"></div>
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1555126634-323283e090fa?w=400&q=80" alt="Dessert"></div>
      </div>
      <div class="login-tagline">
        <h2>Premium meals delivered to your doorstep 🚀</h2>
        <p>Join millions of foodies ordering from the finest Bangalore restaurants.</p>
      </div>
    </div>
    <div class="login-right">
      <div class="login-form-wrap">
        <div class="login-logo">
          <svg width="130" height="42" viewBox="0 0 130 42" xmlns="http://www.w3.org/2000/svg">
            <defs><linearGradient id="wiGrad3" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#e23744"/><stop offset="100%" style="stop-color:#ff6b6b"/></linearGradient></defs>
            <circle cx="21" cy="21" r="19" fill="url(#wiGrad3)"/>
            <text x="21" y="29" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="24" fill="#fff">?</text>
            <text x="47" y="17" font-family="Nunito,sans-serif" font-weight="900" font-size="14" fill="#e23744" letter-spacing="1.5">WHAT</text>
            <text x="47" y="33" font-family="Nunito,sans-serif" font-weight="900" font-size="14" fill="#1c1c1c" letter-spacing="1.5">IF</text>
          </svg>
        </div>
        
        <div class="login-h2">Welcome back!</div>
        <div class="login-sub">Sign in to continue ordering</div>

        <div class="login-tabs">
          <button class="ltab on" id="ltab-phone" onclick="switchLoginTab('phone')"><i class="fas fa-phone"></i> Phone</button>
          <button class="ltab" id="ltab-email" onclick="switchLoginTab('email')"><i class="fas fa-envelope"></i> Email</button>
        </div>

        <!-- Phone Login Panel -->
        <div id="login-phone-wrap">
          <div id="login-step1">
            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label">Mobile Number</label>
              <div class="phone-input-row">
                <div class="country-code">🇮🇳 +91</div>
                <input class="form-input" id="login-phone-num" placeholder="Enter 10-digit number" maxlength="10" type="tel" style="flex:1">
              </div>
              <span class="error-msg" id="phone-err-msg">Please enter a valid 10-digit number.</span>
            </div>
            <button class="pay-now-btn" onclick="loginSendOTP()"><i class="fas fa-paper-plane"></i> Send OTP</button>
          </div>
          
          <div id="login-step2" style="display:none">
            <div class="otp-sent-info">
              <i class="fas fa-check-circle"></i>
              <div>
                <div style="font-weight:700">OTP Sent Successfully!</div>
                <div style="font-size:11.5px;color:var(--faint);margin-top:2px">Sent to +91 <span id="login-otp-phone">XXXXXXXXXX</span></div>
              </div>
            </div>
            <div class="form-group" style="margin-bottom:14px">
              <label class="form-label">Enter 6-digit OTP</label>
              <div class="otp-row" id="login-otp-boxes"></div>
              <span class="error-msg" id="otp-err-msg">Please enter the correct 6-digit OTP.</span>
            </div>
            <div class="resend-row">
              <span class="resend-timer" id="resend-timer">Resend in <span id="timer-count">30</span>s</span>
              <button class="resend-btn" id="resend-btn" disabled onclick="resendOTP()">Resend OTP</button>
            </div>
            <button class="pay-now-btn" onclick="loginVerifyOTP()"><i class="fas fa-shield-alt"></i> Verify & Sign In</button>
          </div>
        </div>

        <!-- Email Login Panel -->
        <div id="login-email-wrap" style="display:none">
          <div id="login-email-step1">
            <div class="form-group" style="margin-bottom:14px">
              <label class="form-label">Email Address</label>
              <input class="form-input" id="login-email" placeholder="Enter your email" type="email">
              <span class="error-msg" id="email-err-msg">Please enter a valid email address.</span>
            </div>
            <button class="pay-now-btn" onclick="loginSendEmailOTP()"><i class="fas fa-paper-plane"></i> Get OTP on Email</button>
          </div>
          
          <div id="login-email-step2" style="display:none">
            <div class="otp-sent-info">
              <i class="fas fa-envelope"></i>
              <div>
                <div style="font-weight:700">OTP Sent to Email!</div>
                <div style="font-size:11.5px;color:var(--faint);margin-top:2px">Sent to <span id="login-otp-email-addr"></span></div>
              </div>
            </div>
            <div class="form-group" style="margin-bottom:14px">
              <label class="form-label">Enter 6-digit OTP</label>
              <div class="otp-row" id="login-email-otp-boxes"></div>
              <span class="error-msg" id="email-otp-err-msg">Please enter the 6-digit OTP.</span>
            </div>
            <button class="pay-now-btn" onclick="loginVerifyEmailOTP()"><i class="fas fa-shield-alt"></i> Verify & Sign In</button>
          </div>
        </div>

        <div class="login-divider"><span>OR CONTINUE WITH</span></div>
        <div class="social-login-row">
          <button class="social-btn" onclick="socialLogin('Google')"><img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" height="18"> Google</button>
          <button class="social-btn" onclick="socialLogin('Facebook')"><i class="fab fa-facebook" style="color:#1877f2;font-size:18px"></i> Facebook</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ REGISTER PAGE ═══ -->
<div class="page" id="page-register">
  <div class="login-page">
    <div class="login-left">
      <div class="login-food-grid">
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&q=80" alt="Burger"></div>
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&q=80" alt="Pizza"></div>
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400&q=80" alt="Biryani"></div>
        <div class="login-food-img"><img src="https://images.unsplash.com/photo-1555126634-323283e090fa?w=400&q=80" alt="Dessert"></div>
      </div>
      <div class="login-tagline">
        <h2>What If your food was always pristine? 🍽️</h2>
        <p>Create a verified premium profile to unlock custom discounts and priority customer care.</p>
      </div>
    </div>
    <div class="login-right">
      <div class="login-form-wrap">
        <div class="login-logo" style="margin-bottom:20px">
          <svg width="130" height="42" viewBox="0 0 130 42" xmlns="http://www.w3.org/2000/svg">
            <circle cx="21" cy="21" r="19" fill="url(#wiGradR)"/>
            <text x="21" y="29" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="24" fill="#fff">?</text>
            <text x="47" y="17" font-family="Nunito,sans-serif" font-weight="900" font-size="14" fill="#e23744" letter-spacing="1.5">WHAT</text>
            <text x="47" y="33" font-family="Nunito,sans-serif" font-weight="900" font-size="14" fill="#1c1c1c" letter-spacing="1.5">IF</text>
          </svg>
        </div>

        <div class="login-tabs" style="margin-bottom:20px">
          <button class="ltab on" id="rtab-register" onclick="switchAuthTab('register')"><i class="fas fa-user-plus"></i> Register</button>
          <button class="ltab" id="rtab-signin" onclick="switchAuthTab('signin')"><i class="fas fa-sign-in-alt"></i> Sign In</button>
        </div>

        <!-- Register Panel -->
        <div id="register-form-wrap">
          <div id="register-step1">
            <div class="form-group" style="margin-bottom:12px">
              <label class="form-label">Full Name</label>
              <input class="form-input" id="reg-name" placeholder="Enter your full name">
              <span class="error-msg" id="reg-name-err">Name cannot be empty.</span>
            </div>
            <div class="form-group" style="margin-bottom:12px">
              <label class="form-label">Email Address</label>
              <input class="form-input" id="reg-email" placeholder="Enter your email" type="email">
              <span class="error-msg" id="reg-email-err">Please enter a valid email.</span>
            </div>
            <div class="form-group" style="margin-bottom:12px">
              <label class="form-label">Mobile Number</label>
              <div class="phone-input-row">
                <div class="country-code">🇮🇳 +91</div>
                <input class="form-input" id="reg-phone" placeholder="10-digit number" maxlength="10" type="tel" style="flex:1">
              </div>
              <span class="error-msg" id="reg-phone-err">Please enter a valid 10-digit mobile number.</span>
            </div>
            <button class="pay-now-btn" onclick="registerSendOTP()"><i class="fas fa-paper-plane"></i> Send Verification OTP</button>
          </div>

          <div id="register-step2" style="display:none">
            <div class="otp-sent-info">
              <i class="fas fa-check-circle"></i>
              <div>
                <div style="font-weight:700">Verification OTP Sent!</div>
                <div style="font-size:11.5px;color:var(--faint);margin-top:2px">Sent to +91 <span id="reg-otp-phone-lbl"></span></div>
              </div>
            </div>
            <div class="form-group" style="margin-bottom:14px">
              <label class="form-label">Enter 6-digit OTP</label>
              <div class="otp-row" id="register-otp-boxes"></div>
              <span class="error-msg" id="reg-otp-err">Incorrect verification OTP.</span>
            </div>
            <button class="pay-now-btn" onclick="registerVerifyOTP()"><i class="fas fa-user-check"></i> Verify & Create Account</button>
          </div>
        </div>

        <!-- Inline Sign In Panel -->
        <div id="register-signin-wrap" style="display:none">
          <div class="form-group" style="margin-bottom:12px">
            <label class="form-label">Email or Phone</label>
            <input class="form-input" id="rsi-email" placeholder="Enter registered email or phone">
            <span class="error-msg" id="rsi-email-err">Please fill this field.</span>
          </div>
          <button class="pay-now-btn" onclick="registerPageSignIn()"><i class="fas fa-sign-in-alt"></i> Verify via OTP</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ SEARCH RESULTS PAGE ═══ -->
<div class="page" id="page-search">
  <div class="search-page">
    <div class="search-header">
      <button class="search-back-btn" onclick="showPage('home')"><i class="fas fa-arrow-left"></i> Back</button>
      <div>
        <div class="search-query-label">Results for "<span id="search-query-text"></span>"</div>
        <div class="search-count" id="search-result-count">0 results near you</div>
      </div>
    </div>
    
    <div class="search-tabs">
      <button class="stab on" id="stab-all" onclick="switchSearchTab('all',this)">All Results</button>
      <button class="stab" id="stab-food" onclick="switchSearchTab('food',this)">Dishes</button>
      <button class="stab" id="stab-rest" onclick="switchSearchTab('rest',this)">Restaurants</button>
    </div>
    
    <div class="filter-row" id="search-filter-row"></div>
    <div id="search-results-container"></div>
  </div>
</div>

<!-- ═══ ORDERS LIST PAGE ═══ -->
<div class="page" id="page-orders">
  <div class="orders-page">
    <h2>Your Orders 🧾</h2>
    <div id="orders-list">
      <!-- Loaded dynamically via AJAX -->
    </div>
  </div>
</div>

<!-- ═══ LIVE ORDER TRACKING PAGE ═══ -->
<div class="page" id="page-track">
  <div class="track-page">
    <div class="track-header">
      <button class="track-back" onclick="showPage('orders')"><i class="fas fa-arrow-left"></i></button>
      <div><div class="track-order-id" id="track-order-id">Order #ZMT000000</div><div class="track-sub" id="track-rest-name">Loading status…</div></div>
    </div>
    
    <div class="track-layout">
      <div>
        <div class="track-card" style="margin-bottom:20px;">
          <div class="track-eta">
            <div class="track-eta-time" id="eta-time">28</div>
            <div class="track-eta-label">minutes remaining</div>
          </div>
          
          <!-- Visual progress tracker -->
          <div class="progress-bar-container">
            <div class="progress-line"><div class="progress-line-fill" id="trackProgressLine"></div></div>
            <div class="progress-step" id="step-placed">
              <div class="ps-circle">1</div>
              <div class="ps-lbl">Placed</div>
            </div>
            <div class="progress-step" id="step-confirmed">
              <div class="ps-circle">2</div>
              <div class="ps-lbl">Confirmed</div>
            </div>
            <div class="progress-step" id="step-preparing">
              <div class="ps-circle">3</div>
              <div class="ps-lbl">Preparing</div>
            </div>
            <div class="progress-step" id="step-delivery">
              <div class="ps-circle">4</div>
              <div class="ps-lbl">Out for Delivery</div>
            </div>
            <div class="progress-step" id="step-delivered">
              <div class="ps-circle">5</div>
              <div class="ps-lbl">Delivered</div>
            </div>
          </div>
        </div>

        <div class="track-card">
          <!-- Fallback animated tracking map with road illustration -->
          <div class="track-gmap" style="background:linear-gradient(135deg,#e8f4f8,#d1e8d0);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;height:240px;border-radius:12px;">
            <div style="text-align:center;color:#666;font-size:13px;z-index:2">
              <div style="font-weight:700">Live Delivery Route Map</div>
              <div style="font-size:11px;color:var(--faint)">Real-time partner dispatch tracking</div>
              
              <div style="position:relative;width:300px;height:120px;margin:12px auto;overflow:hidden;border-radius:10px;">
                <div style="position:absolute;top:35%;left:22%;font-size:24px">🍴</div>
                <div style="position:absolute;font-size:24px;animation:rider-move 8s linear infinite" id="map-rider-animated">🛵</div>
                <div style="position:absolute;bottom:18%;right:18%;font-size:24px">🏠</div>
                <div style="position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 30px,rgba(255,255,255,.3) 30px,rgba(255,255,255,.3) 31px),repeating-linear-gradient(90deg,transparent,transparent 30px,rgba(255,255,255,.3) 30px,rgba(255,255,255,.3) 31px)"></div>
              </div>
            </div>
          </div>

          <div class="track-rider" style="margin-top:16px">
            <h4>Your Delivery Executive</h4>
            <div class="rider-row">
              <div class="rider-avatar">🧑</div>
              <div><div class="rider-name" id="rider-name">Suresh Kumar</div><div class="rider-plate" id="rider-plate">KA 03 MX 5678 · ⭐ 4.9</div></div>
              <div class="rider-actions">
                <button class="rider-btn" onclick="toast('Connecting call to Suresh…')"><i class="fas fa-phone"></i></button>
                <button class="rider-btn" onclick="toast('Chat window initiated.')"><i class="fas fa-comment"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="track-order-card">
        <h3>Order Details</h3>
        <div id="track-order-items"></div>
        <div class="to-bill" id="track-bill"></div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ FEEDBACK & RATING PAGE ═══ -->
<div class="page" id="page-feedback">
  <div class="feedback-page">
    <button class="fb-back" onclick="showPage('orders')"><i class="fas fa-arrow-left"></i> Back to Orders</button>
    <div class="fb-card" id="fb-card">
      <div class="fb-rest-row">
        <img class="fb-rest-img" id="fb-rest-img" src="" alt="">
        <div><div class="fb-rest-name" id="fb-rest-name">Restaurant</div><div class="fb-rest-order" id="fb-rest-order">Order #ZMT000</div></div>
      </div>
      <div class="fb-section">
        <div class="fb-section-title">Rate your food experience</div>
        <div class="stars-row" id="overall-stars"></div>
        <div class="fb-tags" id="overall-tags"></div>
      </div>
      <div class="fb-section">
        <div class="fb-section-title">Rate delivery service</div>
        <div class="stars-row" id="delivery-stars"></div>
        <div class="fb-tags" id="delivery-tags"></div>
      </div>
      <div class="fb-section">
        <div class="fb-section-title">Write a review (optional)</div>
        <textarea class="fb-textarea" id="fb-comment" placeholder="Tell us about the taste, packaging or portion…"></textarea>
      </div>
      <button class="fb-submit" onclick="submitFeedback()"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
    </div>
  </div>
</div>

<!-- ═══ CART OVERLAY DRAWER ═══ -->
<div class="overlay" id="cart-overlay" onclick="closeCart()"></div>
<div class="cart-drawer" id="cart-drawer">
  <div class="cart-hd">
    <div class="cart-hd-title">Your Order 🛒</div>
    <button class="cart-hd-close" onclick="closeCart()">✕</button>
  </div>
  <div class="cart-rest-strip" id="cart-rest-strip">Ordering from: <span id="cart-rest-label"></span></div>
  <div class="cart-body" id="cart-body">
    <div class="cart-empty-msg"><i class="fas fa-shopping-bag"></i><p>Your cart is empty</p></div>
  </div>
  <div class="cart-ft" id="cart-ft" style="display:none">
    <div class="coupon-row">
      <input class="coupon-input" id="coupon-input" placeholder="Enter coupon code…">
      <button class="coupon-apply" onclick="applyCoupon()">Apply</button>
    </div>
    <div class="coupon-applied" id="coupon-applied"><i class="fas fa-tag"></i> Coupon applied! You save ₹<span id="coupon-save">0</span></div>
    <div class="cart-row"><span>Item subtotal</span><span id="cart-subtotal">₹0</span></div>
    <div class="cart-row"><span>Delivery partner fee</span><span id="cart-delivery">₹29</span></div>
    <div class="cart-row"><span>GST & Restaurant Charges (5%)</span><span id="cart-gst">₹0</span></div>
    <div class="cart-row total"><span>Total</span><span id="cart-total">₹0</span></div>
    <button class="checkout-btn" onclick="openPay()"><i class="fas fa-lock"></i> Proceed to Checkout</button>
  </div>
</div>

<!-- ═══ MENU MODAL ═══ -->
<div class="menu-overlay" id="menu-overlay" onclick="closeMenuModal(event)">
  <div class="menu-modal">
    <div class="menu-handle"></div>
    <div class="menu-hd">
      <div class="menu-rest-row">
        <img class="menu-rest-img" id="menu-rest-img" src="" alt="">
        <div>
          <div class="menu-rest-name" id="menu-rest-name"></div>
          <div class="menu-rest-cuis" id="menu-rest-cuis"></div>
          <div class="menu-rest-meta" id="menu-rest-meta"></div>
        </div>
      </div>
      <button class="menu-close" onclick="closeMenu()">✕</button>
    </div>
    <div class="menu-body" id="menu-body">
      <!-- Populated via AJAX menu query -->
    </div>
  </div>
</div>

<!-- ═══ CHECKOUT & PAYMENT MODAL ═══ -->
<div class="modal-wrap" id="pay-modal" onclick="closeModalBackdrop(event, 'pay-modal')">
  <div class="modal-box">
    <button class="modal-close-btn" onclick="closePay()">✕</button>
    <div class="modal-title">Secure Checkout</div>
    <div class="modal-sub">Confirm address and complete payment</div>
    
    <div class="addr-section">
      <div class="form-group" style="margin-bottom:12px">
        <label class="form-label">Delivery Address</label>
        <input class="form-input" id="pay-addr" placeholder="Flat No., Building, Street Name" value="Flat 4B, Sunrise Apartments, 12th Main, Koramangala, Bangalore">
        <span class="error-msg" id="pay-addr-err">Please provide a valid address.</span>
      </div>
      <div class="form-group" style="margin-bottom:12px">
        <label class="form-label">Contact Person Name</label>
        <input class="form-input" id="pay-name" placeholder="Name" value="Aakash Sharma">
        <span class="error-msg" id="pay-name-err">Name field is required.</span>
      </div>
    </div>
    
    <div class="pay-order-summary">
      <h4>Order Summary</h4>
      <div id="pay-items-list"></div>
      <div class="pos-row"><span>Item subtotal</span><span id="pay-subtotal">₹0</span></div>
      <div class="pos-row"><span>Delivery fee</span><span id="pay-delivery">₹29</span></div>
      <div class="pos-row"><span>GST charges</span><span id="pay-gst">₹0</span></div>
      <div class="pos-row" id="pay-disc-row" style="display:none;color:var(--green)"><span>Discount</span><span id="pay-disc-val">-₹0</span></div>
      <div class="pos-row total"><span>Total Payable</span><span id="pay-total">₹0</span></div>
    </div>

    <!-- Coupons toggle section -->
    <div class="pay-coupons-section" style="margin-bottom: 20px;">
      <div style="background:var(--bg-soft);padding:12px;border-radius:10px;display:flex;justify-content:space-between;cursor:pointer;border:1px solid var(--border)" onclick="toggleCouponList()">
        <span><i class="fas fa-tag" style="color:var(--red);margin-right:6px"></i> Apply Promo Coupon</span>
        <i class="fas fa-chevron-down" id="couponChevron"></i>
      </div>
      <div id="couponListWrapper" style="display:none;border:1px solid var(--border);border-top:none;padding:12px;border-radius:0 0 10px 10px;max-height:180px;overflow-y:auto;background:#fff;">
        <!-- Coupons auto-rendered -->
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Select Payment Method</label>
      <div class="pay-methods">
        <div class="pay-method on" id="pm-upi" onclick="selectPayMethod('upi')"><i class="fas fa-mobile-alt" style="color:var(--green)"></i><span>UPI</span></div>
        <div class="pay-method" id="pm-card" onclick="selectPayMethod('card')"><i class="fas fa-credit-card" style="color:var(--red)"></i><span>Card</span></div>
        <div class="pay-method" id="pm-cod" onclick="selectPayMethod('cod')"><i class="fas fa-money-bill-wave" style="color:var(--gold)"></i><span>COD</span></div>
      </div>
    </div>
    
    <button class="pay-now-btn" onclick="processPayment()" id="pay-now-btn"><i class="fas fa-lock"></i> Place Order</button>
  </div>
</div>

<!-- ═══ LOCATION SELECTION MODAL ═══ -->
<div class="modal-wrap" id="loc-modal" onclick="closeModalBackdrop(event, 'loc-modal')">
  <div class="loc-modal-box modal-box">
    <button class="modal-close-btn" onclick="document.getElementById('loc-modal').classList.remove('on')">✕</button>
    <div class="modal-title">📍 Select Delivery Location</div>
    <div class="modal-sub">Choose your area to filter nearby kitchens</div>
    <button class="loc-detect-btn" style="width:100%;padding:12px;border:2px dashed var(--red);border-radius:10px;background:var(--red-light);color:var(--red);font-weight:700;cursor:pointer;margin-bottom:14px;" onclick="detectLoc()"><i class="fas fa-crosshairs"></i> Detect Current Location</button>
    <div class="loc-div" style="text-align:center;margin-bottom:14px;color:var(--faint)"><span>OR SELECT BANGLORE AREA</span></div>
    <div class="loc-sugg" id="loc-sugg"></div>
  </div>
</div>

<!-- ═══ SIGN IN SIMULATOR / REDIRECT MODAL ═══ -->
<div class="modal-wrap" id="signin-modal" onclick="closeModalBackdrop(event, 'signin-modal')">
  <div class="signin-box modal-box">
    <button class="modal-close-btn" onclick="document.getElementById('signin-modal').classList.remove('on')">✕</button>
    <div class="modal-title">Elevate Your Taste Session</div>
    <div class="modal-sub" style="margin-bottom:20px">Sign in to claim offers & secure checkout.</div>
    <button class="pay-now-btn" onclick="document.getElementById('signin-modal').classList.remove('on');showLoginPage()"><i class="fas fa-sign-in-alt"></i> Sign In / Sign Up</button>
  </div>
</div>

<!-- ═══ ORDER CONFIRMED POPUP ═══ -->
<div class="order-success" id="order-success">
  <div class="success-circle">✓</div>
  <div class="success-title">Order Placed!</div>
  <div class="success-msg">Your dinner preparation has begun in the selected kitchen.</div>
  <div class="success-id" id="success-order-id">Order #ZMT000</div>
  <div class="success-btns">
    <button class="nav-btn nav-btn-ghost" onclick="document.getElementById('order-success').classList.remove('on');showPage('orders')"><i class="fas fa-receipt"></i> View History</button>
    <button class="nav-btn nav-btn-red" onclick="document.getElementById('order-success').classList.remove('on');trackCurrentOrder()"><i class="fas fa-map-marker-alt"></i> Track Live</button>
  </div>
</div>

<!-- ═══ TOAST ═══ -->
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toast-msg">Success</span></div>

<!-- ═══ OTP POPUP SIMULATOR ═══ -->
<div class="otp-sim-popup" id="otp-sim-popup">
  <div class="otp-sim-hd">
    <div class="otp-sim-icon" id="otp-sim-icon">📱</div>
    <div>
      <div class="otp-sim-title" id="otp-sim-title">SMS Verification</div>
      <div class="otp-sim-sub" id="otp-sim-sub">+91 XXXXXXXXXX</div>
    </div>
    <button onclick="closeOTPPopup()" style="background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;margin-left:auto;font-size:16px">✕</button>
  </div>
  <div class="otp-sim-code" id="otp-sim-code">000000</div>
  <div class="otp-sim-msg">Your What If login OTP. Valid for 5 minutes.</div>
</div>

<!-- ── SCRIPTS ── -->
<script>
// PHP SEEDED DATA INJECTED
const RESTAURANTS = <?php echo json_encode($preloaded_restaurants); ?>;
const OFFERS = <?php echo json_encode($preloaded_coupons); ?>;
const SESSION_USER = <?php echo isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'null'; ?>;

const CUISINES = [
  {name:'North Indian',emoji:'🍛'},{name:'South Indian',emoji:'🥘'},{name:'Pizza',emoji:'🍕'},
  {name:'Burgers',emoji:'🍔'},{name:'Biryani',emoji:'🍚'},{name:'Chinese',emoji:'🥡'},
  {name:'Sushi',emoji:'🍣'},{name:'Desserts',emoji:'🍰'},{name:'Coffee',emoji:'☕'},
  {name:'Healthy',emoji:'🥗'},{name:'Rolls',emoji:'🌯'},{name:'Pasta',emoji:'🍝'},
  {name:'Sandwiches',emoji:'🥪'},{name:'Momos',emoji:'🥟'},{name:'Shawarma',emoji:'🌮'},
  {name:'Ice Cream',emoji:'🍦'},
];

const COLLECTIONS = [
  {name:'Trending This Week',count:'12 places',img:'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=400&q=80'},
  {name:'Date Night Specials',count:'8 places',img:'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&q=80'},
  {name:'Best Coffee Spots',count:'15 places',img:'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=400&q=80'},
  {name:'Midnight Munchies',count:'20 places',img:'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&q=80'},
  {name:'Healthy Eats',count:'10 places',img:'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80'},
  {name:'Best Biryani',count:'18 places',img:'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=400&q=80'},
];

const TOP_PICKS = [
  {name:'Masala Dosa',disc:'30% OFF',img:'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=300&q=80'},
  {name:'Chicken Biryani',disc:'FREE DELIVERY',img:'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=300&q=80'},
  {name:'Margherita Pizza',disc:'BUY 1 GET 1',img:'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=300&q=80'},
  {name:'Chicken Burger',disc:'40% OFF',img:'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=300&q=80'},
  {name:'Pav Bhaji',disc:'20% OFF',img:'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=300&q=80'},
  {name:'Cold Coffee',disc:'FLAT ₹50 OFF',img:'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=300&q=80'},
  {name:'Gulab Jamun',disc:'25% OFF',img:'https://images.unsplash.com/photo-1555126634-323283e090fa?w=300&q=80'},
  {name:'Paneer Tikka',disc:'15% OFF',img:'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&q=80'},
];

const LOC_SUGG = [
  {name:'Koramangala',sub:'Bangalore, Karnataka',lat:12.9352,lng:77.6245},
  {name:'Indiranagar',sub:'Bangalore, Karnataka',lat:12.9716,lng:77.6412},
  {name:'HSR Layout',sub:'Bangalore, Karnataka',lat:12.9116,lng:77.6389},
  {name:'Whitefield',sub:'Bangalore, Karnataka',lat:12.9698,lng:77.7499},
  {name:'Marathahalli',sub:'Bangalore, Karnataka',lat:12.9591,lng:77.6971},
  {name:'Jayanagar',sub:'Bangalore, Karnataka',lat:12.9250,lng:77.5938},
  {name:'Bandra',sub:'Mumbai, Maharashtra',lat:19.0596,lng:72.8295},
  {name:'Andheri',sub:'Mumbai, Maharashtra',lat:19.1136,lng:72.8697},
  {name:'Connaught Place',sub:'New Delhi',lat:28.6315,lng:77.2167},
  {name:'Cyber Hub',sub:'Gurugram, Haryana',lat:28.4944,lng:77.0884},
];

// STATE VARIABLES
let cart = [];
let cartRest = null;
let couponApplied = null;
let couponSaving = 0;
let orders = [];
let payMethod = 'upi';
let currentTrackOrder = null;
let trackInterval = null;
let currentUser = SESSION_USER;
let loginTab = 'phone';
let resendTimer = null;

// RENDER ALL DATA
function renderAll() {
  renderTopPicks();
  renderCuisines();
  renderOffers();
  renderFilterTabs();
  renderRestaurantsList(RESTAURANTS);
  renderCollections();
  renderLocSugg();
  
  if (currentUser) {
    completeLoginUI(currentUser.name);
  }
}

function renderTopPicks() {
  document.getElementById('tp-scroll').innerHTML = TOP_PICKS.map(p => `
    <div class="tp-card" onclick="quickSearch('${p.name}')">
      <img class="tp-img" src="${p.img}" alt="${p.name}" loading="lazy">
      <div class="tp-overlay"></div>
      <div class="tp-info"><div class="tp-name">${p.name}</div><div class="tp-disc">${p.disc}</div></div>
    </div>`).join('');
}

function renderCuisines() {
  document.getElementById('cuisine-grid').innerHTML = CUISINES.map(c => `
    <div class="cuisine-card" onclick="filterByCuisine('${c.name}')">
      <div class="cuis-em">${c.emoji}</div>
      <div class="cuis-name">${c.name}</div>
    </div>`).join('');
}

function renderOffers() {
  document.getElementById('offers-grid').innerHTML = OFFERS.map(o => `
    <div class="offer-card" onclick="copyCouponCode('${o.code}')">
      <div class="offer-icon" style="background:#fff0f1">🏷️</div>
      <div>
        <div class="offer-title">${o.code}</div>
        <div class="offer-desc">Save ${o.discount_type === 'pct' ? o.discount_value + '%' : '₹' + o.discount_value}</div>
        <div class="offer-code">${o.code}</div>
      </div>
    </div>`).join('');
}

const FILTER_OPTS = ['Relevance', 'Rating 4.5+', 'Fast Delivery', 'Veg Only'];
function renderFilterTabs() {
  document.getElementById('filter-row').innerHTML = FILTER_OPTS.map((f, i) => `
    <div class="ftab ${i===0?'on':''}" onclick="setFilter(this,'${f}')">${f}</div>`).join('');
}

// Render dynamic list of restaurants
function renderRestaurantsList(list) {
  const container = document.getElementById('rest-grid');
  if (list.length === 0) {
    container.innerHTML = `
      <div class="empty-state" style="grid-column: 1/-1;">
        <i class="fas fa-search-minus"></i>
        <h3>No kitchens matched</h3>
        <p>Try clearing filters or search queries.</p>
      </div>`;
    return;
  }
  
  container.innerHTML = list.map(r => `
    <div class="rest-card" onclick="openMenu(${r.id})">
      <div class="rest-img-wrap">
        <img src="${r.image}" alt="${r.name}" loading="lazy">
        ${r.offer_text ? `<div class="rest-offer">${r.offer_text}</div>` : ''}
        <button class="rest-wish" id="wish-${r.id}" onclick="toggleWish(event,${r.id})"><i class="fas fa-heart"></i></button>
      </div>
      <div class="rest-info">
        <div class="rest-name">${r.name}</div>
        <div class="rest-cuis">${r.cuisine}</div>
        <div class="rest-meta">
          <div class="rest-rating"><i class="fas fa-star"></i> ${r.rating}</div>
          <div class="rest-time"><i class="fas fa-clock"></i> ${r.delivery_time} mins</div>
          <div class="rest-price">₹${r.min_order} min</div>
        </div>
      </div>
    </div>`).join('');
}

function renderCollections() {
  document.getElementById('coll-scroll').innerHTML = COLLECTIONS.map(c => `
    <div class="coll-card" onclick="toast('Curated collection matches nearby')">
      <img class="coll-img" src="${c.img}" alt="${c.name}" loading="lazy">
      <div class="coll-overlay"></div>
      <div class="coll-info"><div class="coll-name">${c.name}</div><div class="coll-cnt">${c.count}</div></div>
    </div>`).join('');
}

function renderLocSugg() {
  document.getElementById('loc-sugg').innerHTML = LOC_SUGG.map(l => `
    <div class="cuisine-card" style="padding:14px;border-radius:10px;text-align:left;flex-direction:row;gap:12px;" onclick="selectLoc('${l.name}')">
      <i class="fas fa-map-marker-alt" style="color:var(--red);font-size:20px;"></i>
      <div>
        <div style="font-weight:800;font-size:14px;">${l.name}</div>
        <div style="font-size:11.5px;color:var(--faint);">${l.sub}</div>
      </div>
    </div>`).join('');
}

// ── BACK TO TOP BUTTON ──
window.addEventListener('scroll', () => {
  const btn = document.getElementById('backToTop');
  if (window.scrollY > 400) {
    btn.classList.add('on');
  } else {
    btn.classList.remove('on');
  }
  
  document.querySelector('.navbar').style.boxShadow = window.scrollY > 10 ? '0 4px 20px rgba(0,0,0,.12)' : '';
});

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── WHATSAPP simulation CHAT SUPPORT ──
function openWhatsAppChat() {
  window.open("https://wa.me/919876543210?text=Hello%20WhatIf%20Support!%20I%20need%20assistance.", "_blank");
}

// ── HAMBURGER COLLAPSIBLE NAVBAR ──
function toggleMobileMenu() {
  const menu = document.getElementById('navActions');
  menu.classList.toggle('hidden');
  menu.style.display = menu.classList.contains('hidden') ? 'none' : 'flex';
}

// ── ROUTER TRANSITIONS ──
function showPage(id) {
  clearInterval(trackInterval);
  const activePage = document.querySelector('.page.active');
  
  if (activePage) {
    activePage.classList.remove('fade-in');
    setTimeout(() => {
      activePage.classList.remove('active');
      const newPage = document.getElementById(`page-${id}`);
      newPage.classList.add('active');
      setTimeout(() => {
        newPage.classList.add('fade-in');
        window.scrollTo(0,0);
      }, 50);
    }, 200);
  } else {
    const newPage = document.getElementById(`page-${id}`);
    newPage.classList.add('active');
    setTimeout(() => {
      newPage.classList.add('fade-in');
      window.scrollTo(0,0);
    }, 50);
  }

  if (id === 'orders') fetchOrdersHistory();
}

// ── SEARCH LOGIC VIA PHP GET_RESTAURANTS AJAX ──
function quickSearch(val) {
  document.getElementById('nav-search').value = val;
  document.getElementById('hero-search').value = val;
  doSearch(val);
}

function doSearch(forcedVal) {
  const val = forcedVal || document.getElementById('nav-search').value || document.getElementById('hero-search').value;
  if (!val.trim()) {
    toast('Enter a keyword to search', 'warn');
    return;
  }
  
  showPage('search');
  document.getElementById('search-query-text').textContent = val;
  
  const container = document.getElementById('search-results-container');
  // Render skeleton cards first
  container.innerHTML = `
    <div class="rest-grid">
      ${Array.from({length:4}, () => `
        <div class="skeleton-card">
          <div class="skeleton-img"></div>
          <div class="skeleton-info">
            <div class="skeleton-line title"></div>
            <div class="skeleton-line subtitle"></div>
            <div class="skeleton-line meta"></div>
          </div>
        </div>`).join('')}
    </div>`;

  fetch(`?action=get_restaurants&q=${encodeURIComponent(val)}`)
    .then(r => r.json())
    .then(data => {
      document.getElementById('search-result-count').textContent = `${data.length} kitchens found near Koramangala`;
      renderSearchResults(data);
    });
}

function renderSearchResults(data) {
  const container = document.getElementById('search-results-container');
  if (data.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-frown" style="font-size: 56px;color: var(--faint);"></i>
        <h3>No direct search matches</h3>
        <p>Try searching "biryani", "pizza", or "Truffles"</p>
      </div>`;
    return;
  }

  container.innerHTML = `
    <div class="rest-grid">
      ${data.map(r => `
        <div class="rest-card" onclick="openMenu(${r.id})">
          <div class="rest-img-wrap">
            <img src="${r.image}" alt="${r.name}" loading="lazy">
            ${r.offer_text ? `<div class="rest-offer">${r.offer_text}</div>` : ''}
          </div>
          <div class="rest-info">
            <div class="rest-name">${r.name}</div>
            <div class="rest-cuis">${r.cuisine}</div>
            <div class="rest-meta">
              <div class="rest-rating"><i class="fas fa-star"></i> ${r.rating}</div>
              <div class="rest-time"><i class="fas fa-clock"></i> ${r.delivery_time} mins</div>
              <div class="rest-price">₹${r.min_order} min</div>
            </div>
          </div>
        </div>`).join('')}
    </div>`;
}

// ── DYNAMIC FILTERING & MENU FETCHES ──
function filterByCuisine(cuisine) {
  // Clear search grids and filter
  const grid = document.getElementById('rest-grid');
  grid.innerHTML = Array.from({length:4}, () => `
    <div class="skeleton-card">
      <div class="skeleton-img"></div>
      <div class="skeleton-info">
        <div class="skeleton-line title"></div>
        <div class="skeleton-line subtitle"></div>
        <div class="skeleton-line meta"></div>
      </div>
    </div>`).join('');
    
  fetch(`?action=get_restaurants&cuisine=${encodeURIComponent(cuisine)}`)
    .then(res => res.json())
    .then(data => {
      renderRestaurantsList(data);
      toast(`Showing ${cuisine} kitchens`);
    });
}

function setFilter(el, f) {
  document.querySelectorAll('.ftab').forEach(t => t.classList.remove('on'));
  el.classList.add('on');
  
  let url = '?action=get_restaurants';
  if (f === 'Rating 4.5+') url += '&rating=4.5';
  if (f === 'Veg Only') url += '&q=veg';
  if (f === 'Fast Delivery') url += '&sort=Time';
  
  fetch(url)
    .then(res => res.json())
    .then(data => {
      renderRestaurantsList(data);
    });
}

// Open Restaurant Menu dynamically via AJAX
function openMenu(id) {
  const r = RESTAURANTS.find(x => x.id == id);
  if (!r) return;
  
  document.getElementById('menu-rest-img').src = r.image;
  document.getElementById('menu-rest-name').textContent = r.name;
  document.getElementById('menu-rest-cuis').textContent = r.cuisine;
  document.getElementById('menu-rest-meta').innerHTML = `
    <div class="rest-rating"><i class="fas fa-star"></i> ${r.rating}</div>
    <div class="rest-time"><i class="fas fa-clock"></i> ${r.delivery_time} mins</div>
    <div class="rest-price">₹${r.min_order} min order</div>`;
    
  const body = document.getElementById('menu-body');
  // Skeleton load
  body.innerHTML = `
    <div class="menu-sec">
      <div class="skeleton-line title"></div>
      <div class="menu-item">
        <div class="mi-info">
          <div class="skeleton-line subtitle" style="width: 40%"></div>
          <div class="skeleton-line subtitle" style="width: 80%; margin-top: 8px;"></div>
        </div>
        <div class="mi-img" style="background:#eee"></div>
      </div>
    </div>`;
    
  document.getElementById('menu-overlay').classList.add('on');
  document.body.style.overflow = 'hidden';

  fetch(`?action=get_menu&restaurant_id=${id}`)
    .then(res => res.json())
    .then(data => {
      let html = '';
      for (const cat in data) {
        html += `
          <div class="menu-sec">
            <h3 class="menu-sec-title">${cat}</h3>
            ${data[cat].map(item => `
              <div class="menu-item">
                <div class="mi-info">
                  <div class="veg-dot ${item.type}"></div>
                  <div class="mi-name">${item.name}</div>
                  <div class="mi-desc">${item.description || ''}</div>
                </div>
                <div class="mi-price">₹${parseInt(item.price)}</div>
                ${item.image ? `<img class="mi-img" src="${item.image}" alt="${item.name}" loading="lazy">` : ''}
                <button class="add-btn" onclick="addToCart('${r.name}','${r.image}','${item.name}',${item.price},'${item.type}', ${r.id});event.stopPropagation()">ADD</button>
              </div>`).join('')}
          </div>`;
      }
      body.innerHTML = html || '<div style="padding:22px;text-align:center;">No active menu items available.</div>';
    });
}

function closeMenu() {
  document.getElementById('menu-overlay').classList.remove('on');
  document.body.style.overflow = '';
}

function closeMenuModal(e) {
  if (e.target === document.getElementById('menu-overlay')) {
    closeMenu();
  }
}

// ── CART PROCESS ──
function addToCart(restName, restImg, itemName, price, type, restId) {
  if (cartRest && cartRest.id !== restId) {
    if (!confirm(`Your cart contains items from ${cartRest.name}. Start a fresh cart?`)) return;
    cart = []; couponApplied = null; couponSaving = 0;
  }
  
  cartRest = {id: restId, name: restName, img: restImg};
  const exists = cart.find(i => i.name === itemName);
  if (exists) {
    exists.qty++;
  } else {
    cart.push({name: itemName, price: price, type: type, qty: 1});
  }
  
  updateCartUI();
  toast(`${itemName} added to cart!`);
}

function removeFromCart(idx) {
  cart[idx].qty--;
  if (cart[idx].qty <= 0) {
    cart.splice(idx, 1);
  }
  if (cart.length === 0) {
    cartRest = null;
    couponApplied = null;
    couponSaving = 0;
  }
  updateCartUI();
}

function updateCartUI() {
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const gst = Math.round(subtotal * 0.05);
  const delivery = (subtotal > 299 && couponApplied === 'FREEDEL') ? 0 : 29;
  const disc = couponApplied ? couponSaving : 0;
  const total = subtotal + gst + delivery - disc;
  
  const qty = cart.reduce((s, i) => s + i.qty, 0);
  const badge = document.getElementById('cart-badge');
  if (qty > 0) {
    badge.textContent = qty;
    badge.classList.add('on');
  } else {
    badge.classList.remove('on');
  }

  const strip = document.getElementById('cart-rest-strip');
  if (cartRest) {
    strip.style.display = 'block';
    document.getElementById('cart-rest-label').textContent = cartRest.name;
  } else {
    strip.style.display = 'none';
  }

  const body = document.getElementById('cart-body');
  if (cart.length === 0) {
    body.innerHTML = '<div class="cart-empty-msg"><i class="fas fa-shopping-bag"></i><p>Your cart is empty</p></div>';
    document.getElementById('cart-ft').style.display = 'none';
    return;
  }

  body.innerHTML = cart.map((item, i) => `
    <div class="cart-item">
      <div class="veg-dot ${item.type}"></div>
      <div class="ci-name">${item.name}</div>
      <div class="ci-qty">
        <button class="cq-btn" onclick="removeFromCart(${i})">−</button>
        <span class="cq-val">${item.qty}</span>
        <button class="cq-btn" onclick="addToCart('${cartRest.name}','${cartRest.img}','${item.name}',${item.price},'${item.type}', ${cartRest.id})">+</button>
      </div>
      <div class="ci-price">₹${item.price * item.qty}</div>
    </div>`).join('');
    
  document.getElementById('cart-ft').style.display = 'block';
  document.getElementById('cart-subtotal').textContent = `₹${subtotal}`;
  document.getElementById('cart-delivery').textContent = delivery === 0 ? 'FREE' : '₹29';
  document.getElementById('cart-gst').textContent = `₹${gst}`;
  document.getElementById('cart-total').textContent = `₹${total}`;
}

function openCart() {
  document.getElementById('cart-drawer').classList.add('on');
  document.getElementById('cart-overlay').classList.add('on');
}

function closeCart() {
  document.getElementById('cart-drawer').classList.remove('on');
  document.getElementById('cart-overlay').classList.remove('on');
}

// Coupon validation handler
function applyCoupon() {
  const code = document.getElementById('coupon-input').value.trim().toUpperCase();
  if (!code) {
    toast('Please enter coupon code', 'warn');
    return;
  }
  
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  
  const formData = new FormData();
  formData.append('code', code);
  formData.append('subtotal', subtotal);
  
  fetch('?action=validate_coupon', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      couponApplied = code;
      couponSaving = data.discount_type === 'pct' ? Math.min(Math.round(subtotal * data.discount_value / 100), data.max || Infinity) : data.discount_value;
      document.getElementById('coupon-save').textContent = couponSaving;
      document.getElementById('coupon-applied').classList.add('on');
      updateCartUI();
      toast(`Coupon ${code} applied successfully!`, 'success');
    } else {
      toast(data.msg || 'Invalid coupon', 'warn');
    }
  });
}

// ── PAYMENT MODAL COUPONS LIST ──
function toggleCouponList() {
  const wrapper = document.getElementById('couponListWrapper');
  const chevron = document.getElementById('couponChevron');
  const isOpen = wrapper.style.display === 'block';
  wrapper.style.display = isOpen ? 'none' : 'block';
  chevron.style.transform = isOpen ? 'rotate(0)' : 'rotate(180deg)';
  
  if (!isOpen) {
    wrapper.innerHTML = OFFERS.map(c => `
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f3f4f6;">
        <div>
          <div style="font-weight:700;font-size:13px">${c.code}</div>
          <div style="font-size:11px;color:var(--muted)">Get ${c.discount_type === 'pct' ? c.discount_value + '%' : '₹' + c.discount_value} discount</div>
        </div>
        <button class="add-btn" style="padding:4px 10px;font-size:11px" onclick="selectCheckoutCoupon('${c.code}')">Apply</button>
      </div>`).join('');
  }
}

function selectCheckoutCoupon(code) {
  document.getElementById('coupon-input').value = code;
  applyCoupon();
  toggleCouponList();
}

function openPay() {
  if (!currentUser) {
    document.getElementById('signin-modal').classList.add('on');
    return;
  }
  
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const gst = Math.round(subtotal * 0.05);
  const delivery = (subtotal > 299 && couponApplied === 'FREEDEL') ? 0 : 29;
  const disc = couponApplied ? couponSaving : 0;
  const total = subtotal + gst + delivery - disc;
  
  document.getElementById('pay-items-list').innerHTML = cart.map(i => `
    <div class="pos-row"><span>${i.name} ×${i.qty}</span><span>₹${i.price * i.qty}</span></div>`).join('');
    
  document.getElementById('pay-subtotal').textContent = `₹${subtotal}`;
  document.getElementById('pay-delivery').textContent = delivery === 0 ? 'FREE' : '₹29';
  document.getElementById('pay-gst').textContent = `₹${gst}`;
  document.getElementById('pay-total').textContent = `₹${total}`;
  
  if (disc > 0) {
    document.getElementById('pay-disc-row').style.display = 'flex';
    document.getElementById('pay-disc-val').textContent = `-₹${disc}`;
  } else {
    document.getElementById('pay-disc-row').style.display = 'none';
  }
  
  closeCart();
  document.getElementById('pay-modal').classList.add('on');
}

function closePay() {
  document.getElementById('pay-modal').classList.remove('on');
}

function selectPayMethod(m) {
  payMethod = m;
  document.querySelectorAll('.pay-method').forEach(b => b.classList.remove('on'));
  document.getElementById(`pm-${m}`).classList.add('on');
}

// PLACE ORDER WITH TRANSCTION IN BACKEND
function processPayment() {
  const addrInput = document.getElementById('pay-addr');
  const nameInput = document.getElementById('pay-name');
  
  let valid = true;
  if (!addrInput.value.trim()) {
    addrInput.classList.add('is-invalid');
    document.getElementById('pay-addr-err').style.display = 'block';
    valid = false;
  } else {
    addrInput.classList.remove('is-invalid');
    document.getElementById('pay-addr-err').style.display = 'none';
  }
  
  if (!nameInput.value.trim()) {
    nameInput.classList.add('is-invalid');
    document.getElementById('pay-name-err').style.display = 'block';
    valid = false;
  } else {
    nameInput.classList.remove('is-invalid');
    document.getElementById('pay-name-err').style.display = 'none';
  }

  if (!valid) return;

  const btn = document.getElementById('pay-now-btn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Checkout…';
  btn.disabled = true;

  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const gst = Math.round(subtotal * 0.05);
  const delivery = (subtotal > 299 && couponApplied === 'FREEDEL') ? 0 : 29;
  const disc = couponApplied ? couponSaving : 0;
  const total = subtotal + gst + delivery - disc;

  const formData = new FormData();
  formData.append('restaurant_id', cartRest.id);
  formData.append('subtotal', subtotal);
  formData.append('delivery_fee', delivery);
  formData.append('gst', gst);
  formData.append('discount', disc);
  formData.append('total', total);
  formData.append('address', addrInput.value.trim());
  formData.append('coupon_code', couponApplied || '');
  formData.append('items', JSON.stringify(cart));

  fetch('?action=place_order', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
    btn.disabled = false;
    
    if (data.success) {
      closePay();
      cart = []; cartRest = null; couponApplied = null; couponSaving = 0;
      updateCartUI();
      
      document.getElementById('success-order-id').textContent = `Order #ZMT${data.order_id}`;
      document.getElementById('order-success').classList.add('on');
      
      // Seed order history locally so we can track immediately
      currentTrackOrder = {
        id: data.order_id,
        restName: document.getElementById('cart-rest-label').textContent || 'Gourmet Kitchen',
        status: 'Placed',
        orderedAt: Date.now(),
        subtotal: subtotal,
        gst: gst,
        delivery_fee: delivery,
        total: total,
        items: JSON.parse(JSON.stringify(cart))
      };
    } else {
      toast(data.msg || 'Checkout failed', 'warn');
    }
  });
}

// ── ORDER TRACKING SCREEN ──
function trackOrder(id) {
  fetchOrdersHistory().then(() => {
    currentTrackOrder = orders.find(o => o.id == id);
    trackCurrentOrder();
  });
}

function trackCurrentOrder() {
  if (!currentTrackOrder) {
    showPage('orders');
    return;
  }
  
  const o = currentTrackOrder;
  document.getElementById('track-order-id').textContent = `Order #ZMT${o.id}`;
  document.getElementById('track-rest-name').textContent = `Status: ${o.status} inside ${o.restName || 'Gourmet Kitchen'}`;
  
  // Render bill details in tracking card
  document.getElementById('track-bill').innerHTML = `
    <div class="to-bill-row"><span>Subtotal</span><span>₹${parseInt(o.subtotal)}</span></div>
    <div class="to-bill-row"><span>GST charges</span><span>₹${parseInt(o.gst)}</span></div>
    <div class="to-bill-row"><span>Delivery</span><span>${parseInt(o.delivery_fee) === 0 ? 'FREE' : '₹' + parseInt(o.delivery_fee)}</span></div>
    <div class="to-bill-row tot"><span>Grand Total</span><span>₹${parseInt(o.total)}</span></div>`;
    
  if (o.items) {
    document.getElementById('track-order-items').innerHTML = o.items.map(i => `
      <div class="to-item"><span>${i.name} ×${i.qty}</span><span>₹${parseInt(i.price) * i.qty}</span></div>`).join('');
  }

  updateVisualTrackingProgressBar(o.status);
  showPage('track');

  // Clear any existing tracking interval
  clearInterval(trackInterval);

  // Define steps
  const steps = ['Placed', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered'];
  
  // Start simulation loop (every 10 seconds, advance order status in PostgreSQL!)
  trackInterval = setInterval(() => {
    const currentIdx = steps.indexOf(currentTrackOrder.status);
    if (currentIdx !== -1 && currentIdx < steps.length - 1) {
      const nextStatus = steps[currentIdx + 1];
      
      const formData = new FormData();
      formData.append('order_id', currentTrackOrder.id);
      formData.append('status', nextStatus);
      
      fetch('?action=update_order_status', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          currentTrackOrder.status = nextStatus;
          document.getElementById('track-rest-name').textContent = `Status: ${nextStatus} inside ${currentTrackOrder.restName || 'Gourmet Kitchen'}`;
          updateVisualTrackingProgressBar(nextStatus);
          
          let toastEmoji = '🍲';
          if (nextStatus === 'Confirmed') toastEmoji = '✅';
          else if (nextStatus === 'Preparing') toastEmoji = '👨‍🍳';
          else if (nextStatus === 'Out for Delivery') toastEmoji = '🛵';
          else if (nextStatus === 'Delivered') toastEmoji = '🎉';
          
          toast(`${toastEmoji} Order status updated: ${nextStatus}!`);
          
          if (nextStatus === 'Delivered') {
            clearInterval(trackInterval);
          }
        }
      });
    } else {
      clearInterval(trackInterval);
    }
  }, 10000);
}

// Upgrade order tracking screen visual progress bar
function updateVisualTrackingProgressBar(status) {
  const steps = ['Placed', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered'];
  const currentIdx = steps.indexOf(status);
  
  const fill = document.getElementById('trackProgressLine');
  fill.style.width = `${(currentIdx / (steps.length - 1)) * 100}%`;
  
  steps.forEach((step, idx) => {
    const el = document.getElementById(`step-${step.toLowerCase().replace(' ', '')}`);
    el.classList.remove('done', 'active');
    if (idx < currentIdx) {
      el.classList.add('done');
    } else if (idx === currentIdx) {
      el.classList.add('active');
    }
  });
}

// ── FEEDBACK REVIEW SUBMISSIONS ──
let feedbackOrderId = null;
function openFeedback(id) {
  feedbackOrderId = id;
  const o = orders.find(x => x.id == id);
  if (!o) return;
  
  document.getElementById('fb-rest-img').src = o.restImg;
  document.getElementById('fb-rest-name').textContent = o.restName;
  document.getElementById('fb-rest-order').textContent = `Order #ZMT${o.id}`;
  
  document.getElementById('overall-stars').innerHTML = Array.from({length:5}, (_,i) => `
    <span class="star-btn" data-val="${i+1}" onclick="setFeedbackRating('overall', ${i+1})">⭐</span>`).join('');
  document.getElementById('delivery-stars').innerHTML = Array.from({length:5}, (_,i) => `
    <span class="star-btn" data-val="${i+1}" onclick="setFeedbackRating('delivery', ${i+1})">⭐</span>`).join('');
    
  showPage('feedback');
}

let ratingsState = { overall: 5, delivery: 5 };
function setFeedbackRating(type, val) {
  ratingsState[type] = val;
  document.querySelectorAll(`#${type}-stars .star-btn`).forEach((s, idx) => {
    s.classList.toggle('on', idx < val);
  });
}

function submitFeedback() {
  const comment = document.getElementById('fb-comment').value;
  
  const formData = new FormData();
  formData.append('order_id', feedbackOrderId);
  formData.append('rating', ratingsState.overall);
  formData.append('delivery_rating', ratingsState.delivery);
  formData.append('comment', comment);
  
  fetch('?action=submit_review', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    document.getElementById('fb-card').innerHTML = `
      <div class="fb-success">
        <div class="fb-success-icon">🎉</div>
        <h3>Feedback Submitted!</h3>
        <p style="color:var(--faint);margin-top:8px">Your review assists Bangalore kitchens in maintaining absolute premium quality.</p>
        <button class="nav-btn nav-btn-red" style="margin-top:20px" onclick="showPage('orders')">Back to Orders</button>
      </div>`;
  });
}

// ── SIGN IN / REGISTER LOGIC ──
function showLoginPage() {
  showPage('login');
}

function switchLoginTab(tab) {
  loginTab = tab;
  document.getElementById('ltab-phone').classList.toggle('on', tab === 'phone');
  document.getElementById('ltab-email').classList.toggle('on', tab === 'email');
  document.getElementById('login-phone-wrap').style.display = tab === 'phone' ? 'block' : 'none';
  document.getElementById('login-email-wrap').style.display = tab === 'email' ? 'block' : 'none';
}

function generateSimulatedOTP(phoneOrEmail) {
  const btn = event.target;
  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
  btn.disabled = true;

  const formData = new FormData();
  if (loginTab === 'phone') {
    formData.append('phone', phoneOrEmail);
  } else {
    formData.append('email', phoneOrEmail);
  }

  fetch('?action=login_send_otp', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.innerHTML = originalHtml;
    btn.disabled = false;
    
    if (data.success) {
      toast('Verification code dispatched successfully.');
      showSimulatedOTPPopup(phoneOrEmail, data.otp);
      
      if (loginTab === 'phone') {
        document.getElementById('login-step1').style.display = 'none';
        document.getElementById('login-step2').style.display = 'block';
        document.getElementById('login-otp-phone').textContent = phoneOrEmail;
        buildOTPInputBoxes('login-otp-boxes');
      } else {
        document.getElementById('login-email-step1').style.display = 'none';
        document.getElementById('login-email-step2').style.display = 'block';
        document.getElementById('login-otp-email-addr').textContent = phoneOrEmail;
        buildOTPInputBoxes('login-email-otp-boxes');
      }
      startResendTimer();
    }
  });
}

function buildOTPInputBoxes(containerId) {
  const container = document.getElementById(containerId);
  container.innerHTML = Array.from({length: 6}, (_, i) => `
    <input class="otp-input" type="text" maxlength="1" id="${containerId}-box-${i}" oninput="focusNextOTPBox(this, ${i}, '${containerId}')">`).join('');
}

function focusNextOTPBox(el, idx, containerId) {
  if (el.value.length === 1 && idx < 5) {
    document.getElementById(`${containerId}-box-${idx+1}`).focus();
  }
}

function loginSendOTP() {
  const ph = document.getElementById('login-phone-num');
  if (ph.value.length < 10 || isNaN(ph.value)) {
    ph.classList.add('is-invalid');
    document.getElementById('phone-err-msg').style.display = 'block';
    return;
  }
  ph.classList.remove('is-invalid');
  document.getElementById('phone-err-msg').style.display = 'none';
  generateSimulatedOTP(ph.value);
}

function loginSendEmailOTP() {
  const email = document.getElementById('login-email');
  if (!email.value.includes('@')) {
    email.classList.add('is-invalid');
    document.getElementById('email-err-msg').style.display = 'block';
    return;
  }
  email.classList.remove('is-invalid');
  document.getElementById('email-err-msg').style.display = 'none';
  generateSimulatedOTP(email.value);
}

function verifyOTPAndLogin(phone, email, otp) {
  const formData = new FormData();
  formData.append('phone', phone);
  formData.append('email', email);
  formData.append('otp', otp);
  
  fetch('?action=login', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      currentUser = data.user;
      completeLoginUI(currentUser.name);
      toast(`Welcome back, ${currentUser.name}! 🎉`);
      showPage('home');
    } else {
      toast(data.msg || 'OTP incorrect', 'warn');
    }
  });
}

function loginVerifyOTP() {
  const otp = Array.from({length: 6}, (_, i) => document.getElementById(`login-otp-boxes-box-${i}`).value).join('');
  const ph = document.getElementById('login-phone-num').value;
  verifyOTPAndLogin(ph, '', otp);
}

function loginVerifyEmailOTP() {
  const otp = Array.from({length: 6}, (_, i) => document.getElementById(`login-email-otp-boxes-box-${i}`).value).join('');
  const email = document.getElementById('login-email').value;
  verifyOTPAndLogin('', email, otp);
}

function completeLoginUI(name) {
  document.getElementById('nav-user-avatar').textContent = name.charAt(0).toUpperCase();
  document.getElementById('nav-user-name').textContent = name.length > 10 ? name.slice(0,10)+'…' : name;
  document.getElementById('nav-signin-btn').style.display = 'none';
  document.getElementById('nav-user-pill').style.display = 'flex';
}

function logoutUser() {
  if (!confirm('Log out of What If?')) return;
  fetch('?action=logout')
    .then(() => {
      currentUser = null;
      document.getElementById('nav-signin-btn').style.display = 'block';
      document.getElementById('nav-user-pill').style.display = 'none';
      toast('Signed out successfully.');
      showPage('register');
    });
}

// Register workflow
function switchAuthTab(tab) {
  document.getElementById('rtab-register').classList.toggle('on', tab === 'register');
  document.getElementById('rtab-signin').classList.toggle('on', tab === 'signin');
  document.getElementById('register-form-wrap').style.display = tab === 'register' ? 'block' : 'none';
  document.getElementById('register-signin-wrap').style.display = tab === 'signin' ? 'block' : 'none';
}

function registerSendOTP() {
  const name = document.getElementById('reg-name');
  const email = document.getElementById('reg-email');
  const phone = document.getElementById('reg-phone');
  
  let valid = true;
  if (!name.value.trim()) {
    name.classList.add('is-invalid');
    document.getElementById('reg-name-err').style.display = 'block';
    valid = false;
  } else {
    name.classList.remove('is-invalid');
    document.getElementById('reg-name-err').style.display = 'none';
  }
  
  if (!email.value.includes('@')) {
    email.classList.add('is-invalid');
    document.getElementById('reg-email-err').style.display = 'block';
    valid = false;
  } else {
    email.classList.remove('is-invalid');
    document.getElementById('reg-email-err').style.display = 'none';
  }

  if (phone.value.length < 10 || isNaN(phone.value)) {
    phone.classList.add('is-invalid');
    document.getElementById('reg-phone-err').style.display = 'block';
    valid = false;
  } else {
    phone.classList.remove('is-invalid');
    document.getElementById('reg-phone-err').style.display = 'none';
  }

  if (!valid) return;

  const formData = new FormData();
  formData.append('name', name.value.trim());
  formData.append('email', email.value.trim());
  formData.append('phone', phone.value.trim());
  formData.append('password', 'premium_pass'); // Default placeholder password

  fetch('?action=register_send_otp', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      toast('Verification code dispatched.');
      showSimulatedOTPPopup(phone.value, data.otp);
      
      document.getElementById('register-step1').style.display = 'none';
      document.getElementById('register-step2').style.display = 'block';
      document.getElementById('reg-otp-phone-lbl').textContent = phone.value;
      buildOTPInputBoxes('register-otp-boxes');
    } else {
      toast(data.msg || 'Registration failed', 'warn');
    }
  });
}

function registerVerifyOTP() {
  const otp = Array.from({length: 6}, (_, i) => document.getElementById(`register-otp-boxes-box-${i}`).value).join('');
  const formData = new FormData();
  formData.append('otp', otp);
  
  fetch('?action=register', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      currentUser = data.user;
      completeLoginUI(currentUser.name);
      toast(`Registration complete! Welcome, ${currentUser.name} 🎉`);
      showPage('home');
    } else {
      toast(data.msg || 'Verification failed', 'warn');
    }
  });
}

function registerPageSignIn() {
  const inp = document.getElementById('rsi-email');
  const val = inp.value.trim();
  if (!val) {
    inp.classList.add('is-invalid');
    document.getElementById('rsi-email-err').style.display = 'block';
    return;
  }
  inp.classList.remove('is-invalid');
  document.getElementById('rsi-email-err').style.display = 'none';
  
  // Cleanly detect if input is email or phone
  const isEmail = val.includes('@');
  
  // Transition seamlessly to the main Login Page
  showLoginPage();
  
  // Switch to the matching tab (phone or email)
  switchLoginTab(isEmail ? 'email' : 'phone');
  
  // Prefill field and automatically request simulated OTP dispatch
  if (isEmail) {
    document.getElementById('login-email').value = val;
    loginSendEmailOTP();
  } else {
    document.getElementById('login-phone-num').value = val;
    loginSendOTP();
  }
}

// Simulated OTP Notification Animations
let otpSimTimer = null;
function showSimulatedOTPPopup(to, code) {
  const pop = document.getElementById('otp-sim-popup');
  document.getElementById('otp-sim-sub').textContent = to;
  document.getElementById('otp-sim-code').textContent = code;
  pop.classList.add('on');
  
  clearTimeout(otpSimTimer);
  otpSimTimer = setTimeout(() => pop.classList.remove('on'), 9000);
}

function closeOTPPopup() {
  document.getElementById('otp-sim-popup').classList.remove('on');
}

function startResendTimer() {
  let time = 30;
  document.getElementById('resend-btn').disabled = true;
  document.getElementById('resend-timer').style.display = 'block';
  
  clearInterval(resendTimer);
  resendTimer = setInterval(() => {
    time--;
    document.getElementById('timer-count').textContent = time;
    if (time <= 0) {
      clearInterval(resendTimer);
      document.getElementById('resend-btn').disabled = false;
      document.getElementById('resend-timer').style.display = 'none';
    }
  }, 1000);
}

function resendOTP() {
  const ph = document.getElementById('login-phone-num').value;
  generateSimulatedOTP(ph);
}

// ── AJAX FETCH ORDERS HISTORY ──
function fetchOrdersHistory() {
  return fetch('?action=get_orders')
    .then(res => res.json())
    .then(data => {
      orders = data;
      renderOrdersList();
    });
}

function renderOrdersList() {
  const el = document.getElementById('orders-list');
  if (orders.length === 0) {
    el.innerHTML = `
      <div style="text-align:center;padding:60px 0;color:var(--faint)">
        <i class="fas fa-receipt" style="font-size:48px;margin-bottom:16px;display:block"></i>
        <div style="font-size:15px;font-weight:600">No Orders Placed Yet</div>
        <button class="nav-btn nav-btn-red" style="margin-top:20px" onclick="showPage('home')">Browse Kitchens</button>
      </div>`;
    return;
  }

  el.innerHTML = orders.map(o => `
    <div class="order-card">
      <div class="order-card-hd">
        <img class="order-rest-img" src="${o.restImg}" alt="">
        <div>
          <div class="order-rest-name">${o.restName}</div>
          <div class="order-date">${o.created_at} · ${o.items.length} dishes</div>
        </div>
        <div class="order-status ${o.status === 'Delivered' ? 'status-delivered' : 'status-active'}">${o.status}</div>
      </div>
      <div class="order-items-row">${o.items.map(i => `${i.name} ×${i.qty}`).join(', ')}</div>
      <div class="order-actions">
        ${o.status === 'Delivered' ? `
          <button class="order-action-btn oab-outline" onclick="reorderOrder(${o.id})"><i class="fas fa-redo"></i> Reorder</button>
          <button class="order-action-btn oab-red" onclick="openFeedback(${o.id})"><i class="fas fa-star"></i> Rate Meal</button>
        ` : `
          <button class="order-action-btn oab-outline" onclick="trackOrder(${o.id})"><i class="fas fa-map-marker-alt"></i> Track Live</button>
        `}
      </div>
    </div>`).join('');
}

function reorderOrder(id) {
  const o = orders.find(x => x.id == id);
  if (!o) return;
  
  cart = o.items.map(i => ({name: i.name, price: i.price, qty: i.qty, type: 'veg'}));
  cartRest = {id: o.restaurant_id, name: o.restName, img: o.restImg};
  updateCartUI();
  openCart();
  toast('Dishes imported to cart successfully.');
}

// ── LOCATION SELECTOR ──
function openLocModal() {
  document.getElementById('loc-modal').classList.add('on');
}

function selectLoc(loc) {
  document.getElementById('nav-loc-text').textContent = `${loc}, Bangalore`;
  document.getElementById('hero-loc-text').textContent = loc;
  document.getElementById('loc-modal').classList.remove('on');
  toast(`Location changed to ${loc}`);
}

function detectLoc() {
  selectLoc('Indiranagar');
}

// ── MODAL BACKDROP CLICK DISMISS ──
function closeModalBackdrop(e, modalId) {
  if (e.target === document.getElementById(modalId)) {
    document.getElementById(modalId).classList.remove('on');
  }
}

// ── MISCELLANEOUS UTILITIES ──
function copyCouponCode(code) {
  navigator.clipboard.writeText(code).then(() => {
    toast(`Promo code ${code} copied to clipboard!`);
  });
}

function toggleWish(e, id) {
  e.stopPropagation();
  const heart = document.getElementById(`wish-${id}`);
  heart.classList.toggle('on');
  toast(heart.classList.contains('on') ? 'Added to wishlist ❤️' : 'Removed from wishlist');
}

function socialLogin(provider) {
  toast(`Connecting secure OAuth session with ${provider}…`);
}

let toastT = null;
function toast(msg, type = '') {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast on' + (type ? ' ' + type : '');
  
  clearTimeout(toastT);
  toastT = setTimeout(() => t.classList.remove('on'), 3000);
}

// ── ON INITIAL LOAD GATE ──
window.addEventListener('DOMContentLoaded', () => {
  renderAll();
  
  if (!currentUser) {
    showPage('register');
  } else {
    showPage('home');
  }
});
</script>
</body>
</html>
