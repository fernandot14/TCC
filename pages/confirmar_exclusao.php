<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Confirmar exclusão</title>
<link rel="stylesheet" href="../css/confirmar_exclusao.css">
</head>
<body>

<div class="container">
    <h1>Deseja realmente excluir sua conta?</h1>
    <p>Essa ação é permanente e não pode ser desfeita.</p>

    <form action="excluir_conta.php" method="POST">
        <button type="submit" class="confirmar">Sim, excluir conta</button>
    </form>

    <a href="usuario.php" class="cancelar">Cancelar</a>
</div>

</body>
</html>