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
            <input type="text" name="search" placeholder="email do usuario">
        </form>

        <div class="user-list">
            <?php
                if (isset($_GET['search'])) {
                    $con = conecta();
                    $search = mysqli_real_escape_string($con, $_GET['search']);

                    $sql = "SELECT * FROM usuario WHERE email LIKE '%$search%'";
                    $res = mysqli_query($con, $sql);

                    while ($row = mysqli_fetch_assoc($res)) {
                        echo "
                        <div class='user-item'>
                            <div class='icon'></div>
                            <span class='name'>".$row['email']."</span>
                            <a class='profile' href='set_id_adm.php?id=".$row['id_usuario']."'>acessar perfil</a>
                        </div>";
                    }
                    desconecta($con);
                }
            ?>
        </div>

    </section>

   <section class="middle-box">
    <h2>ÚLTIMOS 20 USUÁRIOS</h2>

    <div class="recent-users">
        <?php
            $con = conecta();
            $sqlLast = "SELECT id_usuario, nome_usuario, email, tipo FROM usuario ORDER BY id_usuario DESC LIMIT 20";
            $resLast = mysqli_query($con, $sqlLast);

            while ($u = mysqli_fetch_assoc($resLast)) {
                echo "
                <div class='user-item'>
                    <span class='name'>".$u['nome_usuario']."</span>
                    <span class='email'>".$u['email']."</span>
                    <span class='tipo'>Tipo: ".$u['tipo']."</span>
                    <a class='profile' href='set_id_adm.php?id=".$u['id_usuario']."'>acessar perfil</a>
                </div>";
            }
            desconecta($con);
        ?>
    </div>
</section>


    <section class="right-box">
        <button onclick="location.href='adicionar_filme.php'">ADICIONAR FILME</button>
        <button onclick="location.href='excluir_filme.php'">REMOVER FILME</button>
    </section>

</main>



</body>
</html>
