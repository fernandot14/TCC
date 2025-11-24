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


$sqlTop5 = "SELECT nome_filme, caminho_imagem, media_geral FROM filme ORDER BY media_geral DESC LIMIT 5";
$topFilmes = mysqli_query($conexao, $sqlTop5);

// Comentários de críticos (usuários com tipo 'critico')
$sqlCriticos = "
SELECT 
    u.nome_usuario, 
    u.foto_perfil, 
    f.nome_filme, 
    f.media_geral, 
    c.conteudo, 
    c.data_comentario
FROM comentario AS c
INNER JOIN usuario AS u ON c.id_usuario = u.id_usuario
INNER JOIN filme AS f ON c.id_filme = f.id_filme
WHERE LOWER(TRIM(u.tipo)) = 'critico'
ORDER BY c.data_comentario DESC
LIMIT 5
";
$comentariosCriticos = mysqli_query($conexao, $sqlCriticos);

if (!$comentariosCriticos) {
    die("Erro ao buscar comentários: " . mysqli_error($conexao));
}

echo "<!-- Debug SQL -->\n";
echo "<!-- SQL executada: $sqlCriticos -->\n";
echo "<!-- Linhas encontradas: " . mysqli_num_rows($comentariosCriticos) . " -->\n";

if (mysqli_num_rows($comentariosCriticos) > 0) {
    $first = mysqli_fetch_assoc($comentariosCriticos);
    echo "<!-- Primeiro resultado: " . htmlspecialchars(json_encode($first, JSON_UNESCAPED_UNICODE)) . " -->\n";
    mysqli_data_seek($comentariosCriticos, 0);
}



// Sugestões de filmes
$sqlSugestoes = "SELECT nome_filme, caminho_imagem FROM filme ORDER BY RAND() LIMIT 5";
$sugestoes = mysqli_query($conexao, $sqlSugestoes);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Movie Reviews</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>
    <header>
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

    <div class="logo">
        <img src="" alt="Logo do site">
    </div>

    <nav>
        <?php if ($_SESSION['login']): ?>
            <?php if ($_SESSION['tipo'] == 'ADM'): ?>
                <a href="adm.php" class="btn">ADM</a>
            <?php endif; ?>
            <a href="logout.php" class="btn">Sair</a>
            <a href="usuario.php" class="user-icon">👤</a>
        <?php else: ?>
            <a href="login.php" class="btn">Login</a>
            <a href="cadastro.php" class="btn">Cadastre-se</a>
        <?php endif; ?>
    </nav>
</header>

    <main>
        <section class="top-filmes">
            <h2>|  Top 5 no Movie Reviews</h2>
            <div class="retangulos-top5">
                <div class="retangulo"></div>
                <div class="retangulo"></div>
                <div class="retangulo"></div>
                <div class="retangulo"></div>
                <div class="retangulo"></div>
            </div>

        </section>

      <section class="opinioes-criticos">
    <h2>|  Opinião dos críticos</h2>
    <section class="critics-section">
        <div class="critics-container">
            <?php if (mysqli_num_rows($comentariosCriticos) > 0): ?>
                <?php while ($c = mysqli_fetch_assoc($comentariosCriticos)): ?>
                    <div class="critic-card">
                        <div class="critic-profile">
                            <?php if (!empty($c['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($c['foto_perfil']) ?>" alt="Foto de <?= htmlspecialchars($c['nome_usuario']) ?>" class="profile-circle">
                             <?php else: ?>
                             <div class="profile-circle"></div>
                             <?php endif; ?>

                            <div class="critic-header">
                            <span class="star">★</span>
                                <strong class="critic-name"><?= htmlspecialchars($c['nome_usuario']) ?></strong>
                                </div>
                                </div>

<div class="critic-text">
    <p class="critic-film"><em><?= htmlspecialchars($c['nome_filme']) ?></em></p>
    <p class="critic-comment"><?= htmlspecialchars($c['conteudo']) ?></p>
    <p class="rating">Nota média: <strong><?= number_format($c['media_geral'], 1) ?></strong></p>
    <p class="critic-date">Data: <?= date('d/m/Y', strtotime($c['data_comentario'])) ?></p>
</div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum comentário de crítico encontrado.</p>
            <?php endif; ?>
        </div>
    </section>
</section>




        <section class="sua-review">
            <h2>|  Dê sua review</h2>

            <div class="retangulos-review">
                <div class="retangulo2"></div>
                <div class="retangulo2"></div>
                <div class="retangulo2"></div>
                <div class="retangulo2"></div>
                <div class="retangulo2"></div>
            </div>
        </section>
    </main>

    <footer>
        <p>Copyright © Movie Reviews</p>
        <div class="redes">
            <a href="#"><img src="../imagens/instagram.png" alt="instagram"></a>
            <a href="#"><img src="../imagens/Youtube.png" alt="youtube"></a>
            <a href="#"><img src="../imagens/twitter.png" alt="twitter"></a>
        </div>
    </footer>
</body>
</html>
