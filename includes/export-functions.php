<?php
/**
 * Export and Audit Logging Helpers
 * Handles role authorization check, export logging, and standard CSV streaming.
 */

require_once __DIR__ . '/support-functions.php';

/**
 * Validates that the active user session is an Admin, otherwise sends 403 Forbidden.
 */
function require_admin_export_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
        header('HTTP/1.1 403 Forbidden');
        echo "<h1>403 Forbidden</h1><p>You are not authorized to perform exports.</p>";
        exit;
    }
}

/**
 * Inserts a log entry for the export operation in the database.
 */
function audit_log_export($export_type) {
    $pdo = get_db_connection();
    if (!$pdo) return;
    
    $admin_id = $_SESSION['user']['id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO export_logs (admin_id, export_type, ip_address) VALUES (:admin_id, :export_type, :ip_address)");
        $stmt->execute([
            ':admin_id' => $admin_id,
            ':export_type' => $export_type,
            ':ip_address' => $ip
        ]);
    } catch (Exception $e) {
        // Silently skip if insert fails during export logging
    }
}

/**
 * Outputs a list of data rows into a downloadable CSV attachment.
 */
function stream_csv_download($filename, $headers, $rows) {
    // Set headers to trigger file download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // Output column headers
    fputcsv($output, $headers);
    
    // Output data rows
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
