<?php
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $_SESSION['filme_id'] = intval($_GET['id']);

    // redireciona para a página do filme
    header("Location: filmes.php");
    exit();
} else {
    // caso o usuário tente acessar sem id válido
    header("Location: index.php");
    exit();
}
