<?php
/**
 * Customer Support: Create Ticket Page
 */

require_once '../../includes/support-functions.php';
require_once '../../includes/mailer.php';

require_customer_auth();

$pdo = get_db_connection();
// Ensure migrations run
run_migrations($pdo);

$user_id = $_SESSION['user']['id'];
$success_msg = '';
$error_msg = '';

// Fetch customer's recent orders to link to the ticket
$orders = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT o.id, o.created_at, o.total, r.name AS rest_name 
                               FROM orders o 
                               JOIN restaurants r ON o.restaurant_id = r.id 
                               WHERE o.user_id = :user_id 
                               ORDER BY o.id DESC LIMIT 10");
        $stmt->execute([':user_id' => $user_id]);
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        // Continue without orders list
    }
}

// Handle ticket creation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        $error_msg = 'Security token invalid. Please retry.';
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $priority = trim($_POST['priority'] ?? 'Medium');
        $message = trim($_POST['message'] ?? '');
        $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : null;
        
        if (empty($subject) || empty($category) || empty($message)) {
            $error_msg = 'Please complete all required fields.';
        } else {
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO support_tickets (customer_id, order_id, subject, category, priority, message) 
                                           VALUES (:customer_id, :order_id, :subject, :category, :priority, :message) 
                                           RETURNING id");
                    $stmt->execute([
                        ':customer_id' => $user_id,
                        ':order_id' => $order_id,
                        ':subject' => $subject,
                        ':category' => $category,
                        ':priority' => $priority,
                        ':message' => $message
                    ]);
                    $ticket_id = $stmt->fetchColumn();
                    
                    // Trigger email notification to Admin
                    $admin_subject = "New Support Ticket #$ticket_id: $subject";
                    $admin_body = "
                        <h2>New Support Ticket #$ticket_id Created</h2>
                        <p><strong>Customer:</strong> " . sanitize_html($_SESSION['user']['name']) . " (" . sanitize_html($_SESSION['user']['email']) . ")</p>
                        <p><strong>Category:</strong> " . sanitize_html($category) . "</p>
                        <p><strong>Priority:</strong> " . sanitize_html($priority) . "</p>
                        " . ($order_id ? "<p><strong>Linked Order:</strong> #ZMT$order_id</p>" : "") . "
                        <p><strong>Message:</strong></p>
                        <blockquote style='border-left: 3px solid #e23744; padding-left: 12px; font-style: italic;'>
                            " . nl2br(sanitize_html($message)) . "
                        </blockquote>
                        <p><a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/admin/support/ticket-details.php?id=$ticket_id' style='display:inline-block; padding: 10px 18px; background: #e23744; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;'>Manage Ticket Dashboard</a></p>
                    ";
                    send_support_email(ADMIN_EMAIL, $admin_subject, $admin_body);
                    
                    header("Location: my-tickets.php?created=1");
                    exit;
                } catch (Exception $e) {
                    $error_msg = 'Database insertion failed: ' . $e->getMessage();
                }
            } else {
                $error_msg = 'Database connection offline.';
            }
        }
    }
}

$csrf_token = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket – What If</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --red: #e23744;
            --red-dark: #c0392b;
            --red-light: #fff0f1;
            --bg: #fafbfc;
            --text: #1c1c1c;
            --muted: #6b7280;
            --border: #e5e7eb;
            --card-bg: #ffffff;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 30px;
        }
        .back-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--muted);
            transition: 0.2s;
            text-decoration: none;
        }
        .back-btn:hover {
            color: var(--red);
        }
        h1 {
            font-family: 'Nunito', sans-serif;
            font-weight: 900;
            font-size: 24px;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-error {
            background: #fff0f1;
            border: 1px solid #ffccd0;
            color: var(--red);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text);
        }
        .form-input, select, textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: 0.25s;
            background: #fcfcfc;
        }
        .form-input:focus, select:focus, textarea:focus {
            border-color: var(--red);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(226, 55, 68, 0.08);
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: var(--red-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(226, 55, 68, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="my-tickets.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1>Create Support Ticket</h1>
        </div>

        <div class="card">
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-input" placeholder="Summarize your issue..." required>
                </div>

                <div class="form-group font-style-custom">
                    <label class="form-label">Category</label>
                    <select name="category" required>
                        <option value="" disabled selected>Select category...</option>
                        <option value="Order Issue">Order Issue</option>
                        <option value="Payment Issue">Payment Issue</option>
                        <option value="Refund Request">Refund Request</option>
                        <option value="Restaurant Complaint">Restaurant Complaint</option>
                        <option value="Delivery Problem">Delivery Problem</option>
                        <option value="Account Issue">Account Issue</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Link Recent Order (Optional)</label>
                    <select name="order_id">
                        <option value="">-- No order link --</option>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?php echo $order['id']; ?>">
                                Order #ZMT<?php echo $order['id']; ?> from <?php echo sanitize_html($order['rest_name']); ?> (₹<?php echo intval($order['total']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Detail Message</label>
                    <textarea name="message" placeholder="Provide detailed information regarding the problem..." required></textarea>
                </div>

                <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Submit Ticket</button>
            </form>
        </div>
    </div>
</body>
</html>
