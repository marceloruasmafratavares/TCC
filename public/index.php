<?php
include('../backend/conection.php');
session_start();

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, matricula, senha, saldo FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_saldo'] = $usuario['saldo'];
            $_SESSION['usuario_matricula'] = $usuario['matricula'];

            header("Location: dashboard.php");
            exit;
        } else {
            $mensagem = "Senha incorreta.";
            $tipo_mensagem = "error";
        }
    } else {
        $mensagem = "Usuário não encontrado.";
        $tipo_mensagem = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cantina - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/index.css">
</head>
<body>
    <div class="container">
        <div class="logo-circle">
            <i class="fa-solid fa-utensils"></i>
        </div>
        <h2>Cantina Escolar</h2>
        <p class="subtitle">Acesse para fazer seu pedido</p>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@sesi.com" required>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="Sua senha" required>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <div class="message <?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>

        <div class="toggle-link">
            Não tem cadastro? <a href="cadastro.php">Cadastre-se</a>
        </div>
    </div>
</body>
</html>