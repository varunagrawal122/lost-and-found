<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// include header for topbar
if (!defined('HEADER_INCLUDED')) {
    include 'header.php';
    define('HEADER_INCLUDED', true);
}
 ensure_login();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $mysqli->real_escape_string($_POST['item_name']);
    $description = $mysqli->real_escape_string($_POST['description']);
    $location = $mysqli->real_escape_string($_POST['location']);
    $status = ($_POST['type'] === 'lost') ? 'lost' : 'found';
    $user_id = $_SESSION['user']['id'];
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }
    $stmt = $mysqli->prepare("INSERT INTO items (item_name, description, location, status, user_id, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssis', $item_name, $description, $location, $status, $user_id, $imageName);
    $msg = $stmt->execute() ? "Report submitted successfully." : "Error: " . $mysqli->error;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Report Lost / Found Item</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
  <div class="card">
    <h2>Report Lost / Found Item</h2>

    <?php if (!empty($error)): ?>
      <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-row">
        <label for="name">Item Name</label>
        <input id="name" name="name" type="text" required>
      </div>

      <div class="form-row">
        <label for="description">Description</label>
        <textarea id="description" name="description"></textarea>
      </div>

      <div class="form-row">
        <label for="location">Location</label>
        <input id="location" name="location" type="text" required>
      </div>

      <div class="form-row">
        <label for="type">Type</label>
        <select id="type" name="type" required>
          <option value="">Select</option>
          <option value="Lost">Lost</option>
          <option value="Found">Found</option>
        </select>
      </div>

      <div class="form-row">
        <label for="image">Image (optional)</label>
        <input id="image" name="image" type="file" accept="image/*">
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div style="min-width:110px;"></div>
        <button type="submit" class="small-btn">Submit Report</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>