<?php
session_start();
require_once "../conection/conexao.php";

$con = conecta();

$id = $_POST['id_usuario'];
$novoCargo = $_POST['tipo'];

$sql = "UPDATE usuario SET tipo = '$novoCargo' WHERE id_usuario = $id";
mysqli_query($con, $sql);

mysqli_close($con);

header("Location: usuario_adm.php?id=$id");
exit;
?>
