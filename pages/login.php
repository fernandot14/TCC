<?php
require_once '../conection/conexao.php';
session_start();

$conexao = conecta();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = trim($_POST['email']);
    $senha  = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Verifica se o email existe
        $stmt = $conexao->prepare("SELECT id_usuario, nome_usuario, senha, tipo FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_usuario, $nome_usuario, $senha_bd, $tipo);
            $stmt->fetch();

            // Como você não está usando senha criptografada, comparação direta:
            if ($senha === $senha_bd) {
                // Inicia sessão do usuário
                session_regenerate_id(true);
                $_SESSION['id'] = $id_usuario;
                $_SESSION['nome_usuario'] = $nome_usuario;
                $_SESSION['tipo'] = $tipo;
                $_SESSION['login'] = true;

                header("Location: home.php"); // página principal após login
                exit();
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Email não encontrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../css/cadastro.css"> <!-- reutiliza o mesmo CSS -->
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2>Login</h2>

            <?php if (isset($erro)): ?>
                <div class="mensagem erro"><?= htmlspecialchars($erro) ?></div>
            <?php elseif (isset($sucesso)): ?>
                <div class="mensagem sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>

                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required>

                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>



?>