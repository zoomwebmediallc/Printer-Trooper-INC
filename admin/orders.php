<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../includes/email_helper.php';

// NOTE: No authentication implemented. Consider protecting this page.

$database = new Database();
$db = $database->getConnection();
$orderModel = new Order($db);

$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$validStatuses = $orderModel->getValidStatuses();
if ($statusFilter && !in_array($statusFilter, $validStatuses, true)) {
  $statusFilter = '';
}

$orders = $orderModel->listOrders($statusFilter, 200, 0);

function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Orders</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .admin-body h2{
      text-align: center;
      margin-top: 1.5rem;
      color: #084298;
      font-size: 2.3rem;
    }
    .admin-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1rem;
      background-color: #cfe2ff;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border-radius: 10px;
    }

    .orders-table {
      width: 100%;
      border-collapse: collapse;
    }

    .orders-table th,
    .orders-table td {
      padding: 8px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
    }

    .status-processing {
      background: #cfe2ff;
      color: #084298;
    }

    .status-shipped {
      background: #d1e7dd;
      color: #0f5132;
    }

    .status-delivered {
      background: #e2f7d9;
      color: #1b5e20;
    }

    .status-cancelled {
      background: #f8d7da;
      color: #842029;
    }

    .inline-form {
      display: inline-block;
    }

    .inline-form select {
      padding: 4px;
    }

    .inline-form button {
      padding: 6px 10px;
      margin-left: 6px;
    }

    .filters {
      margin-bottom: 1rem;
    }

    .filters form label {
      font-size: 24px;
      font-weight: 700;
    }

    .filters form select {
      font-size: 16px;
      padding: 6px 10px;
      margin-left: 8px;
    }
    .filters form button {
      padding: 10px 15px;
      margin-left: 8px;
    }
  </style>
</head>

<body class="admin-body">
  <h2>Admin Dashboard ( All Orders )</h2>
  <div class="admin-container">

    <div class="filters">
      <form method="GET" action="orders.php">
        <label for="status">Filter by status:</label>
        <select name="status" id="status">
          <option value="">All</option>
          <?php foreach ($validStatuses as $s): ?>
            <option value="<?php echo h($s); ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>>
              <?php echo ucfirst($s); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary">Apply</button>
      </form>
    </div>



    <div style="overflow:auto;">
      <table class="orders-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Order #</th>
            <th>Date</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><?php echo (int) $o['id']; ?></td>
              <td><?php echo h($o['order_number']); ?></td>
              <td><?php echo h($o['order_date']); ?></td>
              <td><?php echo h($o['customer_email']); ?></td>
              <td>$<?php echo number_format((float) $o['total_amount'], 2); ?></td>
              <td>
                <span
                  class="status-badge status-<?php echo h($o['status']); ?>"><?php echo ucfirst($o['status']); ?></span>
              </td>
              <td>
                <form class="inline-form" method="POST" action="update_order_status.php"
                  onsubmit="return confirm('Update status?');">
                  <input type="hidden" name="order_id" value="<?php echo (int) $o['id']; ?>">
                  <select name="status">
                    <?php foreach ($validStatuses as $s): ?>
                      <option value="<?php echo h($s); ?>" <?php echo $o['status'] === $s ? 'selected' : ''; ?>>
                        <?php echo ucfirst($s); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label style="margin-left:8px;">
                    <input type="checkbox" name="notify" value="1" checked>
                    Notify customer
                  </label>
                  <button type="submit" class="btn-secondary">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <p style="margin-top:1rem;"><a href="../index.php">← Back to site</a> | <a href="orders.php">Refresh</a></p>
  </div>
</body>

</html>