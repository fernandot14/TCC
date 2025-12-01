<?php
session_start();
require_once '../conection/conexao.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id'];
$conn = conecta();

$erro = '';
$sucesso = '';

// Se o formulário foi enviado (POST) — processa atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome = isset($_POST['novo_nome']) ? trim($_POST['novo_nome']) : '';

    // Validação simples
    if ($novo_nome === '') {
        $erro = "O nome não pode ficar vazio.";
    } elseif (mb_strlen($novo_nome) > 100) {
        $erro = "O nome pode ter no máximo 100 caracteres.";
    } else {
        // Atualiza no banco
        $sql = "UPDATE usuario SET nome_usuario = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $erro = "Erro interno: prepare falhou.";
        } else {
            $stmt->bind_param("si", $novo_nome, $id_usuario);
            if ($stmt->execute()) {
                $sucesso = "Nome atualizado com sucesso.";
                // Redireciona de volta para o perfil para ver a mudança
                header("Location: usuario.php?msg=nome_atualizado");
                exit;
            } else {
                $erro = "Falha ao atualizar. Tente novamente.";
            }
            $stmt->close();
        }
    }
}

// Buscar o nome atual para preencher o campo
$sql2 = "SELECT nome_usuario FROM usuario WHERE id_usuario = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id_usuario);
$stmt2->execute();
$result = $stmt2->get_result();
$nome_atual = '';
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nome_atual = $row['nome_usuario'];
}
$stmt2->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Alterar nome - Movie Reviews</title>
<link rel="stylesheet" href="../css/allterar_nome.css">
</head>
<body>

<header>
   
</header>

<main class="container">
    <h1>Alterar nome</h1>
<?php if ($erro): ?>
    <div class="msg erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="msg sucesso"><?= htmlspecialchars($sucesso) ?></div>
<?php endif; ?>

<form action="alterar_nome.php" method="POST" class="form-nome">
    <input type="text" id="novo_nome" name="novo_nome" value="<?= htmlspecialchars($nome_atual) ?>" maxlength="100" required>

    <div class="botoes">
        <button type="submit" class="btn-confirm">Salvar novo nome</button>
        <a href="usuario.php" class="btn-cancel">Voltar</a>
    </div>
</form>
</main>

</body>
</html>