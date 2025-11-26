<?php
include '../conection/conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="../css/adm.css">
</head>
<body>

<header class="top-bar">
    <div class="icons">

        <!-- ÍCONE HOME BRANCO -->
        <a href="../pages/home.php" class="icon-svg">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                <path d="M3 10L12 3L21 10V21H14V14H10V21H3V10Z"/>
            </svg>
        </dclass=>
        </a>


    </div>
</header>

<main class="container">

    <section class="left-box">
        <h2>ENCONTRAR USUÁRIO</h2>

        <form action="adm.php" method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Usuário">
        </form>

        <div class="user-list">
            <?php
                if (isset($_GET['search'])) {
                    $con = conecta();
                    $search = mysqli_real_escape_string($con, $_GET['search']);

                    $sql = "SELECT * FROM usuario WHERE nome_usuario LIKE '%$search%'";
                    $res = mysqli_query($con, $sql);

                    while ($row = mysqli_fetch_assoc($res)) {
                        echo "
                        <div class='user-item'>
                            <div class='icon'></div>
                            <span class='name'>".$row['nome_usuario']."</span>
                            <a class='profile'>acessar perfil</a>
                        </div>";
                    }
                    desconecta($con);
                }
            ?>
        </div>
    </section>

    <section class="right-box">
        <button onclick="location.href='adicionar_filme.php'">ADICIONAR FILME</button>
        <button onclick="location.href='remover_filme.php'">REMOVER FILME</button>
    </section>

</main>

</body>
</html>
