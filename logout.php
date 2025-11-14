<?php
// C:\xampp\htdocs\todolist\logout.php
session_start(); 


session_unset();
session_destroy();


header("Location: login.php");
exit;
?>