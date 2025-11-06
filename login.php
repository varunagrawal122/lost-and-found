<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// include header for topbar
if (!defined('HEADER_INCLUDED')) {
    include 'header.php';
    define('HEADER_INCLUDED', true);
}

$err = '';
$next = 'view.php';
if (!empty($_GET['next'])) {
    $candidate = basename($_GET['next']);
    $allowed = ['report.php','view.php','admin.php','login.php'];
    if (in_array($candidate, $allowed)) $next = $candidate;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $mysqli->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    $sql = $mysqli->prepare("SELECT user_id,name,email,password,role FROM users WHERE email=?");
    $sql->bind_param('s',$email);$sql->execute();
    $res=$sql->get_result();
    if($res->num_rows===1){
        $row=$res->fetch_assoc();
        if(hash('sha256',$pass)===$row['password']){
            $_SESSION['user']=['id'=>$row['user_id'],'name'=>$row['name'],'email'=>$row['email'],'role'=>$row['role']];
            header('Location: '.$next);exit;
        }else $err='Invalid credentials';
    }else $err='User not found';
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lost & Found - Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
  <div class="card">
    <h2>Lost &amp; Found - Login</h2>

    <form method="post" action="login.php" class="login-form" novalidate>
      <div class="form-row">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required autocomplete="username" />
      </div>

      <div class="form-row" style="margin-top:8px;">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" />
      </div>

      <div class="form-row" style="margin-top:12px; align-items:center;">
        <div style="min-width:110px;"></div>
        <button type="submit" class="small-btn">Login</button>
      </div>

      <p style="margin-top:14px;">New user? <a href="register.php">Create an account</a></p>
    </form>
  </div>
</div>

</body>
</html>
