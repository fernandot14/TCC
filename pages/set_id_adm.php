<?php
session_start();

if (isset($_GET['id'])) {
    $_SESSION['id_adm'] = $_GET['id']; // salva na sessão
    header("Location: usuario_adm.php?id=" . $_GET['id']); // redireciona para o perfil
    exit();
}
?>
