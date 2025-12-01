<?php
session_start();
require_once '../conection/conexao.php';
$conexao = conecta();

if (!isset($_SESSION['id'])) {
    echo "<script>alert('Você precisa estar logado para comentar.'); window.location.href='login.php';</script>";
    exit();
}

$id_filme = $_GET['id'];
$id_usuario = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conteudo = trim($_POST['conteudo']);

    if ($conteudo !== "") {
        $sql = "INSERT INTO comentario (conteudo, data_comentario, id_filme, id_usuario) 
                VALUES (?, CURDATE(), ?, ?)";

        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sii", $conteudo, $id_filme, $id_usuario);
        mysqli_stmt_execute($stmt);

        echo "<script>alert('Comentário enviado!'); window.location.href='filmes.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Comentar</title>
<link rel="stylesheet" href="../css/comentario.css">
</head>
<body>

<div class="coment-box">

    <h2>Escrever Comentário</h2>

    <form method="POST">
        <textarea name="conteudo" placeholder="Digite seu comentário..." required></textarea>

        <button type="submit">Enviar</button>
        <a href="filmes.php" class="cancelar">Cancelar</a>
    </form>

</div>

</body>
</html>