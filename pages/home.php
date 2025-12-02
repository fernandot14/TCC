<?php
session_start();

require_once '../conection/conexao.php';
$conexao = conecta(); 

$sqlTop5 = "
    SELECT 
        f.id_filme,
        f.nome_filme,
        f.caminho_imagem,
        AVG(n.valor) AS media_mr
    FROM filme f
    LEFT JOIN nota n ON n.id_filme = f.id_filme
    GROUP BY f.id_filme
    ORDER BY media_mr DESC
    LIMIT 5
";

$resultTop5 = mysqli_query($conexao, $sqlTop5);
$top5 = [];

while($linha = mysqli_fetch_assoc($resultTop5)){
    $top5[] = $linha;
}


if (isset($_GET['busca'])) {
    $busca = trim($_GET['busca']);

    if ($busca === '') {
        echo "<script>alert('Digite algo para buscar.');</script>";
    } else {
       
        $sqlBusca = "SELECT id_filme, nome_filme, data_lancamento, genero, trailer, caminho_imagem, media_tomatoes, media_imbd, sinopse FROM filme WHERE nome_filme LIKE ? LIMIT 10";
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
                $sqlAll = "SELECT id_filme, nome_filme, data_lancamento, genero, trailer, caminho_imagem, media_tomatoes, media_imbd, sinopse FROM filme";
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

// Comentários de críticos (usuários com tipo 'critico')
$sqlCriticos = "
    SELECT 
        c.id_comentario, 
        c.conteudo, 
        c.data_comentario, 
        c.id_filme, 
        c.id_usuario, 
        f.nome_filme,
        u.nome_usuario,
        u.foto_perfil
    FROM comentario c
    INNER JOIN usuario u ON c.id_usuario = u.id_usuario
    LEFT JOIN filme f ON c.id_filme = f.id_filme
    WHERE LOWER(TRIM(u.tipo)) = 'critico'
    ORDER BY c.data_comentario DESC
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

$sql_notas = "SELECT id_filme, id_usuario, valor FROM nota";
$result_notas = $conexao->query($sql_notas);

$notas = [];
while ($nota = $result_notas->fetch_assoc()) {
    // Cria uma chave única com filme e usuário
    $notas[$nota['id_filme'] . '_' . $nota['id_usuario']] = $nota['valor'];
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

        <img src="../imagens/logo.png" alt="Logo do site" class="logo">

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
    <?php foreach ($top5 as $f): ?>
        <a href="set_filme.php?id=<?= $f['id_filme'] ?>">
            <img src="<?= $f['caminho_imagem'] ?>" 
                 alt="<?= htmlspecialchars($f['nome_filme']) ?>" 
                 style="width:150px; height:200px; border-radius:8px;">
        </a>
    <?php endforeach; ?>
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
    <?php
            $chave = $c['id_filme'] . '_' . $c['id_usuario'];
            if (isset($notas[$chave])):
    ?>
            <p class="rating"><strong>Nota atribuída:</strong> <?= $notas[$chave] ?></p>
        <?php else: ?>
            <p class="rating"><em>Esse usuário não atribuiu nota a esse filme.</em></p>
        <?php endif; ?>

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
        <img src="../imagens/Marca_IFSP_2015_Bri_H_4.png" alt="" style = "width: 100px;
    height: auto;" >
        <div class="redes">
            <a href="#"><img src="../imagens/instagram.png" alt="instagram"></a>
            <a href="#"><img src="../imagens/Youtube.png" alt="youtube"></a>
            <a href="#"><img src="../imagens/twitter.png" alt="twitter"></a>
        </div>
    </footer>
</body>
</html>
