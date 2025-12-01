<?php
session_start();
require_once '../conection/conexao.php';

if (isset($_POST['id_comentario'])) {
    $id_comentario = $_POST['id_comentario'];
    $id_usuario = $_SESSION['id_adm'];

    $conexao = conecta();

    $sql = "DELETE FROM comentario WHERE id_comentario = ? AND id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $id_comentario, $id_usuario);

    if ($stmt->execute()) {
        header("Location: usuario_adm.php?msg=excluido");
        exit;
    } else {
        echo "Erro ao excluir comentário.";
    }
}
?>