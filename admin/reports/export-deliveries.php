<?php
/**
 * Admin Reports: Export Deliveries CSV
 */

require_once '../../includes/export-functions.php';

require_admin_export_auth();

$pdo = get_db_connection();
$rows = [];

if ($pdo) {
    try {
        $sql = "SELECT d.id AS delivery_id, d.order_id, d.delivery_partner, 
                       u.name AS customer_name, r.name AS restaurant_name, 
                       d.status AS delivery_status, d.assigned_at AS assigned_date, 
                       d.delivered_at AS delivered_date
                FROM deliveries d
                JOIN orders o ON d.order_id = o.id
                JOIN users u ON o.user_id = u.id
                JOIN restaurants r ON o.restaurant_id = r.id
                ORDER BY d.id DESC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        // Continue
    }
}

// Log to audit logs
audit_log_export('Deliveries Export');

$headers = ['Delivery ID', 'Order ID', 'Delivery Partner', 'Customer Name', 'Restaurant Name', 'Delivery Status', 'Assigned Date', 'Delivered Date'];
$filename = 'deliveries_' . date('Y_m_d') . '.csv';

stream_csv_download($filename, $headers, $rows);
