<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Alterar Senha</title>
<link rel="stylesheet" href="../css/alterar_senha.css">

</head>
<body>

<div class="container">
    <h2>Alterar Senha</h2>

    <form action="processa_senha.php" method="POST">
        <input type="password" name="senha_atual" placeholder="Senha atual" required>

        <input type="password" name="nova_senha" placeholder="Nova senha" required maxlength="10">

        <input type="password" name="confirmar_senha" placeholder="Confirmar nova senha" required maxlength="10">

      <button type="submit" class="botao">Salvar nova senha</button>
      <br><br>
<a href="usuario.php" >Voltar</a>

    </form>
</div>

</body>
</html>