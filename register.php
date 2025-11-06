<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// include header for topbar
if (!defined('HEADER_INCLUDED')) {
    include 'header.php';
    define('HEADER_INCLUDED', true);
}

$err='';$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']);$email=trim($_POST['email']);$pass=$_POST['password'];$role='student';
    if($name==''||$email==''||$pass=='')$err='Please fill all required fields.';
    else{
        $stmt=$mysqli->prepare("SELECT user_id FROM users WHERE email=?");$stmt->bind_param('s',$email);
        $stmt->execute();$res=$stmt->get_result();
        if($res->num_rows>0)$err='Email already registered.';
        else{
            $hash=hash('sha256',$pass);
            $ins=$mysqli->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $ins->bind_param('ssss',$name,$email,$hash,$role);
            if($ins->execute()){
                $_SESSION['user']=['id'=>$ins->insert_id,'name'=>$name,'email'=>$email,'role'=>$role];
                $next='view.php';if(!empty($_GET['next'])){$c=basename($_GET['next']);$a=['report.php','view.php'];if(in_array($c,$a))$next=$c;}
                header('Location: '.$next);exit;
            }else $err='Error creating account: '.$mysqli->error;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Account - Lost & Found</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
  <div class="card">
    <h2>Create Account</h2>

    <?php if (!empty($error)): ?>
      <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" class="register-form">
      <div class="form-row">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" required>
      </div>

      <div class="form-row">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required>
      </div>

      <div class="form-row">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div style="min-width:110px;"></div>
        <button type="submit" class="small-btn">Register</button>
      </div>

      <p style="margin-top:14px;">Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>
</div>
</body>
</html>
