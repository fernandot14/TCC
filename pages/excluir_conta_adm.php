<?php
session_start();
require_once '../conection/conexao.php';

if (!isset($_SESSION['id_adm'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id_adm'];
$conn = conecta();

// 1. Excluir comentários
$sql1 = "DELETE FROM comentario WHERE id_usuario = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $id_usuario);
$stmt1->execute();

$sql2 = "DELETE FROM nota WHERE id_usuario = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id_usuario);
$stmt2->execute();

// 3. Excluir o usuário
$sql3 = "DELETE FROM usuario WHERE id_usuario = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("i", $id_usuario);
$stmt3->execute();

$_SESSION['id_adm'] = 0;

header("Location: adm.php?msg=conta_excluida");
exit;
?>