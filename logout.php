<?php
require 'config.php';




session_destroy();
header('Location: view.php');
exit;
?>