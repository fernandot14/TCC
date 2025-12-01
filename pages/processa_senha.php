<?php
session_start();
require_once '../conection/conexao.php';

$conexao = conecta();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id'];
$senha_atual = $_POST['senha_atual'];
$nova_senha = $_POST['nova_senha'];
$confirmar_senha = $_POST['confirmar_senha'];

if ($nova_senha !== $confirmar_senha) {
    echo "<script>alert('A nova senha e a confirmação não coincidem!'); history.back();</script>";
    exit;
}

$sql = "SELECT senha FROM usuario WHERE id_usuario = $id_usuario";
$result = mysqli_query($conexao, $sql);
$dados = mysqli_fetch_assoc($result);

if ($dados['senha'] !== $senha_atual) {
    echo "<script>alert('Senha atual incorreta!'); history.back();</script>";
    exit;
}

$sqlUpdate = "UPDATE usuario SET senha = '$nova_senha' WHERE id_usuario = $id_usuario";

if (mysqli_query($conexao, $sqlUpdate)) {
    echo "<script>alert('Senha alterada com sucesso!'); window.location.href='usuario.php';</script>";
} else {
    echo "<script>alert('Erro ao atualizar senha.'); history.back();</script>";
}
?>