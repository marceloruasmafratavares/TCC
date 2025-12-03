<?php
require_once('../backend/conection.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recarregar_saldo'])) {
    $valor = str_replace(',', '.', $_POST['valor']);
    $valor = floatval($valor);

    if ($valor > 0) {
        $id_usuario = $_SESSION['usuario_id'];

        $stmt = $conn->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
        $stmt->bind_param("di", $valor, $id_usuario);

        if ($stmt->execute()) {

            $_SESSION['usuario_saldo'] += $valor;
            
            $mensagem = "Recarga de R$ " . number_format($valor, 2, ',', '.') . " realizada com sucesso!";
            $tipo_msg = "success";
        } else {
            $mensagem = "Erro ao processar recarga.";
            $tipo_msg = "error";
        }
    } else {
        $mensagem = "Digite um valor válido maior que zero.";
        $tipo_msg = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Byte Menu - Perfil e Saldo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/perfil.css">
<body>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
    
    <header class="top-bar">
        <div class="header-left">
            <div class="menu-toggle" onclick="window.location.href='dashboard.php'">
                <i class="fa-solid fa-arrow-left"></i> </div>
            <a href="dashboard.php" class="logo">
                <div class="logo-icon"><i class="fa-solid fa-utensils"></i></div>
                Byte Menu
            </a>
        </div>
        
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-book-open"></i> Cardápio</a>
            <a href="meus_pedidos.php" class="nav-item"><i class="fa-solid fa-clock-rotate-left"></i> Pedidos</a>
            <a href="perfil.php" class="nav-item active"><i class="fa-solid fa-wallet"></i> Saldo</a>
        </nav>

        <div class="header-right">
            <a href="carrinho.php" class="icon-btn">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php 
                $qtd_carrinho = 0;
                if(isset($_SESSION['carrinho'])) foreach($_SESSION['carrinho'] as $q) $qtd_carrinho += $q;
                if($qtd_carrinho > 0) echo "<span class='badge-count'>$qtd_carrinho</span>";
                ?>
            </a>

            <div class="user-mini-profile">
                <div class="mini-avatar"><i class="fa-regular fa-user"></i></div>
                <div class="mini-info">
                    <h4><?php echo $_SESSION['usuario_nome']; ?></h4>
                    <span><?php echo $_SESSION['usuario_matricula']; ?></span>
                </div>
            </div>
        </div>
    </header>


    <div class="container">
        <h2 class="page-title">Meu Perfil</h2>

        <?php if($mensagem): ?>
            <div class="alert <?php echo $tipo_msg; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            
            <div class="card-box">
                <div class="profile-header">
                    <div class="big-avatar"><i class="fa-regular fa-user"></i></div>
                    <h3>Informações Pessoais</h3>
                </div>

                <div class="info-group">
                    <span class="info-label">Nome Completo</span>
                    <div class="info-value"><?php echo $_SESSION['usuario_nome']; ?></div>
                </div>

                <div class="info-group">
                    <span class="info-label">Email</span>
                    <div class="info-value">
                        <?php 
                        $id = $_SESSION['usuario_id'];
                        $res = $conn->query("SELECT email FROM usuarios WHERE id = $id");
                        $u = $res->fetch_assoc();
                        echo $u['email'];
                        ?>
                    </div>
                </div>

                <div class="info-group">
                    <span class="info-label">Matrícula</span>
                    <div class="info-value"><?php echo $_SESSION['usuario_matricula']; ?></div>
                </div>

                <div class="info-group">
                    <span class="info-label">Curso</span>
                    <div class="info-value">Técnico em Desenvolvimento de Sistemas</div>
                    </div>
            </div>


            <div class="right-column">
                
                <div class="card-box">
                    <div class="balance-header">
                        <div class="balance-icon"><i class="fa-solid fa-wallet"></i></div>
                        Saldo Disponível
                    </div>
                    <div class="balance-display">
                        <span class="balance-label">Saldo atual</span>
                        <span class="balance-amount">R$ <?php echo number_format($_SESSION['usuario_saldo'], 2, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="card-box">
                    <div class="balance-header">
                        <div class="balance-icon" style="color: #2563eb; background: #eff6ff;"><i class="fa-regular fa-credit-card"></i></div>
                        Recarregar Saldo
                    </div>

                    <form method="POST" class="recharge-form">
                        <label>Valor da Recarga</label>
                        
                        <div class="input-group">
                            <input type="number" step="0.01" name="valor" id="valorInput" class="input-money" placeholder="R$ 0,00" required>
                            <button type="submit" name="recarregar_saldo" class="btn-recharge">
                                <i class="fa-solid fa-bolt"></i> Recarregar
                            </button>
                        </div>

                        <label>Valores Rápidos</label>
                        <div class="quick-values">
                            <button type="button" class="btn-quick" onclick="setValor(10)">R$ 10,00</button>
                            <button type="button" class="btn-quick" onclick="setValor(20)">R$ 20,00</button>
                            <button type="button" class="btn-quick" onclick="setValor(50)">R$ 50,00</button>
                            <button type="button" class="btn-quick" onclick="setValor(100)">R$ 100,00</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function setValor(valor) {
    document.getElementById('valorInput').value = valor;
}
    </script>

</body>
</html>