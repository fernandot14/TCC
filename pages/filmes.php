<?php
session_start();

require_once '../conection/conexao.php';
$conexao = conecta(); 

if (isset($_GET['busca'])) {
    $busca = trim($_GET['busca']);

    if ($busca === '') {
        echo "<script>alert('Digite algo para buscar.');</script>";
    } else {
       
        $sqlBusca = "SELECT id_filme, nome_filme, data_lancamento, genero, trailer, caminho_imagem, media_tomatoes, media_imbd, media_geral, sinopse FROM filme WHERE nome_filme LIKE ? LIMIT 10";
        $like = '%' . $busca . '%';

        if ($stmt = mysqli_prepare($conexao, $sqlBusca)) {
            mysqli_stmt_bind_param($stmt, "s", $like);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            $melhor = null;
            $melhorScore = PHP_INT_MAX; 
            $melhorSimilar = -1; 

            while ($row = mysqli_fetch_assoc($res)) {
                $nomeBanco = $row['nome_filme'];

                // calcula distância e similaridade
                $lev = levenshtein(mb_strtolower($busca, 'UTF-8'), mb_strtolower($nomeBanco, 'UTF-8'));
                similar_text(mb_strtolower($busca, 'UTF-8'), mb_strtolower($nomeBanco, 'UTF-8'), $perc);

                // estratégia combinada: prefira menor lev; em caso de empate, maior similaridade %
                // para tornar comparável, usamos um "score" simples: lev - floor(perc)
                $score = $lev - intval($perc); // menor score = melhor

                if ($melhor === null || $score < $melhorScore) {
                    $melhor = $row;
                    $melhorScore = $score;
                    $melhorSimilar = $perc;
                }
            }

            mysqli_stmt_close($stmt);

            // Se nenhum candidato via LIKE, procuramos todos e fazemos matching completo (fallback)
            if ($melhor === null) {
                $sqlAll = "SELECT id_filme, nome_filme, data_lancamento, genero, trailer, caminho_imagem, media_tomatoes, media_imbd, media_geral, sinopse FROM filme";
                $resAll = mysqli_query($conexao, $sqlAll);
                while ($row = mysqli_fetch_assoc($resAll)) {
                    $nomeBanco = $row['nome_filme'];
                    $lev = levenshtein(mb_strtolower($busca, 'UTF-8'), mb_strtolower($nomeBanco, 'UTF-8'));
                    similar_text(mb_strtolower($busca, 'UTF-8'), mb_strtolower($nomeBanco, 'UTF-8'), $perc);
                    $score = $lev - intval($perc);
                    if ($melhor === null || $score < $melhorScore) {
                        $melhor = $row;
                        $melhorScore = $score;
                        $melhorSimilar = $perc;
                    }
                }
            }

            if ($melhor !== null) {
                // Salva todas as informações do filme em session (mantendo os campos do seu create table)
                $_SESSION['filme_id'] = $melhor['id_filme'];

                // Redireciona para filmes.php
                header("Location: filmes.php");
                exit();
            } else {
                echo "<script>alert('Nenhum filme encontrado.');</script>";
            }
        } else {
            // Erro na preparação
            error_log("Erro prepare busca: " . mysqli_error($conexao));
            echo "<script>alert('Erro na busca. Tente novamente.');</script>";
        }
    }
}


$id = $_SESSION['filme_id'];


$sql = "SELECT * FROM filme WHERE id_filme = $id";
$res = mysqli_query($conexao, $sql);
$filme = mysqli_fetch_assoc($res);


$sqlComentarios = "
SELECT c.conteudo, c.data_comentario, u.nome_usuario, u.foto_perfil 
FROM comentario c 
JOIN usuario u ON c.id_usuario = u.id_usuario 
WHERE c.id_filme = $id
ORDER BY c.data_comentario DESC";
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
        <form method="get" action="">
            <input 
                type="text" 
                name="busca" 
                placeholder="Buscar filme..." 
                required 
                onkeydown="if(event.key==='Enter'){this.form.submit();}"
            >
        </form>
    </div>

            <img src="../imagens/logo.png" alt="Logo" class="logo">

            <nav>
                <a href="home.php">home</a>
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
            <div> Tomatoes: <?= $filme['media_tomatoes'] ?></div>
            <div> IMDB: <?= $filme['media_imbd'] ?></div>
            <div><span class="comentario-estrela">★</span> MR: <?= $filme['media_geral'] ?></div>
        </section>

        <section class="media-final">
            <p>MÉDIA FINAL: <span><?= $filme['media_geral'] ?> ★</span></p>
        </section>

        <section class="sinopse">
            <h2>Sinopse</h2>
            <p><?= $filme['sinopse'] ?></p>
        </section>

       <section class="comentarios-section">
  <div class="comentarios-header">
    <h3>| Comentários</h3>
     <?php if ($_SESSION['login']): ?>
        <a href="comentario.php?id=<?= $filme['id_filme'] ?>" class="btn-comentar">COMENTAR</a> 
     <?php endif; ?>
  </div>

  <div class="comentarios-container">
    <!-- Comentários vindos do banco -->
    <?php foreach ($comentarios as $comentario): ?>
      <div class="comentario-card">
        <div class="comentario-topo">
          <img src="../imagens/default_user.png" alt="Usuário" class="comentario-avatar">
          <span class="comentario-estrela">★</span>
        </div>
        
        <p style= "font-size: 20px;" class="comentario-texto"><?= htmlspecialchars($comentario['nome_usuario']) ?></p>
        <p style= "font-size: 12px;" class="comentario-texto"><?= htmlspecialchars($comentario['data_comentario']) ?></p>
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
