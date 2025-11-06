<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// include header for topbar
if (!defined('HEADER_INCLUDED')) {
    include 'header.php';
    define('HEADER_INCLUDED', true);
}
// mark_return handler
if (isset($_GET['mark_return']) && isset($_SESSION['user'])) {
    $id=(int)$_GET['mark_return'];
    $row=$mysqli->query("SELECT user_id FROM items WHERE item_id={$id} LIMIT 1")->fetch_assoc();
    if ($row && (($_SESSION['user']['role']=='admin')||($_SESSION['user']['id']==$row['user_id']))) {
        $mysqli->query("UPDATE items SET status='returned' WHERE item_id={$id} LIMIT 1");
    }
    header('Location: view.php'); exit;
}


$filter = $_GET['filter'] ?? 'all';
$show_returned = isset($_GET['show_returned']) && $_GET['show_returned'] == '1';
$search = trim($_GET['search'] ?? '');

$where = ["removed=0", "approved=1"];
if ($filter === 'lost') $where[] = "status='lost'";
elseif ($filter === 'found') $where[] = "status='found'";
if (!$show_returned) $where[] = "status<>'returned'";
if ($search !== '') {
    $s = $mysqli->real_escape_string($search);
    $where[] = "(item_name LIKE '%$s%' OR location LIKE '%$s%' OR description LIKE '%$s%')";
}

$query = "SELECT i.*, u.name FROM items i LEFT JOIN users u ON i.user_id = u.user_id";
if (!empty($where)) $query .= ' WHERE ' . implode(' AND ', $where);
$query .= " ORDER BY date_reported DESC";
$res = $mysqli->query($query);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Items - Lost & Found</title>
<link rel="stylesheet" href="styles.css">
<style>
.card .filter-row, .card .filter-row * { box-sizing: content-box; }
.card .filter-row input[type="radio"], .card .filter-row input[type="checkbox"]{ width:auto; margin:0 6px 0 0; vertical-align:middle; }
.card .filter-row .options label, .card .filter-row .controls label { display:inline-flex; align-items:center; gap:8px; margin:0; cursor:pointer; font-weight:600; }
.report-thumb{max-width:120px; border-radius:6px; margin-top:6px; display:inline-block;}
.small-btn{display:inline-block;padding:6px 8px;border-radius:4px;text-decoration:none;margin-left:8px;border:1px solid #cfcfcf;}
.top-actions{display:flex;gap:8px;align-items:center;}
</style>
</head>
<body>
<div class="card">
  <h2 >
    <span>Reported Items</span>
    <div class="top-actions">
      <?php if (isset($_SESSION['user'])): ?>
        <a class="small-btn" href="report.php">Report New</a>
      <?php else: ?>
        <a class="small-btn" href="login.php?next=report.php">Report New</a>
      <?php endif; ?>
    </div>
  </h2>

  <form method="get" >
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search item or location..." >
    <button type="submit">Search</button>
  </form>

  <form method="get" class="filter-row" role="search" aria-label="Filter reported items" >
    <div class="options" role="radiogroup" aria-label="Item type">
      <label><input type="radio" name="filter" value="all" <?php if($filter=='all') echo 'checked'; ?>> All</label>
      <label><input type="radio" name="filter" value="lost" <?php if($filter=='lost') echo 'checked'; ?>> Lost</label>
      <label><input type="radio" name="filter" value="found" <?php if($filter=='found') echo 'checked'; ?>> Found</label>
    </div>

    <div class="controls" >
      <label><input type="checkbox" name="show_returned" value="1" <?php if($show_returned) echo 'checked'; ?>> Show returned items</label>
      <button type="submit">Apply</button>
    </div>
  </form>

  <?php if($res && $res->num_rows > 0): ?>
    <?php while($row = $res->fetch_assoc()): ?>
      <div class="item" >
        <h3><?php echo htmlspecialchars($row['item_name']); ?> <small >(<?php echo htmlspecialchars($row['status']); ?>)</small></h3>
        <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
        <p><b>Location:</b> <?php echo htmlspecialchars($row['location']); ?> |
           <b>Reported by:</b> <?php echo htmlspecialchars($row['name'] ?: 'Guest'); ?> |
           <b>Date:</b> <?php echo htmlspecialchars($row['date_reported']); ?></p>

        <?php if($row['image']): ?>
          <p><a href="uploads/<?php echo htmlspecialchars($row['image']); ?>" target="_blank"><img class="report-thumb" src="uploads/<?php echo htmlspecialchars($row['image']); ?>"></a></p>
        <?php endif; ?>

        <?php if(isset($_SESSION['user']) && ($_SESSION['user']['id'] == $row['user_id'])): ?>
          <?php if($row['status']!='returned'): ?>
            <a class="small-btn" href="mark_return.php?id=<?php echo $row['item_id']; ?>" onclick="return confirm('Mark this item as returned?');">Mark Returned</a>
          <?php endif; ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['user']) && ($_SESSION['user']['id'] != $row['user_id'])): ?>
          <a class="small-btn" href="contact.php?item=<?php echo $row['item_id']; ?>&to=<?php echo $row['user_id']; ?>">Contact</a>
        <?php endif; ?>

      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No items found for the selected filters.</p>
  <?php endif; ?>
</div>
</body>
</html>
