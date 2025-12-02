<?php
session_start();
require_once '../conection/conexao.php';


$id_usuario = $_SESSION['id_adm'];
$conn = conecta();


$sql = "SELECT nome_usuario, foto_perfil, tipo  FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $nome_usuario = $usuario['nome_usuario'];
    $foto_perfil = $usuario['foto_perfil'];
    $tipo = $usuario['tipo'];
} else {
    $nome_usuario = "Usuário não encontrado";
    $foto_perfil = null;
}
$stmt->close();


// Buscar comentários do usuário (juntando com o nome do filme)
$sql_coment = "
    SELECT c.id_comentario, c.conteudo, c.data_comentario, f.nome_filme 
    FROM comentario c
    LEFT JOIN filme f ON c.id_filme = f.id_filme
    WHERE c.id_usuario = ?
    ORDER BY c.data_comentario DESC
";
$stmt = $conn->prepare($sql_coment);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_coment = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil - Movie Reviews</title>
    <link rel="stylesheet" href="../css/usuario.css">
</head>
<body>

    <!-- TOPO -->
    <header>
        <div class="b">

        </div>

        <img src="../imagens/logo.png" alt="Logo do site" class="logo">
        
        <nav>
            <a href="adm.php" class="btn">ADM</a>
        </nav>
    </header>

    
    <section class="perfil">
        <img src="<?= $foto_perfil ? $foto_perfil : '../imagens/default_user.png' ?>" alt="Foto do usuário">
        <h1> <?= htmlspecialchars($nome_usuario) ?> (<?= htmlspecialchars($tipo) ?>) </h1> 
    </section>

   

    <!-- AÇÕES -->
    <div class="acoes">
        <a href="promover.php">Promover</a>
        <a href="excluir_conta_adm.php">Excluir conta</a>
    </div>

    <!-- FAVORITOS -->
    <section class="favoritos">
        <h2>Favoritos</h2>
        <div class="favoritos-container">
            <div class="filme-card"></div>
            <div class="filme-card"></div>
            <div class="filme-card"></div>
            <div class="filme-card"></div>
            <div class="filme-card"></div>
            <div class="filme-card"></div>
        </div>
    </section>

    <!-- COMENTÁRIOS -->
 <section class="comentarios">
    <h2>Comentários anteriores</h2>

    <?php if ($result_coment->num_rows > 0): ?>
        <?php while ($coment = $result_coment->fetch_assoc()): ?>
            <div class="comentario-box">
                <img src="<?= $foto_perfil ? $foto_perfil : '../imagens/default_user.png' ?>" alt="User">
                <div class="comentario-texto">
                    <strong>Sobre: <?= htmlspecialchars($coment['nome_filme']) ?></strong>
                    <p><?= htmlspecialchars($coment['conteudo']) ?></p>
                    <small><?= date('d/m/Y', strtotime($coment['data_comentario'])) ?></small>
                </div>

                <!-- Botão Excluir -->
                <form action="comentario_excluir_adm.php" method="POST" class="excluir-form">
                    <input type="hidden" name="id_comentario" value="<?= $coment['id_comentario'] ?>">
                    <button class="button_excluir" type="submit">
                        Excluir
                    </button>
                </form>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Você ainda não fez comentários.</p>
    <?php endif; ?>
</section>

</body>
</html>
