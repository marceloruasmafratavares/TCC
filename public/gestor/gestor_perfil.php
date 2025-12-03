<?php
require_once('../../backend/conection.php');
session_start();

if (!isset($_SESSION['gestor_id'])) { header("Location: gestor_login.php"); exit; }

$mensagem = "";
$tipo_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];
    $id_gestor = $_SESSION['gestor_id'];

    if (!empty($nova_senha) && $nova_senha !== $confirma_senha) {
        $mensagem = "As senhas não coincidem!";
        $tipo_msg = "error";
    } else {
        if (empty($nova_senha)) {
            $sql = "UPDATE gestores SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $novo_email, $id_gestor);
        } else {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql = "UPDATE gestores SET email = ?, senha = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $novo_email, $senha_hash, $id_gestor);
        }

        if ($stmt->execute()) {
            $mensagem = "Dados atualizados com sucesso!";
            $tipo_msg = "success";
            $_SESSION['gestor_nome'] = "Gestor (Atualizado)"; // Opcional
        } else {
            $mensagem = "Erro ao atualizar: " . $conn->error;
            $tipo_msg = "error";
        }
    }
}

$sql_atual = "SELECT email FROM gestores WHERE id = " . $_SESSION['gestor_id'];
$res_atual = $conn->query($sql_atual);
$dados_atuais = $res_atual->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Gestor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/gestor_perfil.css">
    
</head>
<body>
    <aside>
        <div class="brand"><i class="fa-solid fa-utensils"></i> Gestor</div>
        <nav class="menu">
            <a href="gestor_dashboard.php"><i class="fa-solid fa-list-check"></i> Pedidos</a>
            <a href="gestor_produtos.php"><i class="fa-solid fa-box"></i> Produtos</a>
            <a href="gestor_perfil.php" class="active"><i class="fa-solid fa-user-gear"></i> Perfil</a>
            <a href="gestor_login.php" style="margin-top: auto; color: #ef4444;"><i class="fa-solid fa-power-off"></i> Sair</a>
        </nav>
    </aside>

    <main>
        <div class="card">
            <h2><i class="fa-solid fa-lock"></i> Alterar Acesso</h2>
            
            <?php if($mensagem): ?>
                <div class="msg <?php echo $tipo_msg; ?>"><?php echo $mensagem; ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Email de Acesso</label>
                <input type="email" name="email" value="<?php echo $dados_atuais['email']; ?>" required>

                <label>Nova Senha</label>
                <input type="password" name="nova_senha" placeholder="Deixe em branco para manter a atual">

                <label>Confirmar Nova Senha</label>
                <input type="password" name="confirma_senha" placeholder="Repita a senha">

                <button type="submit">Salvar Alterações</button>
            </form>
        </div>
    </main>
</body>
</html>