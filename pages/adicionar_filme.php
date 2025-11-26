<?php
session_start();


include('../conection/conexao.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome_filme'];
    $data = $_POST['data_lancamento'];
    $genero = $_POST['genero'];
    $trailer = $_POST['trailer'];
    $caminho = $_POST['caminho_imagem'];
    $tomatoes = $_POST['media_tomatoes'];
    $imbd = $_POST['media_imbd'];
    $geral = $_POST['media_geral'];
    $sinopse = $_POST['sinopse'];

    $sql = "INSERT INTO filme (nome_filme, data_lancamento, genero, trailer, caminho_imagem, media_tomatoes, media_imbd, media_geral, sinopse) 
            VALUES ('$nome', '$data', '$genero', '$trailer', '$caminho', '$tomatoes', '$imbd', '$geral', '$sinopse')";
    
    if (mysqli_query($conn, $sql)) {
        $mensagem = "Filme adicionado com sucesso!";
    } else {
        $mensagem = "Erro ao adicionar: " . mysqli_error($conn);
    }
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

<div class="top-bar"></div>

<div class="container">
    <div class="form-box">
        <h2>Adicionar Filme</h2>

        <?php if (!empty($mensagem)) echo "<p class='msg'>$mensagem</p>"; ?>

        <form action="" method="post">

            <input type="text" name="nome_filme" placeholder="Nome do Filme" required>

            <label>Data de Lançamento:</label>
            <input type="date" name="data_lancamento" required>

            <input type="text" name="genero" placeholder="Gênero" required>

            <input type="text" name="trailer" placeholder="Link do Trailer (URL)">

            <input type="text" name="caminho_imagem" placeholder="URL da Imagem do Filme">

            <input type="number" step="0.01" name="media_tomatoes" placeholder="Média Rotten Tomatoes" required>

            <input type="number" step="0.01" name="media_imbd" placeholder="Média IMDB" required>

            <input type="number" step="0.01" name="media_geral" placeholder="Média Geral" required>

            <textarea name="sinopse" rows="5" placeholder="Sinopse do Filme" required></textarea>

            <button type="submit">Salvar Filme</button>
        </form>
    </div>
</div>

</body>
</html>

