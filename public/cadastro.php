<?php
include('../backend/conection.php');

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql_check = "SELECT id FROM usuarios WHERE email = ? OR matricula = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $email, $matricula);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $mensagem = "Erro: Já existe um usuário com este email ou matrícula.";
        $tipo_mensagem = "error";
    } else {

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        

        $sql_insert = "INSERT INTO usuarios (nome, matricula, email, senha) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssss", $nome, $matricula, $email, $senha_hash);

        if ($stmt_insert->execute()) {
            $mensagem = "Conta criada com sucesso! Redirecionando para o login...";
            $tipo_mensagem = "success";
            header("refresh:2;url=index.php");
        } else {
            $mensagem = "Erro no sistema: " . $conn->error;
            $tipo_mensagem = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cantina - Cadastro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/cadastro.css">

</head>
<body>
    <div class="container">
        <div class="logo-circle">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2>Criar Conta</h2>
        <p class="subtitle">Cadastre-se para comprar na cantina</p>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" placeholder="Seu nome" required>
            </div>
            <div class="form-group">
                <label>Matrícula</label>
                <input type="text" name="matricula" placeholder="Ex: 2023001" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@escola.com.br" required>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Crie uma senha" required>
            </div>
            <button type="submit" class="btn-primary">Cadastrar</button>
        </form>

        <div class="message <?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>
        <div class="toggle-link">
            Já tem conta? <a href="index.php">Entrar</a>
        </div>
    </div>
</body>
</html>