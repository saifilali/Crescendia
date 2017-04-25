<?php
 session_start();
 ob_start();
 if (!isset($_SESSION['user'])) {
  header("Location: http://crescendiagame.com/index.php");
 } else if(isset($_SESSION['user'])!="") {
  header("Location: home.php");
 }

 if (isset($_GET['logout'])) {
  unset($_SESSION['user']);
  session_unset();
  session_destroy();
  header("Location: http://crescendiagame.com/index.php");
  exit;
 }
?>
