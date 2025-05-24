<?php
session_start();


require_once '../config/bd.php';

$mensagem = '';
$tipo_mensagem = '';

// Verifica o status na URL
if(isset($_GET['status']) && $_GET['status'] === 'success') {
    $evento_id = isset($_GET['id']) ? $_GET['id'] : '';
    $mensagem = "Evento cadastrado com sucesso! Link do evento: evento.php?id=" . $evento_id;
    $tipo_mensagem = "success";
}

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $data = $_POST['data'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_termino = $_POST['hora_termino'];
    $descricao = $_POST['descricao'];
    
    // Processa o upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $arquivo_tmp = $_FILES['imagem']['tmp_name'];
        $nome = $_FILES['imagem']['name'];
        
        // Gera um nome único para o arquivo
        $extensao = pathinfo($nome, PATHINFO_EXTENSION);
        $novo_nome = uniqid() . '.' . $extensao;
        
        // Verifica e redimensiona a imagem
        list($largura_original, $altura_original) = getimagesize($arquivo_tmp);
        
        $largura_nova = 1080;
        $altura_nova = 1920;
        
        $imagem_final = imagecreatetruecolor($largura_nova, $altura_nova);
        
        switch($extensao) {
            case 'jpg':
            case 'jpeg':
                $imagem_original = imagecreatefromjpeg($arquivo_tmp);
                break;
            case 'png':
                $imagem_original = imagecreatefrompng($arquivo_tmp);
                break;
            default:
                die('Formato de imagem não suportado');
        }
        
        // Redimensiona
        imagecopyresampled(
            $imagem_final, $imagem_original,
            0, 0, 0, 0,
            $largura_nova, $altura_nova,
            $largura_original, $altura_original
        );
        
        // Salva a imagem
        $caminho_imagem = "../upload/" . $novo_nome;
        imagejpeg($imagem_final, $caminho_imagem, 90);
        
        // Libera a memória
        imagedestroy($imagem_original);
        imagedestroy($imagem_final);
        
        try {
            $sql = "INSERT INTO eventos (titulo, imagem, data, hora_inicio, hora_termino, descricao) 
                    VALUES (:titulo, :imagem, :data, :hora_inicio, :hora_termino, :descricao)";
            $stmt = $conn->prepare($sql);
            
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':imagem', $novo_nome);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_termino', $hora_termino);
            $stmt->bindParam(':descricao', $descricao);
            
            $stmt->execute();
            
            $evento_id = $conn->lastInsertId();
            
            // Redireciona com mensagem de sucesso
            header("Location: addevento.php?status=success&id=" . $evento_id);
            exit();
            
        } catch(PDOException $e) {
            header("Location: addevento.php?status=error");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Evento</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea {
            height: 150px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            border-color: #d6e9c6;
            color: #3c763d;
        }
        .alert-error {
            background-color: #f2dede;
            border-color: #ebccd1;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Adicionar Novo Evento</h2>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título do Evento:</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label for="imagem">Imagem (1080x1920):</label>
                <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png" required>
            </div>

            <div class="form-group">
                <label for="data">Data:</label>
                <input type="date" id="data" name="data" required>
            </div>

            <div class="form-group">
                <label for="hora_inicio">Hora de Início:</label>
                <input type="time" id="hora_inicio" name="hora_inicio" required>
            </div>

            <div class="form-group">
                <label for="hora_termino">Hora de Término:</label>
                <input type="time" id="hora_termino" name="hora_termino" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" required></textarea>
            </div>

            <button type="submit">Cadastrar Evento</button>
        </form>
    </div>

    <script>
    // Limpa a mensagem após 3 segundos
    setTimeout(function() {
        var alertDiv = document.querySelector('.alert');
        if (alertDiv) {
            alertDiv.style.display = 'none';
            // Limpa a URL removendo os parâmetros GET
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }, 3000);

    // Limpa a URL imediatamente se houver parâmetros
    if(window.location.search) {
        setTimeout(function() {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 100);
    }
    </script>
</body>
</html>
