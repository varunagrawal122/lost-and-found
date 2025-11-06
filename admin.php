<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// include header for topbar
if (!defined('HEADER_INCLUDED')) {
    include 'header.php';
    define('HEADER_INCLUDED', true);
}
// require login and admin check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
ensure_login();
// --- Admin action handlers ---
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $stmt = $mysqli->prepare('UPDATE items SET approved=1 WHERE item_id=? LIMIT 1');
    if ($stmt) { $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close(); }
    header('Location: admin.php'); exit;
}
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    $stmt = $mysqli->prepare('UPDATE items SET removed=1 WHERE item_id=? LIMIT 1');
    if ($stmt) { $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close(); }
    header('Location: admin.php'); exit;
}
if (isset($_GET['export'])) {
    $res = $mysqli->query("SELECT item_id,item_name,status,location,date_reported FROM items WHERE removed=0");
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=lostfound_export.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['item_id','item_name','status','location','date_reported']);
    while ($r = $res->fetch_assoc()) {
        fputcsv($out, [$r['item_id'],$r['item_name'],$r['status'],$r['location'],$r['date_reported']]);
    }
    fclose($out);
    exit;
}

// fetch items
$res = $mysqli->query('SELECT i.*, u.name FROM items i LEFT JOIN users u ON i.user_id=u.user_id ORDER BY date_reported DESC');

?><!doctype html>
<html><head><meta charset="utf-8"><title>Admin - Lost & Found</title>
<link rel="stylesheet" href="styles.css">
</head><body>


<div class="card admin-list">
  <h2>Admin Dashboard</h2>
  <?php
    $pending = $mysqli->query('SELECT COUNT(*) AS c FROM items WHERE approved=0 AND removed=0')->fetch_assoc()['c'];
  ?>
  <p style="font-weight:700;color:#b05a00;">Pending approvals: <?php echo (int)$pending; ?></p>
  <p><a href="admin.php?export=1" class="small-btn">Export CSV</a></p>

  <?php if($res && $res->num_rows>0): ?>
    <?php while($row = $res->fetch_assoc()): ?>
      <div class="admin-item">
        <div class="meta">
          <h3 style="margin:0;">
            <?php echo htmlspecialchars($row['item_name']); ?>
            <small style="font-weight:600;color:#555;">(<?php echo htmlspecialchars($row['status']); ?>)</small>
            <?php if(!$row['approved']) echo ' <span style="background:#ffe6a7;padding:4px 8px;border-radius:8px;margin-left:8px;">Unapproved</span>'; ?>
          </h3>
          <p style="margin:6px 0;"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
          <p style="margin:4px 0;color:#444;">
            <b>Location:</b> <?php echo htmlspecialchars($row['location']); ?> |
            <b>Reported by:</b> <?php echo htmlspecialchars($row['name'] ?: 'Guest'); ?>
          </p>
          <p style="margin:4px 0;color:#666;font-size:0.9rem;"><b>Date:</b> <?php echo htmlspecialchars($row['date_reported']); ?></p>
        </div>

        <div class="admin-actions">
          <?php if(!$row['approved']): ?>
            <a class="verify-btn small-btn" href="admin.php?approve=<?php echo (int)$row['item_id']; ?>">Approve</a>
          <?php endif; ?>

          <?php if(!$row['removed']): ?>
            <a class="return-btn small-btn" href="admin.php?remove=<?php echo (int)$row['item_id']; ?>" onclick="return confirm('Remove this post?')">Remove</a>
          <?php else: ?>
            <span style="color:#888;">Removed</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No reports yet.</p>
  <?php endif; ?>
</div>

</body></html>
