<?php
session_start();




$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'lostfound';
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) { die("DB connection failed: " . $mysqli->connect_error); }
function ensure_login(){
    if(!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
}
?>