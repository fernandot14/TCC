<?php
session_start();

include('../conection/conexao.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $con = conecta();

    $nome = $_POST['nome_filme'];
    $data = $_POST['data_lancamento'];
    $genero = $_POST['genero'];
    $trailer = $_POST['trailer'];
    $tomatoes = $_POST['media_tomatoes'];
    $imbd = $_POST['media_imbd'];
    $sinopse = $_POST['sinopse'];

    // ======== PROCESSAR A IMAGEM =========
    $pastaDestino = "../imagens/";
    $imagemNome = basename($_FILES["imagem_filme"]["name"]);
    $caminhoCompleto = $pastaDestino . $imagemNome;

    // mover para pasta ../imagens
    if (move_uploaded_file($_FILES["imagem_filme"]["tmp_name"], $caminhoCompleto)) {

        // salvar caminho no banco
        $sql = "INSERT INTO filme 
                (nome_filme, data_lancamento, genero, trailer, caminho_imagem, media_tomatoes, media_imbd, sinopse) 
                VALUES 
                ('$nome', '$data', '$genero', '$trailer', '$caminhoCompleto', '$tomatoes', '$imbd', '$sinopse')";

        if (mysqli_query($con, $sql)) {
            $mensagem = "Filme adicionado com sucesso!";
        } else {
            $mensagem = "Erro ao adicionar ao banco: " . mysqli_error($con);
        }

    } else {
        $mensagem = "Erro ao enviar a imagem.";
    }

    desconecta($con);
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Filme</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #e6e6e6;
            font-family: Arial, sans-serif;
        }

        .top-bar {
            width: 100%;
            height: 60px;
            background-color: #1c1c1c;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-right: 30px;
        }

        .container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .form-box {
            background-color: #d9d9d9;
            border: 1px solid black;
            border-radius: 15px;
            padding: 30px;
            width: 450px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #555;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            border: 1px solid black;
            background-color: transparent;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        button:hover {
            background-color: #cfcfcf;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
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
    <div class="form-box">
    <h2>Adicionar Filme</h2>

    <?php if (!empty($mensagem)) echo "<p class='msg'>$mensagem</p>"; ?>

    <form action="" method="post" enctype="multipart/form-data">

        <input type="text" name="nome_filme" placeholder="Nome do Filme" required>

        <label>Data de Lançamento:</label>
        <input type="date" name="data_lancamento" required>

        <input type="text" name="genero" placeholder="Gênero" required>

        <input type="text" name="trailer" placeholder="Link do Trailer (URL)">

        <label>Enviar Imagem do Filme:</label>
        <input type="file" name="imagem_filme" required>

        <input type="number" step="0.01" name="media_tomatoes" placeholder="Média Rotten Tomatoes" required>

        <input type="number" step="0.01" name="media_imbd" placeholder="Média IMDB" required>

        <textarea name="sinopse" rows="5" placeholder="Sinopse do Filme" required></textarea>

        <button type="submit">Salvar Filme</button>
    </form>

    </div>
</div>

</body>
</html>

