<?php
/**
 * Admin Reports: Export Menu Items CSV
 */

require_once '../../includes/export-functions.php';

require_admin_export_auth();

$pdo = get_db_connection();
$rows = [];

if ($pdo) {
    try {
        $sql = "SELECT m.id AS food_id, r.name AS restaurant_name, m.name AS food_name, 
                       m.category, m.price, 
                       CASE WHEN m.is_available THEN 'Available' ELSE 'Unavailable' END AS availability_status, 
                       m.created_at AS created_date
                FROM menu_items m
                JOIN restaurants r ON m.restaurant_id = r.id
                ORDER BY r.name ASC, m.id ASC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        // Continue
    }
}

// Log to audit logs
audit_log_export('Menu Items Export');

$headers = ['Food ID', 'Restaurant Name', 'Food Name', 'Category', 'Price', 'Availability Status', 'Created Date'];
$filename = 'menu_items_' . date('Y_m_d') . '.csv';

stream_csv_download($filename, $headers, $rows);
