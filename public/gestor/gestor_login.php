<?php
require_once('../../backend/conection.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM gestores WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $gestor = $result->fetch_assoc();
        if (password_verify($senha, $gestor['senha'])) {
            $_SESSION['gestor_id'] = $gestor['id'];
            $_SESSION['gestor_nome'] = $gestor['nome'];
            header("Location: gestor_dashboard.php");
            exit;
        }
    }
    $erro = "Acesso negado.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Área do Gestor - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/gestor_login.css">
</head>
<body>
    <div class="login-card">
        <div class="logo"><i class="fa-solid fa-user-tie"></i></div>
        <h2>Área Administrativa</h2>
        <?php if(isset($erro)) echo "<p class='error'>$erro</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email administrativo" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Acessar Painel</button>
        </form>
    </div>
</body>
</html>