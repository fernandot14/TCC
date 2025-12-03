<?php
session_start();

$id_user = $_SESSION['id_adm'] ?? null;

if (!$id_user) {
    echo "<p style='color: red; text-align:center;'>ID do usuário não encontrado.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Promover Usuário</title>

<style>
    body {
        background: #0b0b0b;
        color: white;
        font-family: Arial;
    }

    .promover-box {
        width: 500px;
        background: #1a1a1a;
        margin: 80px auto;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 0 15px #a04bff;
    }

    .promover-box h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    select {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 2px solid #a04bff;
        background: #0b0b0b;
        color: white;
        font-size: 16px;
        margin-top: 10px;
    }

    button {
        width: 100%;
        padding: 12px;
        background: #a04bff;
        color: white;
        border: none;
        border-radius: 10px;
        margin-top: 15px;
        cursor: pointer;
        font-size: 16px;
    }

    .cancelar {
        display: block;
        text-align: center;
        margin-top: 15px;
        color: #ccc;
        text-decoration: none;
    }
</style>

</head>
<body>

<div class="promover-box">

    <h2>Alterar Tipo de Usuário</h2>

    <form action="processa_promocao.php" method="POST">
        
        <!-- Id oculto do usuário a ser alterado -->
        <input type="hidden" name="id_usuario" value="<?= $id_user ?>">

        <label for="tipo">Selecione o novo tipo:</label>
        <select name="tipo" id="tipo" required>
            <option value="normal">Usuário Normal</option>
            <option value="critico">critico</option>
            <option value="ADM">Administrador</option>
        </select>

        <button type="submit">Salvar Alterações</button>

        <a href="usuario_adm.php" class="cancelar">Cancelar</a>
    </form>

</div>

</body>
</html>
