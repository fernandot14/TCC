<?php
require_once '../conection/conexao.php';

$conexao = conecta();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = trim($_POST['nome']);
    $email  = trim($_POST['email']);
    $senha  = $_POST['senha'];
    $tipo = isset($_POST['perfil']) ? $_POST['perfil'] : 'Comum';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } else {
        // Verifica se email já existe
        $stmt = $conexao->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erro = "Este email já está cadastrado.";
        } else {
            // Inserir usuário
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO usuario (nome_usuario, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $hash, $tipo);

            if ($stmt->execute()) {
                $sucesso = "Usuário cadastrado com sucesso!";
            } else {
                $erro = "Erro ao cadastrar usuário.";
            }
        }
}
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2>Cadastro</h2>

            <?php if (isset($erro)): ?>
                <div class="mensagem erro"><?= htmlspecialchars($erro) ?></div>
            <?php elseif (isset($sucesso)): ?>
                <div class="mensagem sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" required>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>

                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required>

                <div class="perfil">
                    <label><input type="radio" name="perfil" value="Crítico" > Crítico</label>
                    <label><input type="radio" name="perfil" value="Adm" > Adm</label>
                </div>

                <button type="submit">Cadastrar</button>
            </form>
        </div>
    </div>
</body>
</html>
