<?php
/**
 * Admin Reports: Export Orders CSV
 */

require_once '../../includes/export-functions.php';

require_admin_export_auth();

$pdo = get_db_connection();
$rows = [];

if ($pdo) {
    try {
        $sql = "SELECT o.id AS order_id, u.name AS customer_name, u.email AS customer_email, 
                       r.name AS restaurant_name, o.total AS order_amount, 
                       COALESCE(p.payment_method, 'COD') AS payment_method, 
                       o.status AS order_status, o.created_at AS order_date
                FROM orders o
                JOIN users u ON o.user_id = u.id
                JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN payments p ON o.id = p.order_id
                WHERE 1=1";
        $params = [];
        
        // Filters
        $start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
        $restaurant_id = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']) : 0;
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $payment_method = isset($_GET['payment_method']) ? trim($_GET['payment_method']) : '';
        
        if (!empty($start_date)) {
            $sql .= " AND o.created_at >= :start_date";
            $params[':start_date'] = $start_date . ' 00:00:00';
        }
        if (!empty($end_date)) {
            $sql .= " AND o.created_at <= :end_date";
            $params[':end_date'] = $end_date . ' 23:59:59';
        }
        if ($restaurant_id > 0) {
            $sql .= " AND o.restaurant_id = :restaurant_id";
            $params[':restaurant_id'] = $restaurant_id;
        }
        if (!empty($status)) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $status;
        }
        if (!empty($payment_method)) {
            if (strtolower($payment_method) === 'cod') {
                $sql .= " AND (p.payment_method ILIKE 'cod' OR p.payment_method IS NULL)";
            } else {
                $sql .= " AND p.payment_method ILIKE :payment_method";
                $params[':payment_method'] = $payment_method;
            }
        }
        
        $sql .= " ORDER BY o.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        // Continue
    }
}

// Log to audit logs
audit_log_export('Orders Export');

$headers = ['Order ID', 'Customer Name', 'Customer Email', 'Restaurant Name', 'Order Amount', 'Payment Method', 'Order Status', 'Order Date'];
$filename = 'orders_' . date('Y_m_d') . '.csv';

stream_csv_download($filename, $headers, $rows);
