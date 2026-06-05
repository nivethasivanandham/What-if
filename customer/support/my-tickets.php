<?php
/**
 * Customer Support: My Tickets List
 */

require_once '../../includes/support-functions.php';

require_customer_auth();

$pdo = get_db_connection();
$user_id = $_SESSION['user']['id'];
$tickets = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE customer_id = :customer_id ORDER BY id DESC");
        $stmt->execute([':customer_id' => $user_id]);
        $tickets = $stmt->fetchAll();
    } catch (Exception $e) {
        // Continue with empty list
    }
}

$created_success = isset($_GET['created']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets – What If</title>
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
            --green: #3d9b6e;
            --green-light: #e6f6ee;
            --gold: #f59e0b;
            --blue: #2563eb;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header-title-section {
            display: flex;
            align-items: center;
            gap: 16px;
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
            font-size: 26px;
        }
        .btn-new {
            padding: 10px 20px;
            background: var(--red);
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-new:hover {
            background: var(--red-dark);
            box-shadow: 0 4px 14px rgba(226, 55, 68, 0.25);
        }
        .alert {
            padding: 12px 16px;
            background: var(--green-light);
            border: 1px solid rgba(61, 155, 110, 0.2);
            color: var(--green);
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .ticket-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .ticket-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            text-decoration: none;
            color: inherit;
        }
        .ticket-card:hover {
            transform: translateY(-2px);
            border-color: var(--red);
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        }
        .ticket-info {
            flex: 1;
            padding-right: 20px;
        }
        .ticket-meta {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 8px;
        }
        .ticket-id {
            font-weight: 800;
            font-size: 13px;
            color: var(--red);
        }
        .ticket-category {
            font-size: 12px;
            background: #f1f3f5;
            padding: 3px 8px;
            border-radius: 5px;
            font-weight: 700;
            color: var(--muted);
        }
        .ticket-subject {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
            color: var(--text);
        }
        .ticket-date {
            font-size: 12px;
            color: var(--muted);
        }
        .ticket-badges {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
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
        
        .priority {
            font-size: 11px;
            font-weight: 700;
        }
        .priority-high { color: var(--red); }
        .priority-medium { color: var(--gold); }
        .priority-low { color: var(--blue); }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 18px;
            color: var(--muted);
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--border);
        }
        .empty-state h3 {
            color: var(--text);
            margin-bottom: 8px;
            font-size: 18px;
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title-section">
                <a href="../../index.php" class="back-btn"><i class="fas fa-home"></i></a>
                <h1>Support Center</h1>
            </div>
            <a href="create-ticket.php" class="btn-new"><i class="fas fa-plus"></i> Create Ticket</a>
        </div>

        <?php if ($created_success): ?>
            <div class="alert"><i class="fas fa-check-circle"></i> Ticket submitted successfully! A support agent will review it shortly.</div>
        <?php endif; ?>

        <?php if (count($tickets) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-headset"></i>
                <h3>No Tickets Found</h3>
                <p>Have a question or problem with your order? Open a support ticket above.</p>
            </div>
        <?php else: ?>
            <div class="ticket-list">
                <?php foreach ($tickets as $t): 
                    $status_class = strtolower(str_replace(' ', '', $t['status']));
                    $priority_class = strtolower($t['priority']);
                    ?>
                    <a href="ticket-details.php?id=<?php echo $t['id']; ?>" class="ticket-card">
                        <div class="ticket-info">
                            <div class="ticket-meta">
                                <span class="ticket-id">#TKT-<?php echo $t['id']; ?></span>
                                <span class="ticket-category"><?php echo sanitize_html($t['category']); ?></span>
                            </div>
                            <div class="ticket-subject"><?php echo sanitize_html($t['subject']); ?></div>
                            <div class="ticket-date"><i class="far fa-clock"></i> Opened on <?php echo date('M d, Y · h:i A', strtotime($t['created_at'])); ?></div>
                        </div>
                        <div class="ticket-badges">
                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo sanitize_html($t['status']); ?></span>
                            <span class="priority priority-<?php echo $priority_class; ?>">
                                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i><?php echo sanitize_html($t['priority']); ?> Priority
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
