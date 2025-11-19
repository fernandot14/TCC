<?php
require_once '../conection/conexao.php';
$conexao = conecta(); 

// Obtém o ID do filme pela URL (ex: filme.php?id=1)
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Consulta o filme
$sql = "SELECT * FROM filme WHERE id_filme = $id";
$res = mysqli_query($conexao, $sql);
$filme = mysqli_fetch_assoc($res);

// Consulta os comentários do filme
$sqlComentarios = "
SELECT c.conteudo, c.data_comentario, u.nome_usuario, u.foto_perfil 
FROM comentario c 
JOIN usuario u ON c.id_usuario = u.id_usuario 
WHERE c.id_filme = $id";
$comentarios = mysqli_query($conexao, $sqlComentarios);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $filme['nome_filme'] ?></title>
<link rel="stylesheet" href="../css/filmes.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="search-bar">
                <i class="fa fa-search"></i>
                <input type="text" placeholder="Buscar filme...">
            </div>

            <img src="../imagens/logo.png" alt="Logo" class="logo">

            <nav>
                <a href="#">Login</a>
                <a href="#">Cadastre-se</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="filme-header">
            <h1><?= $filme['nome_filme'] ?></h1>
            <span class="genero"><?= $filme['genero'] ?></span>
            <p class="data"><?= date('d/m/Y', strtotime($filme['data_lancamento'])) ?></p>
        </section>

        <section class="midias">
            <img src="<?= $filme['caminho_imagem'] ?>" alt="Capa" class="capa">
            <video controls class="trailer">
                <source src="<?= $filme['trailer'] ?>" type="video/mp4">
            </video>
        </section>

        <section class="avaliacoes">
            <p>Avaliações:</p>
            <div>🍅 Tomatoes: <?= $filme['media_tomatoes'] ?></div>
            <div>🎬 IMDB: <?= $filme['media_imbd'] ?></div>
            <div>⭐ MR: <?= $filme['media_geral'] ?></div>
        </section>

        <section class="media-final">
            <p>MÉDIA FINAL: <span><?= $filme['media_geral'] ?> ⭐</span></p>
        </section>

        <section class="sinopse">
            <h2>Sinopse</h2>
            <p><?= $filme['sinopse'] ?></p>
        </section>

       <section class="comentarios-section">
  <div class="comentarios-header">
    <h3>⭐ Comentários</h3>
    <button class="btn-comentar">COMENTAR</button>
  </div>

  <div class="comentarios-container">
    <!-- Comentários vindos do banco -->
    <?php foreach ($comentarios as $comentario): ?>
      <div class="comentario-card">
        <div class="comentario-topo">
          <img src="imagens/user-icon.png" alt="Usuário" class="comentario-avatar">
          <span class="comentario-estrela">⭐</span>
        </div>
        <p class="comentario-texto"><?= htmlspecialchars($comentario['conteudo']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>


     
    </main>

    <footer>
        <p>Copyright ©</p>
        <div class="icons">
            <i class="fa fa-instagram"></i>
            <i class="fa fa-youtube"></i>
            <i class="fa fa-facebook"></i>
        </div>
    </footer>
</body>
</html>

<?php desconecta($conexao); ?>
