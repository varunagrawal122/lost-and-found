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
$to_id = isset($_GET['to']) ? (int)$_GET['to'] : 0;
$item_id = isset($_GET['item']) ? (int)$_GET['item'] : 0;
$prefill_subject = '';
$err = '';
$success = '';

// --- Reply handling: robustly choose the "other" participant ---
if (isset($_GET['reply_to'])) {
    $reply_id = (int)$_GET['reply_to'];
    $rep = $mysqli->query(
        "SELECT message_id, item_id, sender_id, receiver_id, subject 
         FROM messages WHERE message_id = {$reply_id} LIMIT 1"
    )->fetch_assoc();

    if ($rep) {
        if ($rep['sender_id'] == $me) {
            $to_id = (int)$rep['receiver_id'];
        } else {
            $to_id = (int)$rep['sender_id'];
        }
        $item_id = (int)$rep['item_id'];
        $prefill_subject = 'Re: ' . ($rep['subject'] ?: '(no subject)');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
    $receiver = (int)$_POST['receiver'];
    $item = (int)$_POST['item_id'];

    if ($receiver == $me) {
        $err = 'Cannot send message to yourself.';
    } elseif ($body === '') {
        $err = 'Message cannot be empty.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, item_id, subject, body, created_at) VALUES (?,?,?,?,?,NOW())");
        $stmt->bind_param('iiiss', $me, $receiver, $item, $subject, $body);
        if ($stmt->execute()) {
            $success = 'Message sent successfully.';
        } else {
            $err = 'Error sending message.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Contact</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="card">
  <h2><?php echo isset($_GET['reply_to']) ? 'Reply to Message' : 'Contact Reporter'; ?></h2>

  <?php if($err): ?><p ><?php echo $err; ?></p><?php endif; ?>
  <?php if($success): ?><p ><?php echo $success; ?></p><?php endif; ?>

  <?php if(isset($_GET['reply_to'])): ?>
    <p>Replying to a previous message.</p>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($to_id); ?>">
    <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_id); ?>">

    <label>Subject (optional)</label><br>
    <input type="text" name="subject" value="<?php echo htmlspecialchars($prefill_subject); ?>" ><br><br>

    <label>Message</label><br>
    <textarea name="body" rows="6" ></textarea><br><br>

    <button type="submit">Send</button>
    <a href="messages.php">Cancel</a>
  </form>
</div>
</body>
</html>
