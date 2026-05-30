<?php
/**
 * PostgreSQL Database Explorer & Viewer
 * An ultra-premium, interactive developer utility to view tables, schemas, and live data in the 'whatif_db'.
 */

session_start();

// 1. Parse Database Config from index.php dynamically to avoid duplicate configurations!
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'whatif_db';
$db_user = 'postgres';
$db_pass = 'postgres123';

if (file_exists('index.php')) {
    $index_code = file_get_contents('index.php');
    
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
$connection_error = null;
$pdo = null;

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    $connection_error = $e->getMessage();
}

// Handle Ajax details for loading table data dynamically
if (isset($_GET['ajax_table']) && $pdo) {
    $table = $_GET['ajax_table'];
    
    // Whitelist tables to prevent SQL injection
    $allowed_tables = ['users', 'restaurants', 'menu_items', 'orders', 'order_items', 'coupons', 'addresses', 'reviews'];
    if (!in_array($table, $allowed_tables)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid table requested']);
        exit;
    }
    
    try {
        // Fetch schema
        $schema_stmt = $pdo->prepare("
            SELECT column_name, data_type, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = :table
            ORDER BY ordinal_position
        ");
        $schema_stmt->execute([':table' => $table]);
        $columns = $schema_stmt->fetchAll();
        
        // Fetch rows (limit to 100 for safety and speed)
        $data_stmt = $pdo->query("SELECT * FROM " . db_escape_identifier($table) . " LIMIT 100");
        $rows = $data_stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'table' => $table,
            'columns' => $columns,
            'rows' => $rows
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Function to escape PG identifier
function db_escape_identifier($name) {
    return '"' . str_replace('"', '""', $name) . '"';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PostgreSQL Database Explorer – What If</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #f8fafc; /* Very light cool grey */
            --bg-card: #ffffff; /* Solid clean white */
            --bg-sidebar: #f1f5f9; /* Soft light slate grey sidebar */
            --border-neon: #e2e8f0; /* Soft border */
            --border-neon-hover: #cbd5e1; /* Hover border */
            --neon-accent: #2563eb; /* Royal blue brand accent */
            --neon-green: #059669; /* Rich professional emerald green */
            --neon-cyan: #0d9488; /* Professional dark teal */
            --text-main: #0f172a; /* Slate 900 primary text */
            --text-muted: #475569; /* Slate 600 secondary text */
            --text-dim: #94a3b8; /* Slate 400 dim text */
            --glass-grad: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(255,255,255,0.95) 100%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 10% 20%, rgba(37, 99, 235, 0.04) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(13, 148, 136, 0.04) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header Style */
        header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-neon);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            font-size: 28px;
            background: linear-gradient(135deg, var(--neon-accent), var(--neon-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 4px rgba(37, 99, 235, 0.15));
        }

        .logo-title {
            font-weight: 800;
            font-size: 20px;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #0f172a, #334155);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .connection-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(5, 150, 105, 0.08);
            border: 1px solid rgba(5, 150, 105, 0.2);
            color: var(--neon-green);
        }

        .connection-badge.failed {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        /* Main Content Grid */
        .app-container {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 73px);
        }

        /* Sidebar Style */
        .sidebar {
            width: 280px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-neon);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .table-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .table-item {
            padding: 12px 16px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 14px;
            color: #334155;
        }

        .table-item i.table-icon {
            color: var(--neon-accent);
            margin-right: 10px;
        }

        .table-item:hover {
            background: rgba(37, 99, 235, 0.06);
            border-color: rgba(37, 99, 235, 0.25);
            color: var(--neon-accent);
            transform: translateX(4px);
        }

        .table-item.active {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.1) 0%, rgba(13, 148, 136, 0.03) 100%);
            border-color: var(--neon-accent);
            color: var(--neon-accent);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
            font-weight: 600;
        }

        .row-count-badge {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            padding: 2px 6px;
            border-radius: 6px;
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-muted);
        }

        /* Workspace / Explorer Panel */
        .workspace {
            flex: 1;
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            overflow-y: auto;
            max-width: calc(100vw - 280px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-neon);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
        }

        .stat-card:hover {
            border-color: var(--border-neon-hover);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.06);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(37, 99, 235, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--neon-accent);
        }

        .stat-icon.cyan {
            background: rgba(13, 148, 136, 0.08);
            border-color: rgba(13, 148, 136, 0.2);
            color: var(--neon-cyan);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin-top: 4px;
        }

        /* Database View Card */
        .viewer-card {
            background: var(--bg-card);
            border: 1px solid var(--border-neon);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 450px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);
        }

        .viewer-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-neon);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.01);
        }

        .viewer-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 700;
        }

        .search-wrapper {
            position: relative;
            width: 300px;
        }

        .search-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
            font-size: 14px;
        }

        .search-input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            background: #f8fafc;
            border: 1px solid var(--border-neon);
            border-radius: 10px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
        }

        .search-input:focus {
            border-color: var(--neon-accent);
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.12);
            background: #ffffff;
        }

        /* Tabs Container */
        .viewer-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-neon);
            background: #f8fafc;
        }

        .tab-btn {
            padding: 14px 24px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: var(--neon-accent);
            background: rgba(37, 99, 235, 0.03);
        }

        .tab-btn.active {
            color: var(--neon-accent);
            border-bottom-color: var(--neon-accent);
            background: #ffffff;
        }

        /* Data & Schema Viewer Area */
        .viewer-body {
            padding: 24px;
            overflow-x: auto;
            flex: 1;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-neon);
        }

        table.explorer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        table.explorer-table th {
            background: #f8fafc;
            color: var(--text-muted);
            padding: 14px 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-neon);
            font-family: 'Plus Jakarta Sans', sans-serif;
            text-transform: uppercase;
            font-size: 11px;
        }

        table.explorer-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-neon);
            color: var(--text-main);
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        table.explorer-table tbody tr {
            transition: background 0.15s ease;
        }

        table.explorer-table tbody tr:hover {
            background: #f8fafc;
        }

        table.explorer-table tbody tr:last-child td {
            border-bottom: none;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
        }

        .badge-type {
            background: rgba(13, 148, 136, 0.08);
            color: var(--neon-cyan);
            border: 1px solid rgba(13, 148, 136, 0.15);
            padding: 2px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-null {
            color: var(--text-dim);
            font-style: italic;
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-pk {
            background: rgba(37, 99, 235, 0.08);
            color: var(--neon-accent);
            border: 1px solid rgba(37, 99, 235, 0.15);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Error States & Welcome Screen */
        .welcome-view {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px;
            text-align: center;
            color: var(--text-muted);
            gap: 16px;
        }

        .welcome-icon {
            font-size: 64px;
            background: linear-gradient(135deg, var(--neon-accent), var(--neon-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
            animation: bounce 3s infinite ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        /* Setup Instructions Container */
        .error-card {
            background: #fff5f5;
            border: 1px solid #fee2e2;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 750px;
            margin: 0 auto;
        }

        .error-title {
            color: #ef4444;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-desc {
            color: #b91c1c;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13.5px;
            background: #fff;
            padding: 16px;
            border-radius: 10px;
            border: 1px solid #fee2e2;
            word-break: break-all;
        }

        .setup-list {
            list-style-type: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            color: var(--text-main);
            text-align: left;
            margin-top: 10px;
        }

        .setup-list li {
            font-size: 14.5px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .setup-list li i {
            color: var(--neon-accent);
            margin-top: 4px;
        }

        .code-snippet {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            color: #b91c1c;
            border: 1px solid var(--border-neon);
        }

        /* Loading Indicator */
        .loader {
            display: none;
            text-align: center;
            padding: 60px;
            font-size: 16px;
            color: var(--text-muted);
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(37, 99, 235, 0.1);
            border-top-color: var(--neon-accent);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-section">
            <i class="fa-solid fa-layer-group logo-icon"></i>
            <div>
                <h1 class="logo-title">What If</h1>
                <p style="font-size: 10px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">PostgreSQL Explorer</p>
            </div>
        </div>
        
        <?php if ($pdo): ?>
            <div class="connection-badge">
                <i class="fa-solid fa-circle-check"></i>
                Connected to Postgres
            </div>
        <?php else: ?>
            <div class="connection-badge failed">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Connection Offline
            </div>
        <?php endif; ?>
    </header>

    <div class="app-container">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <h3 class="sidebar-title">Database System</h3>
                <div style="font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 4px;">
                    <div><strong>Host:</strong> <?php echo htmlspecialchars($db_host); ?></div>
                    <div><strong>Port:</strong> <?php echo htmlspecialchars($db_port); ?></div>
                    <div><strong>DB Name:</strong> <?php echo htmlspecialchars($db_name); ?></div>
                    <div><strong>User:</strong> <?php echo htmlspecialchars($db_user); ?></div>
                </div>
            </div>
            
            <hr style="border: none; border-top: 1px solid var(--border-neon);">
            
            <div>
                <h3 class="sidebar-title">Explore Tables</h3>
                <?php if ($pdo): 
                    $tables = ['users', 'restaurants', 'menu_items', 'orders', 'order_items', 'coupons', 'addresses', 'reviews'];
                    ?>
                    <ul class="table-list">
                        <?php foreach ($tables as $t): 
                            // Query count dynamically
                            $cnt = 0;
                            try {
                                $c_stmt = $pdo->query("SELECT COUNT(*) FROM " . db_escape_identifier($t));
                                $cnt = $c_stmt->fetchColumn();
                            } catch (Exception $e) {}
                            ?>
                            <li class="table-item" onclick="loadTableData('<?php echo $t; ?>')">
                                <span><i class="fa-solid fa-table table-icon"></i><?php echo $t; ?></span>
                                <span class="row-count-badge"><?php echo $cnt; ?> rows</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="font-size: 13px; color: var(--text-dim); font-style: italic;">No database connection available.</p>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="workspace">
            
            <?php if (!$pdo): ?>
                <!-- Connection Failure Error view -->
                <div class="error-card">
                    <h3 class="error-title"><i class="fa-solid fa-circle-xmark"></i> PostgreSQL Connection Failed</h3>
                    <p style="color: var(--text-main); font-size: 14.5px;">
                        Could not establish a connection to your local PostgreSQL server using the credentials parsed from 
                        <a href="file:///c:/Users/prem/Desktop/what_if01/index.php" style="color: var(--neon-accent); text-decoration: none; font-weight: bold;">index.php</a>.
                    </p>
                    <div class="error-desc"><?php echo htmlspecialchars($connection_error); ?></div>
                    
                    <hr style="border: none; border-top: 1px solid rgba(220, 38, 38, 0.15);">
                    
                    <h4 style="color: var(--text-main); font-weight: 600; font-size: 15px;"><i class="fa-solid fa-circle-info"></i> How to solve this connection error:</h4>
                    <ul class="setup-list">
                        <li>
                            <i class="fa-solid fa-circle-dot"></i>
                            <div>
                                <strong>Ensure PostgreSQL is running:</strong> Make sure you have installed PostgreSQL and the background service is running on Windows (Search for `services.msc` and verify that the <strong>postgresql</strong> service is started).
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-dot"></i>
                            <div>
                                <strong>Update your password in index.php:</strong> Open your <code class="code-snippet">index.php</code> file, locate <code class="code-snippet">DB_PASS</code> around line 20, and change <code class="code-snippet">'your_password_here'</code> to the actual password you set during the PostgreSQL installation.
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-dot"></i>
                            <div>
                                <strong>Create the database:</strong> Open pgAdmin or psql, and execute: <code class="code-snippet">CREATE DATABASE whatif_db;</code>
                            </div>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-dot"></i>
                            <div>
                                <strong>Enable XAMPP drivers:</strong> In <code class="code-snippet">php.ini</code>, make sure you have uncommented <code class="code-snippet">extension=pdo_pgsql</code> and <code class="code-snippet">extension=pgsql</code>, then restarted Apache.
                            </div>
                        </li>
                    </ul>
                    
                    <button onclick="window.location.reload()" style="align-self: flex-start; padding: 12px 24px; background: var(--neon-accent); color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; margin-top: 10px;">
                        <i class="fa-solid fa-rotate-right"></i> Retry Connection
                    </button>
                </div>
            <?php else: 
                // DB stats
                $pg_version = $pdo->query("SELECT version()")->fetchColumn();
                preg_match('/PostgreSQL (\d+\.\d+|\d+)/', $pg_version, $v_matches);
                $version = !empty($v_matches) ? $v_matches[0] : 'PostgreSQL';
                
                $db_size_query = $pdo->query("SELECT pg_size_pretty(pg_database_size('$db_name'))");
                $db_size = $db_size_query ? $db_size_query->fetchColumn() : 'N/A';
            ?>
                
                <!-- Quick Stats Info Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-database"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Database Name</span>
                            <span class="stat-value"><?php echo htmlspecialchars($db_name); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon cyan"><i class="fa-solid fa-microchip"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">DBMS Version</span>
                            <span class="stat-value"><?php echo htmlspecialchars($version); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-weight-hanging"></i></div>
                        <div class="stat-info">
                            <span class="stat-label">Database Size</span>
                            <span class="stat-value"><?php echo htmlspecialchars($db_size); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Interactive Viewer Card -->
                <div class="viewer-card">
                    
                    <!-- Welcome view when no table selected -->
                    <div id="welcome-view" class="welcome-view">
                        <i class="fa-solid fa-circle-nodes welcome-icon"></i>
                        <h2>PostgreSQL Data Explorer</h2>
                        <p style="max-width: 500px; line-height: 1.6; font-size: 14.5px;">
                            Establishment successful! Click on any database table in the sidebar list to inspect columns, schema types, constraints, and view the actual seeded rows.
                        </p>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loader-view" class="loader">
                        <div class="spinner"></div>
                        <p>Fetching table details from PostgreSQL...</p>
                    </div>

                    <!-- Live Content Panel -->
                    <div id="explorer-view" style="display: none; flex-direction: column; flex: 1;">
                        <div class="viewer-header">
                            <h3 class="viewer-title" id="selected-table-title">
                                <i class="fa-solid fa-table"></i> TABLE_NAME
                            </h3>
                            <div class="search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="table-search" class="search-input" placeholder="Search rows..." oninput="filterRows()">
                            </div>
                        </div>

                        <!-- Tab view -->
                        <div class="viewer-tabs">
                            <button id="tab-data" class="tab-btn active" onclick="switchTab('data')">
                                <i class="fa-solid fa-database" style="margin-right: 6px;"></i>Live Data Table (100 Rows Limit)
                            </button>
                            <button id="tab-schema" class="tab-btn" onclick="switchTab('schema')">
                                <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i>Schema & Constraints
                            </button>
                        </div>

                        <div class="viewer-body">
                            <!-- Tab: Live Data -->
                            <div id="panel-data" class="table-responsive">
                                <table class="explorer-table" id="data-table">
                                    <thead>
                                        <tr id="data-table-headers"></tr>
                                    </thead>
                                    <tbody id="data-table-body"></tbody>
                                </table>
                            </div>

                            <!-- Tab: Schema -->
                            <div id="panel-schema" class="table-responsive" style="display: none;">
                                <table class="explorer-table">
                                    <thead>
                                        <tr>
                                            <th>Column</th>
                                            <th>Data Type</th>
                                            <th>Nullable?</th>
                                            <th>Default Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="schema-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- JavaScript to Handle Dynamic Actions seamlessly -->
    <script>
        let currentTableData = {
            columns: [],
            rows: []
        };
        
        let activeTab = 'data';

        async function loadTableData(tableName) {
            // Highlight active sidebar item
            document.querySelectorAll('.table-item').forEach(item => {
                item.classList.remove('active');
                if (item.querySelector('span').textContent.includes(tableName)) {
                    item.classList.add('active');
                }
            });

            // Show loader
            document.getElementById('welcome-view').style.display = 'none';
            document.getElementById('explorer-view').style.display = 'none';
            document.getElementById('loader-view').style.display = 'flex';
            document.getElementById('table-search').value = '';

            try {
                const response = await fetch(`db-viewer.php?ajax_table=${tableName}`);
                const result = await response.json();
                
                if (result.success) {
                    currentTableData.columns = result.columns;
                    currentTableData.rows = result.rows;
                    
                    document.getElementById('selected-table-title').innerHTML = `<i class="fa-solid fa-table" style="color: var(--neon-accent);"></i> ${tableName.toUpperCase()}`;
                    
                    // Render Data Tab
                    renderDataTable(result.columns, result.rows);
                    
                    // Render Schema Tab
                    renderSchemaTable(result.columns);

                    // Show content
                    document.getElementById('loader-view').style.display = 'none';
                    document.getElementById('explorer-view').style.display = 'flex';
                    switchTab(activeTab);
                } else {
                    alert('Error: ' + (result.error || 'Failed to load table details'));
                }
            } catch (err) {
                console.error(err);
                alert('Communication error with the local database script.');
            }
        }

        function renderDataTable(columns, rows) {
            const headersRow = document.getElementById('data-table-headers');
            const body = document.getElementById('data-table-body');
            
            headersRow.innerHTML = '';
            body.innerHTML = '';

            if (columns.length === 0) return;

            // Render headers
            columns.forEach(col => {
                const th = document.createElement('th');
                th.textContent = col.column_name;
                headersRow.appendChild(th);
            });

            // Render rows
            if (rows.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.setAttribute('colspan', columns.length);
                td.style.textAlign = 'center';
                td.style.color = 'var(--text-dim)';
                td.style.fontStyle = 'italic';
                td.textContent = 'This table is currently empty.';
                tr.appendChild(td);
                body.appendChild(tr);
                return;
            }

            rows.forEach(row => {
                const tr = document.createElement('tr');
                columns.forEach(col => {
                    const td = document.createElement('td');
                    const value = row[col.column_name];
                    
                    if (value === null) {
                        td.innerHTML = '<span class="badge-null">NULL</span>';
                    } else if (typeof value === 'boolean') {
                        td.textContent = value ? 'TRUE' : 'FALSE';
                        td.style.color = value ? 'var(--neon-green)' : '#ef4444';
                        td.style.fontWeight = 'bold';
                    } else {
                        td.textContent = value;
                        if (col.column_name.includes('id') || col.column_name.includes('price') || col.column_name.includes('time') || col.column_name.includes('fee')) {
                            td.classList.add('mono');
                        }
                    }
                    tr.appendChild(td);
                });
                body.appendChild(tr);
            });
        }

        function renderSchemaTable(columns) {
            const body = document.getElementById('schema-table-body');
            body.innerHTML = '';

            columns.forEach(col => {
                const tr = document.createElement('tr');
                
                // Name
                const tdName = document.createElement('td');
                tdName.style.fontWeight = 'bold';
                tdName.innerHTML = `${col.column_name} ${col.column_name === 'id' ? '<span class="badge-pk">PRIMARY KEY</span>' : ''}`;
                tr.appendChild(tdName);

                // Type
                const tdType = document.createElement('td');
                tdType.innerHTML = `<span class="badge-type">${col.data_type}</span>`;
                tr.appendChild(tdType);

                // Nullable
                const tdNull = document.createElement('td');
                tdNull.textContent = col.is_nullable;
                tdNull.style.color = col.is_nullable === 'YES' ? 'var(--text-muted)' : 'var(--neon-accent)';
                tdNull.style.fontWeight = 'bold';
                tr.appendChild(tdNull);

                // Default
                const tdDef = document.createElement('td');
                tdDef.classList.add('mono');
                tdDef.textContent = col.column_default !== null ? col.column_default : '-';
                tr.appendChild(tdDef);

                body.appendChild(tr);
            });
        }

        function switchTab(tabName) {
            activeTab = tabName;
            
            // Buttons toggle
            document.getElementById('tab-data').classList.remove('active');
            document.getElementById('tab-schema').classList.remove('active');
            document.getElementById(`tab-${tabName}`).classList.add('active');

            // Panels toggle
            document.getElementById('panel-data').style.display = tabName === 'data' ? 'block' : 'none';
            document.getElementById('panel-schema').style.display = tabName === 'schema' ? 'block' : 'none';
        }

        function filterRows() {
            const query = document.getElementById('table-search').value.toLowerCase();
            const body = document.getElementById('data-table-body');
            body.innerHTML = '';

            const columns = currentTableData.columns;
            const rows = currentTableData.rows;

            const filteredRows = rows.filter(row => {
                return Object.values(row).some(val => 
                    val !== null && String(val).toLowerCase().includes(query)
                );
            });

            if (filteredRows.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.setAttribute('colspan', columns.length);
                td.style.textAlign = 'center';
                td.style.color = 'var(--text-dim)';
                td.style.fontStyle = 'italic';
                td.textContent = 'No matching rows found.';
                tr.appendChild(td);
                body.appendChild(tr);
                return;
            }

            filteredRows.forEach(row => {
                const tr = document.createElement('tr');
                columns.forEach(col => {
                    const td = document.createElement('td');
                    const value = row[col.column_name];
                    
                    if (value === null) {
                        td.innerHTML = '<span class="badge-null">NULL</span>';
                    } else if (typeof value === 'boolean') {
                        td.textContent = value ? 'TRUE' : 'FALSE';
                        td.style.color = value ? 'var(--neon-green)' : '#ef4444';
                        td.style.fontWeight = 'bold';
                    } else {
                        td.textContent = value;
                        if (col.column_name.includes('id') || col.column_name.includes('price') || col.column_name.includes('time') || col.column_name.includes('fee')) {
                            td.classList.add('mono');
                        }
                    }
                    tr.appendChild(td);
                });
                body.appendChild(tr);
            });
        }
    </script>
</body>
</html>
