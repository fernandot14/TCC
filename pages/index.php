<?php
session_start();  

$_SESSION['login'] = false;
$_SESSION ['tipo'] = "comum";

header("Location: home.php");
exit;              
?>