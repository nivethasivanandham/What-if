<?php
/**
 * PHPMailer SMTP Configuration & Dispatcher
 * Integrates PHPMailer SMTP. Falls back gracefully to appending visual email payloads to mail_log.html
 */

// Manually require PHPMailer source files
$base_dir = __DIR__ . '/PHPMailer';
$has_phpmailer = false;

if (file_exists("$base_dir/Exception.php") && file_exists("$base_dir/PHPMailer.php") && file_exists("$base_dir/SMTP.php")) {
    require_once "$base_dir/Exception.php";
    require_once "$base_dir/PHPMailer.php";
    require_once "$base_dir/SMTP.php";
    $has_phpmailer = true;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// SMTP configuration constants
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_AUTH', true);
define('SMTP_USER', 'nivetha.s.1230@gmail.com'); // Put actual SMTP email here
define('SMTP_PASS', 'fwfo amlf vneq evti');  // Put actual SMTP password here
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'support@whatif.com');
define('FROM_NAME', 'What If Support');
define('ADMIN_EMAIL', 'nivetha.s.1230@gmail.com');

/**
 * Dispatches HTML email. Falls back to writing to local file on failure.
 */
function send_support_email($to_email, $subject, $message_html) {
    global $has_phpmailer;
    $sent = false;
    $error_info = '';

    if ($has_phpmailer && SMTP_USER !== 'placeholder@gmail.com') {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = SMTP_AUTH;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($to_email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message_html;

            $mail->send();
            $sent = true;
        } catch (Exception $e) {
            $error_info = $mail->ErrorInfo;
        }
    } else {
        $error_info = 'PHPMailer files missing or SMTP placeholder credentials active.';
    }

    if (!$sent) {
        log_email_locally($to_email, $subject, $message_html, $error_info);
    }
    return $sent;
}

/**
 * Appends the email content to a local HTML log file for development and verification.
 */
function log_email_locally($to_email, $subject, $message_html, $reason = '') {
    $log_file = dirname(__DIR__) . '/mail_log.html';
    $timestamp = date('Y-m-d H:i:s');
    
    $entry = "
    <div style='border: 1px solid #e23744; border-radius: 12px; font-family: sans-serif; margin: 20px auto; max-width: 600px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
        <div style='background: #e23744; color: #fff; padding: 14px 20px;'>
            <h3 style='margin: 0; font-size: 16px;'>📧 Email Notification Dispatched (Local Mock Log)</h3>
            <span style='font-size: 11px; opacity: 0.9;'>Logged at: $timestamp | Status: Mocked (Reason: $reason)</span>
        </div>
        <div style='padding: 20px; background: #fff; color: #333;'>
            <p style='margin: 0 0 8px 0;'><strong>To:</strong> " . htmlspecialchars($to_email) . "</p>
            <p style='margin: 0 0 16px 0;'><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 16px 0;'>
            <div style='border: 1px solid #f0f0f0; padding: 16px; border-radius: 8px; background: #fafafa;'>
                $message_html
            </div>
        </div>
    </div>
    \n";

    if (!file_exists($log_file)) {
        $header = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>What If Mail Logs</title></head><body style='background:#f7f9fa; padding: 20px;'>\n";
        file_put_contents($log_file, $header);
    }
    file_put_contents($log_file, $entry, FILE_APPEND);
}
