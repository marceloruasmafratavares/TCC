<?php
require_once('../../backend/conection.php');

$nome = "Administrador";
$email = "admin@cantina.com";
$senha_clara = "123456";

$senha_hash = password_hash($senha_clara, PASSWORD_DEFAULT);

$conn->query("DELETE FROM gestores WHERE email = '$email'");

$sql = "INSERT INTO gestores (nome, email, senha) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

$stmt->bind_param("sss", $nome, $email, $senha_hash);

if ($stmt->execute()) {
    echo "<h1>Sucesso!</h1>";
    echo "<p>Gestor recriado com sucesso.</p>";
    echo "<hr>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Senha:</strong> $senha_clara</p>";
    echo "<br>";
    echo "<a href='gestor_login.php'>Clique aqui para fazer Login</a>";
} else {
    echo "<h1>Erro!</h1>";
    echo "Erro ao criar gestor: " . $conn->error;
}
?>