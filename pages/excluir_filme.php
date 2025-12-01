<?php
require_once "../conection/conexao.php";

$conn = conecta();
if (!$conn) {
    die("Erro ao conectar ao banco de dados.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_filme"])) {

    $id = intval($_POST["id_filme"]);

    // deletar nota
    $stmt = $conn->prepare("DELETE FROM nota WHERE id_filme = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // deletar comentarios
    $stmt = $conn->prepare("DELETE FROM comentario WHERE id_filme = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // deletar filme
    $stmt = $conn->prepare("DELETE FROM filme WHERE id_filme = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<script>alert('Filme removido com sucesso!'); window.location='excluir_filme.php';</script>";
    exit;
}

// pegar filmes
$query = $conn->query("SELECT id_filme, nome_filme FROM filme ORDER BY nome_filme ASC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Remover Filme</title>
    <link rel="stylesheet" href="../css/excluir_filme.css">
</head>
<body>

<div class="top-bar">
    <a href="../pages/home.php" class="icon-svg">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
            <path d="M3 10L12 3L21 10V21H14V14H10V21H3V10Z"/>
        </svg>
    </a>
</div>





<div class="container">
    <h2>REMOVER FILME</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Filme</th>
            <th>Ação</th>
        </tr>

        <?php while ($f = $query->fetch_assoc()): ?>
        <tr>
            <td><?= $f["id_filme"] ?></td>
            <td><?= $f["nome_filme"] ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Tem certeza que deseja remover este filme?')">
                    <input type="hidden" name="id_filme" value="<?= $f["id_filme"] ?>">
                    <button class="btn-delete">Remover</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

</body>
</html>