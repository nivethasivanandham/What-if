<?php
/**
 * Admin Support: Ticket Details, Reply and Status Update
 */

require_once '../../includes/support-functions.php';
require_once '../../includes/mailer.php';

require_admin_auth();

$pdo = get_db_connection();
$admin_id = $_SESSION['user']['id'];

$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ticket = null;
$replies = [];
$error_msg = '';
$success_msg = '';

if ($pdo && $ticket_id > 0) {
    try {
        // Fetch ticket details
        $stmt = $pdo->prepare("SELECT t.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone, o.total AS order_total, r.name AS rest_name 
                               FROM support_tickets t 
                               JOIN users u ON t.customer_id = u.id 
                               LEFT JOIN orders o ON t.order_id = o.id 
                               LEFT JOIN restaurants r ON o.restaurant_id = r.id 
                               WHERE t.id = :id");
        $stmt->execute([':id' => $ticket_id]);
        $ticket = $stmt->fetch();
        
        if (!$ticket) {
            header("Location: tickets.php");
            exit;
        }
        
        // Fetch replies
        $stmt_rep = $pdo->prepare("SELECT r.*, u.name AS user_name, u.role AS user_role 
                                   FROM support_replies r 
                                   LEFT JOIN users u ON r.user_id = u.id OR r.admin_id = u.id 
                                   WHERE r.ticket_id = :ticket_id 
                                   ORDER BY r.created_at ASC");
        $stmt_rep->execute([':ticket_id' => $ticket_id]);
        $replies = $stmt_rep->fetchAll();
    } catch (Exception $e) {
        $error_msg = 'Database retrieval failed: ' . $e->getMessage();
    }
} else {
    header("Location: tickets.php");
    exit;
}

// Handle reply submission or status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        $error_msg = 'Security token invalid.';
    } else {
        $action_post = $_POST['action_post'] ?? '';
        
        if ($action_post === 'update_status') {
            $new_status = trim($_POST['status'] ?? '');
            $allowed_statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
            
            if (in_array($new_status, $allowed_statuses)) {
                if ($pdo) {
                    try {
                        $pdo->beginTransaction();
                        
                        $stmt_upd = $pdo->prepare("UPDATE support_tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
                        $stmt_upd->execute([':status' => $new_status, ':id' => $ticket_id]);
                        
                        $pdo->commit();
                        $ticket['status'] = $new_status; // Update local state
                        $success_msg = 'Ticket status updated successfully!';
                        
                        // If status changed to Resolved, send resolution notification to customer
                        if ($new_status === 'Resolved') {
                            $cust_subject = "Support Ticket #$ticket_id Resolved";
                            $cust_body = "
                                <h2>Your Support Ticket #$ticket_id is Resolved</h2>
                                <p>Dear " . sanitize_html($ticket['customer_name']) . ",</p>
                                <p>Our support agents have marked your ticket regarding <strong>\"" . sanitize_html($ticket['subject']) . "\"</strong> as <strong>Resolved</strong>.</p>
                                <p>We hope this answers your concern. If you have any further questions, please do not hesitate to contact us again.</p>
                                <p>Thank you for choosing What If!</p>
                            ";
                            send_support_email($ticket['customer_email'], $cust_subject, $cust_body);
                        }
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error_msg = 'Failed to update status: ' . $e->getMessage();
                    }
                }
            } else {
                $error_msg = 'Invalid status selected.';
            }
        } elseif ($action_post === 'send_reply') {
            $message = trim($_POST['message'] ?? '');
            if (empty($message)) {
                $error_msg = 'Reply message cannot be empty.';
            } else {
                if ($pdo) {
                    try {
                        $pdo->beginTransaction();
                        
                        // Insert reply
                        $ins = $pdo->prepare("INSERT INTO support_replies (ticket_id, admin_id, message) VALUES (:ticket_id, :admin_id, :message)");
                        $ins->execute([
                            ':ticket_id' => $ticket_id,
                            ':admin_id' => $admin_id,
                            ':message' => $message
                        ]);
                        
                        // Update ticket status to In Progress automatically when admin replies, if it was Open
                        $status_to_save = ($ticket['status'] === 'Open') ? 'In Progress' : $ticket['status'];
                        $upd = $pdo->prepare("UPDATE support_tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
                        $upd->execute([
                            ':status' => $status_to_save,
                            ':id' => $ticket_id
                        ]);
                        
                        $pdo->commit();
                        
                        // Trigger email notification to Customer
                        $cust_subject = "New Agent Reply on Ticket #$ticket_id";
                        $cust_body = "
                            <h2>New Agent Message on Support Ticket #$ticket_id</h2>
                            <p>Dear " . sanitize_html($ticket['customer_name']) . ",</p>
                            <p>Our support team has replied to your ticket: <strong>\"" . sanitize_html($ticket['subject']) . "\"</strong>.</p>
                            <p><strong>Message:</strong></p>
                            <blockquote style='border-left: 3px solid #e23744; padding-left: 12px; font-style: italic;'>
                                " . nl2br(sanitize_html($message)) . "
                            </blockquote>
                            <p><a href='http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/customer/support/ticket-details.php?id=$ticket_id' style='display:inline-block; padding: 10px 18px; background: #e23744; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;'>View Support Conversation Thread</a></p>
                        ";
                        send_support_email($ticket['customer_email'], $cust_subject, $cust_body);
                        
                        header("Location: ticket-details.php?id=$ticket_id");
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error_msg = 'Failed to submit reply: ' . $e->getMessage();
                    }
                }
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
    <title>Ticket #<?php echo $ticket_id; ?> Review – What If Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --red: #e23744;
            --red-dark: #c0392b;
            --red-light: #fff0f1;
            --bg: #f3f4f6;
            --text: #111827;
            --muted: #4b5563;
            --border: #e5e7eb;
            --card-bg: #ffffff;
            --green: #10b981;
            --green-light: #ecfdf5;
            --gold: #f59e0b;
            --blue: #3b82f6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            padding: 40px;
        }
        .container {
            max-width: 1000px;
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
            flex: 1;
        }
        
        .badge {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .badge-open { background: var(--green-light); color: var(--green); }
        .badge-inprogress { background: #fef3c7; color: var(--gold); }
        .badge-resolved { background: #e0f2fe; color: var(--blue); }
        .badge-closed { background: #f3f4f6; color: var(--muted); }

        .layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
        }
        @media (max-width: 768px) {
            .layout { grid-template-columns: 1fr; }
        }
        
        .chat-section {
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            min-height: 480px;
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .sidebar-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .sidebar-title {
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 6px;
        }
        
        .alert-error {
            background: #fff0f1;
            border: 1px solid #ffccd0;
            color: var(--red);
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: var(--green-light);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--green);
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        /* Chat Thread layout */
        .chat-thread {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding-right: 4px;
        }
        
        .chat-msg {
            display: flex;
            flex-direction: column;
            max-width: 80%;
        }
        
        .chat-msg.customer {
            align-self: flex-start;
            align-items: flex-start;
        }
        
        .chat-msg.admin {
            align-self: flex-end;
            align-items: flex-end;
        }
        
        .msg-meta {
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .msg-meta strong {
            color: var(--text);
        }
        
        .msg-bubble {
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }
        
        .customer .msg-bubble {
            background: #f1f3f5;
            color: var(--text);
            border-bottom-left-radius: 2px;
            border: 1px solid var(--border);
        }
        
        .admin .msg-bubble {
            background: var(--red);
            color: #fff;
            border-bottom-right-radius: 2px;
        }
        
        .reply-form {
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        
        .reply-input-wrap {
            display: flex;
            gap: 12px;
        }
        
        .reply-textarea {
            flex: 1;
            padding: 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            resize: none;
            height: 48px;
            transition: 0.2s;
        }
        
        .reply-textarea:focus {
            border-color: var(--red);
            height: 100px;
        }
        
        .btn-send {
            padding: 0 20px;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-send:hover {
            background: var(--red-dark);
        }
        
        .btn-update {
            width: 100%;
            padding: 10px;
            background: var(--text);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            font-size: 13px;
        }
        .btn-update:hover {
            background: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="tickets.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1>Manage Ticket #TKT-<?php echo $ticket['id']; ?></h1>
            <span class="badge badge-<?php echo strtolower(str_replace(' ', '', $ticket['status'])); ?>"><?php echo sanitize_html($ticket['status']); ?></span>
        </div>

        <div class="layout">
            <div class="chat-section">
                <?php if (!empty($error_msg)): ?>
                    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_msg)): ?>
                    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div>
                <?php endif; ?>

                <div class="chat-thread">
                    <!-- Initial Ticket Description -->
                    <div class="chat-msg customer">
                        <div class="msg-meta"><strong><?php echo sanitize_html($ticket['customer_name']); ?> (Customer)</strong> · <?php echo date('M d, Y · h:i A', strtotime($ticket['created_at'])); ?></div>
                        <div class="msg-bubble"><?php echo nl2br(sanitize_html($ticket['message'])); ?></div>
                    </div>

                    <!-- Conversation Logs -->
                    <?php foreach ($replies as $rep): 
                        $is_admin = (!empty($rep['admin_id']) || $rep['user_role'] === 'Admin');
                        $sender_class = $is_admin ? 'admin' : 'customer';
                        $sender_name = $is_admin ? 'What If Agent (You)' : sanitize_html($ticket['customer_name']) . ' (Customer)';
                        ?>
                        <div class="chat-msg <?php echo $sender_class; ?>">
                            <div class="msg-meta"><strong><?php echo $sender_name; ?></strong> · <?php echo date('M d, Y · h:i A', strtotime($rep['created_at'])); ?></div>
                            <div class="msg-bubble"><?php echo nl2br(sanitize_html($rep['message'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($ticket['status'] !== 'Closed'): ?>
                    <div class="reply-form">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action_post" value="send_reply">
                            <div class="reply-input-wrap">
                                <textarea name="message" class="reply-textarea" placeholder="Type your reply to customer..." required></textarea>
                                <button type="submit" class="btn-send"><i class="fas fa-reply"></i> Send Reply</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: var(--muted); font-size: 13.5px; border-top: 1px solid var(--border); padding-top: 15px;">
                        <i class="fas fa-lock"></i> This ticket is closed. Change the status in the panel to enable replies.
                    </div>
                <?php endif; ?>
            </div>

            <div class="sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-title">Manage Status</div>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action_post" value="update_status">
                        <select name="status" style="background: #fff;">
                            <option value="Open" <?php echo $ticket['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                            <option value="In Progress" <?php echo $ticket['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Resolved" <?php echo $ticket['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="Closed" <?php echo $ticket['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                        <button type="submit" class="btn-update">Update Status</button>
                    </form>
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-title">Customer Profile</div>
                    <div style="font-size: 13.5px; display: flex; flex-direction: column; gap: 8px;">
                        <div><strong>Name:</strong><div style="color:var(--muted); margin-top:2px;"><?php echo sanitize_html($ticket['customer_name']); ?></div></div>
                        <div><strong>Email:</strong><div style="color:var(--muted); margin-top:2px; font-size:12px; word-break:break-all;"><?php echo sanitize_html($ticket['customer_email']); ?></div></div>
                        <div><strong>Phone:</strong><div style="color:var(--muted); margin-top:2px;"><?php echo sanitize_html($ticket['customer_phone'] ?? 'N/A'); ?></div></div>
                    </div>
                </div>

                <div class="sidebar-card">
                    <div class="sidebar-title">Ticket Information</div>
                    <div style="font-size: 13.5px; display: flex; flex-direction: column; gap: 10px;">
                        <div><strong>Category:</strong><div style="margin-top:2px; color:var(--muted);"><?php echo sanitize_html($ticket['category']); ?></div></div>
                        <div><strong>Priority:</strong><div style="margin-top:2px; font-weight:700;" class="priority-<?php echo strtolower($ticket['priority']); ?>"><?php echo sanitize_html($ticket['priority']); ?></div></div>
                        <div><strong>Opened:</strong><div style="margin-top:2px; color:var(--muted);"><?php echo date('M d, Y · h:i A', strtotime($ticket['created_at'])); ?></div></div>
                    </div>
                </div>

                <?php if ($ticket['order_id']): ?>
                    <div class="sidebar-card">
                        <div class="sidebar-title">Linked Order</div>
                        <div style="font-size: 13.5px; display: flex; flex-direction: column; gap: 8px;">
                            <div>Order <strong>#ZMT<?php echo $ticket['order_id']; ?></strong></div>
                            <div>Kitchen: <span style="color:var(--muted);"><?php echo sanitize_html($ticket['rest_name']); ?></span></div>
                            <div>Amount: <strong>₹<?php echo intval($ticket['order_total']); ?></strong></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
