<?php
/**
 * Admin Reports: Export Customers CSV
 */

require_once '../../includes/export-functions.php';

require_admin_export_auth();

$pdo = get_db_connection();
$rows = [];

if ($pdo) {
    try {
        $sql = "SELECT u.id AS customer_id, u.name, u.email, u.phone, 
                       COALESCE(u.address, '') AS address, u.created_at AS registration_date,
                       COUNT(o.id) AS total_orders
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE u.role = 'Customer'
                GROUP BY u.id
                ORDER BY u.id ASC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        // Continue
    }
}

// Log to audit logs
audit_log_export('Customers Export');

$headers = ['Customer ID', 'Name', 'Email', 'Phone', 'Address', 'Registration Date', 'Total Orders'];
$filename = 'customers_' . date('Y_m_d') . '.csv';

stream_csv_download($filename, $headers, $rows);
