<?php
session_start();

if ($_SESSION['login'] != true) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
        echo "<a href = 'usuario.php'>bem vindo(a)</a>: ".$_SESSION['nome_usuario'];
    ?>

</body>
</html>