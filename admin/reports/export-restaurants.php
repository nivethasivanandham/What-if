<?php
/**
 * Admin Reports: Export Restaurants CSV
 */

require_once '../../includes/export-functions.php';

require_admin_export_auth();

$pdo = get_db_connection();
$rows = [];

if ($pdo) {
    try {
        $sql = "SELECT r.id AS restaurant_id, r.name AS restaurant_name, 
                       COALESCE(u.name, 'N/A') AS owner_name, 
                       COALESCE(u.email, 'N/A') AS email, 
                       COALESCE(u.phone, 'N/A') AS phone, 
                       COALESCE(r.address, 'N/A') AS address,
                       COUNT(o.id) AS total_orders, 
                       r.rating, 
                       CASE WHEN r.is_open THEN 'Open' ELSE 'Closed' END AS status
                FROM restaurants r
                LEFT JOIN users u ON r.owner_id = u.id
                LEFT JOIN orders o ON r.id = o.restaurant_id
                GROUP BY r.id, u.name, u.email, u.phone
                ORDER BY r.id ASC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        // Continue
    }
}

// Log to audit logs
audit_log_export('Restaurants Export');

$headers = ['Restaurant ID', 'Restaurant Name', 'Owner Name', 'Email', 'Phone', 'Address', 'Total Orders', 'Rating', 'Status'];
$filename = 'restaurants_' . date('Y_m_d') . '.csv';

stream_csv_download($filename, $headers, $rows);
