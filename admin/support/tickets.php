<?php
/**
 * Admin Support: Manage Tickets List
 */

require_once '../../includes/support-functions.php';

require_admin_auth();

$pdo = get_db_connection();
$tickets = [];

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$priority_filter = isset($_GET['priority']) ? trim($_GET['priority']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($pdo) {
    try {
        $sql = "SELECT t.*, u.name AS customer_name, u.email AS customer_email 
                FROM support_tickets t 
                JOIN users u ON t.customer_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($status_filter)) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $status_filter;
        }
        if (!empty($priority_filter)) {
            $sql .= " AND t.priority = :priority";
            $params[':priority'] = $priority_filter;
        }
        if (!empty($category_filter)) {
            $sql .= " AND t.category = :category";
            $params[':category'] = $category_filter;
        }
        if (!empty($search_query)) {
            $sql .= " AND (t.id::text = :q1 OR t.subject ILIKE :q2 OR u.name ILIKE :q3 OR u.email ILIKE :q4)";
            $params[':q1'] = $search_query;
            $params[':q2'] = "%$search_query%";
            $params[':q3'] = "%$search_query%";
            $params[':q4'] = "%$search_query%";
        }
        
        $sql .= " ORDER BY t.updated_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Dashboard – What If</title>
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
            max-width: 1100px;
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
            font-size: 28px;
        }
        
        .controls-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
        }
        .form-group {
            flex: 1;
            min-width: 150px;
        }
        .form-group.search {
            flex: 2;
            min-width: 250px;
        }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            color: var(--muted);
        }
        .form-input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13.5px;
            outline: none;
            background: #f9fafb;
            font-family: inherit;
        }
        .form-input:focus, select:focus {
            border-color: var(--red);
            background: #fff;
        }
        .btn-filter {
            padding: 10px 20px;
            background: var(--red);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13.5px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-filter:hover {
            background: var(--red-dark);
        }
        .btn-reset {
            padding: 10px 20px;
            background: #e5e7eb;
            color: var(--text);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13.5px;
            transition: 0.2s;
            text-align: center;
        }
        .btn-reset:hover {
            background: #d1d5db;
        }

        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }
        th {
            background: #f9fafb;
            padding: 14px 18px;
            font-weight: 700;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover td {
            background: #fcfcfc;
        }
        
        .badge {
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-open { background: var(--green-light); color: var(--green); }
        .badge-inprogress { background: #fef3c7; color: var(--gold); }
        .badge-resolved { background: #e0f2fe; color: var(--blue); }
        .badge-closed { background: #f3f4f6; color: var(--muted); }
        
        .priority-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .priority-high { background: var(--red); }
        .priority-medium { background: var(--gold); }
        .priority-low { background: var(--blue); }
        
        .action-link {
            color: var(--red);
            font-weight: 700;
            text-decoration: none;
        }
        .action-link:hover {
            color: var(--red-dark);
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title-section">
                <a href="../../index.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
                <h1>Admin Support Console</h1>
            </div>
        </div>

        <div class="controls-card">
            <form method="GET" action="" class="filter-form">
                <div class="form-group search">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" class="form-input" placeholder="Search Ticket ID, subject, customer name..." value="<?php echo sanitize_html($search_query); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <option value="Order Issue" <?php echo $category_filter === 'Order Issue' ? 'selected' : ''; ?>>Order Issue</option>
                        <option value="Payment Issue" <?php echo $category_filter === 'Payment Issue' ? 'selected' : ''; ?>>Payment Issue</option>
                        <option value="Refund Request" <?php echo $category_filter === 'Refund Request' ? 'selected' : ''; ?>>Refund Request</option>
                        <option value="Restaurant Complaint" <?php echo $category_filter === 'Restaurant Complaint' ? 'selected' : ''; ?>>Restaurant Complaint</option>
                        <option value="Delivery Problem" <?php echo $category_filter === 'Delivery Problem' ? 'selected' : ''; ?>>Delivery Problem</option>
                        <option value="Account Issue" <?php echo $category_filter === 'Account Issue' ? 'selected' : ''; ?>>Account Issue</option>
                        <option value="Other" <?php echo $category_filter === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority">
                        <option value="">All Priorities</option>
                        <option value="Low" <?php echo $priority_filter === 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $priority_filter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $priority_filter === 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="Open" <?php echo $status_filter === 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Resolved" <?php echo $status_filter === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="Closed" <?php echo $status_filter === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                <a href="tickets.php" class="btn-reset">Reset</a>
            </form>
        </div>

        <div class="table-card">
            <?php if (count($tickets) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox" style="font-size: 48px; color: var(--border); margin-bottom: 12px;"></i>
                    <p>No support tickets match the selected filters.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Customer</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Updated At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): 
                            $status_class = strtolower(str_replace(' ', '', $t['status']));
                            $priority_class = strtolower($t['priority']);
                            ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--red);">#TKT-<?php echo $t['id']; ?></td>
                                <td>
                                    <div style="font-weight: 700;"><?php echo sanitize_html($t['customer_name']); ?></div>
                                    <div style="font-size: 11px; color: var(--muted);"><?php echo sanitize_html($t['customer_email']); ?></div>
                                </td>
                                <td><span style="font-size:12px; background:#f3f4f6; padding:3px 6px; border-radius:4px; font-weight:700; color:var(--muted);"><?php echo sanitize_html($t['category']); ?></span></td>
                                <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo sanitize_html($t['subject']); ?></td>
                                <td>
                                    <span class="priority-dot priority-<?php echo $priority_class; ?>"></span>
                                    <span style="font-weight: 500; font-size:13px;"><?php echo sanitize_html($t['priority']); ?></span>
                                </td>
                                <td><span class="badge badge-<?php echo $status_class; ?>"><?php echo sanitize_html($t['status']); ?></span></td>
                                <td style="font-size: 12.5px; color: var(--muted);"><?php echo date('M d, Y · h:i A', strtotime($t['updated_at'])); ?></td>
                                <td><a href="ticket-details.php?id=<?php echo $t['id']; ?>" class="action-link">Review & Reply</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
