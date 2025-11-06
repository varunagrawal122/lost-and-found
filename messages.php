<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// include header for topbar
if (!defined('HEADER_INCLUDED')) {
    include 'header.php';
    define('HEADER_INCLUDED', true);
}

ensure_login();
$me = $_SESSION['user']['id'];
$received = $mysqli->query("SELECT m.*, i.item_name, u.name AS sender_name FROM messages m LEFT JOIN items i ON m.item_id=i.item_id LEFT JOIN users u ON m.sender_id=u.user_id WHERE m.receiver_id={$me} ORDER BY m.created_at DESC");
$sent = $mysqli->query("SELECT m.*, i.item_name, u.name AS receiver_name FROM messages m LEFT JOIN items i ON m.item_id=i.item_id LEFT JOIN users u ON m.receiver_id=u.user_id WHERE m.sender_id={$me} ORDER BY m.created_at DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Messages</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="card">
  <h2>Messages</h2>

  <h3>Received</h3>
  <?php if($received && $received->num_rows>0): ?>
    <?php while($m = $received->fetch_assoc()): ?>
      <div >
        <strong><?php echo htmlspecialchars($m['subject']?:'No subject'); ?></strong>
        <a class="small-btn" href="contact.php?reply_to=<?php echo (int)$m['message_id']; ?>">Reply</a>
        <div >From: <?php echo htmlspecialchars($m['sender_name']?:'Unknown'); ?> | About: <?php echo htmlspecialchars($m['item_name']?:'Item'); ?> | At: <?php echo $m['created_at']; ?></div>
        <?php echo nl2br(htmlspecialchars($m['body'])); ?>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $m['receiver_id']) : ?>
          <p>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No received messages.</p>
  <?php endif; ?>

  <h3>Sent</h3>
  <?php if($sent && $sent->num_rows>0): ?>
    <?php while($m = $sent->fetch_assoc()): ?>
      <div >
        <strong><?php echo htmlspecialchars($m['subject']?:'No subject'); ?></strong>
        <div >To: <?php echo htmlspecialchars($m['receiver_name']?:'Unknown'); ?> | About: <?php echo htmlspecialchars($m['item_name']?:'Item'); ?> | At: <?php echo $m['created_at']; ?></div>
        <?php echo nl2br(htmlspecialchars($m['body'])); ?>
        <p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No sent messages.</p>
  <?php endif; ?>
</div>
</body>
</html>
