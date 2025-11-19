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
                $_SESSION['filme_nome'] = $melhor['nome_filme'];
                $_SESSION['filme_data_lancamento'] = $melhor['data_lancamento'];
                $_SESSION['filme_genero'] = $melhor['genero'];
                $_SESSION['filme_trailer'] = $melhor['trailer'];
                $_SESSION['filme_imagem'] = $melhor['caminho_imagem'];
                $_SESSION['filme_media_tomatoes'] = $melhor['media_tomatoes'];
                $_SESSION['filme_media_imbd'] = $melhor['media_imbd'];
                $_SESSION['filme_media_geral'] = $melhor['media_geral'];
                $_SESSION['filme_sinopse'] = $melhor['sinopse'];

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
SELECT u.nome_usuario, u.foto_perfil, f.nome_filme, c.conteudo, f.media_geral
FROM comentario c
JOIN usuario u ON c.id_usuario = u.id_usuario
JOIN filme f ON c.id_filme = f.id_filme
WHERE u.tipo = 'critico'
ORDER BY c.data_comentario DESC
LIMIT 3";
$comentariosCriticos = mysqli_query($conexao, $sqlCriticos);

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
    <!-- BARRA DE PESQUISA -->
    <div class="search-bar">
        <input type="text" placeholder="Pesquisar...">
    </div>

    <!-- LOGO CENTRAL -->
    <div class="logo">
        <img src="../imagens/logo.png" alt="Logo">
    </div>

    <!-- MENU DIREITA: ADM | AJUDA + ÍCONE -->
    <nav class="right-menu">
        <a href="adm.php">Adm</a>
        <span class="divider">|</span>
        <a href="#">Ajuda</a>

        <!-- Ícone de perfil -->
        <a href="../pages/usuario.php" class="icon-svg">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="#FFFFFF" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 12c2.8 0 5-2.2 5-5s-2.2-5-5-5-5 2.2-5 5 2.2 5 5 5zm0 2c-4.4 0-8 3.6-8 8h16c0-4.4-3.6-8-8-8z"/>
    </svg>
</a>


    </nav>
</header>


  



    <main>
        <section class="top-filmes">
            <h2>✧ Top 5 no Movie Reviews</h2>
            <div class="retangulos-top5">
                <div class="retangulo"></div>
                <div class="retangulo"></div>
                <div class="retangulo"></div>
                <div class="retangulo"></div>
                <div class="retangulo"></div>
            </div>

        </section>

        <section class="opinioes-criticos">
            <h2>✧ Opinião dos críticos</h2>
            <section class="critics-section">
                <div class="critics-container">
                    <!-- Aqui você pode iterar $comentariosCriticos se quiser -->
                    <div class="critic-card">
                        <div class="critic-profile">
                            <div class="profile-circle"></div>
                            <div class="star">★</div>
                        </div>
                        <div class="critic-text">
                            <p>Lorem ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <p class="rating">NOTA: 0.3</p>
                        </div>
                    </div>
                    <div class="critic-card">
                        <div class="critic-profile">
                            <div class="profile-circle"></div>
                            <div class="star">★</div>
                        </div>
                        <div class="critic-text">
                            <p>Lorem ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <p class="rating">NOTA: 0.1</p>
                        </div>
                    </div>
                    <div class="critic-card">
                        <div class="critic-profile">
                            <div class="profile-circle"></div>
                            <div class="star">★</div>
                        </div>
                        <div class="critic-text">
                            <p>Lorem ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <p class="rating">NOTA: 0.1</p>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <section class="sua-review">
            <h2>✧ Dê sua review</h2>

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
