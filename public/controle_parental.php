<?php
require_once('../backend/conection.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$mensagem = "";
$tipo_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = $_POST['pin_parental'];
    $limite_diario = floatval($_POST['limite_diario']);
    $limite_pedido = floatval($_POST['limite_por_pedido']);
    
    $restricao_horario = isset($_POST['restricao_horario']) ? 1 : 0;
    $aprovacao_pais = isset($_POST['aprovacao_pais']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE usuarios SET pin_parental=?, limite_diario=?, limite_por_pedido=?, restricao_horario=?, aprovacao_pais=? WHERE id=?");
    $stmt->bind_param("sddiii", $pin, $limite_diario, $limite_pedido, $restricao_horario, $aprovacao_pais, $id_usuario);

    if ($stmt->execute()) {
        $mensagem = "Configurações salvas com sucesso!";
        $tipo_msg = "success";
    } else {
        $mensagem = "Erro ao salvar.";
        $tipo_msg = "error";
    }
}

$res_user = $conn->query("SELECT * FROM usuarios WHERE id = $id_usuario");
$user = $res_user->fetch_assoc();

$sql_gasto = "SELECT SUM(total) as total FROM pedidos WHERE aluno_id = $id_usuario AND DATE(data_criacao) = CURDATE() AND status != 'cancelado'";
$res_gasto = $conn->query($sql_gasto);
$gasto_hoje = $res_gasto->fetch_assoc()['total'] ?? 0;

$porcentagem = 0;
if ($user['limite_diario'] > 0) {
    $porcentagem = ($gasto_hoje / $user['limite_diario']) * 100;
    if($porcentagem > 100) $porcentagem = 100;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle Parental - Byte Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/controle_parental.css">

</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon"><i class="fa-solid fa-utensils"></i></div>
                Byte Menu
            </div>
            <div class="close-btn" onclick="toggleMenu()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        <div class="user-card">
            <div class="user-card-header">
                <i class="fa-regular fa-user user-card-avatar"></i>
                <div class="user-card-info">
                    <h4><?php echo $_SESSION['usuario_nome']; ?></h4>
                    <span><?php echo $_SESSION['usuario_matricula']; ?></span>
                </div>
            </div>
            <div class="divider"></div>
            <div class="user-balance">
                <p>Saldo disponível</p>
                <strong>R$ <?php echo number_format($_SESSION['usuario_saldo'], 2, ',', '.'); ?></strong>
            </div>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-link"><i class="fa-solid fa-book-open"></i> <div class="link-text"><span class="link-title">Cardápio</span></div></a>
            <a href="meus_pedidos.php" class="menu-link"><i class="fa-solid fa-clock-rotate-left"></i> <div class="link-text"><span class="link-title">Meus Pedidos</span></div></a>
            <a href="perfil.php" class="menu-link"><i class="fa-solid fa-wallet"></i> <div class="link-text"><span class="link-title">Saldo</span></div></a>
            <a href="controle_parental.php" class="menu-link active"><i class="fa-solid fa-shield-halved"></i> <div class="link-text"><span class="link-title">Controle Parental</span><span class="link-desc">Configurações e limites</span></div></a>
        </nav>
        <a href="index.php" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i> Sair do sistema</a>
    </aside>

    <header class="top-bar">
        <div class="header-left">
            <div class="menu-toggle" onclick="toggleMenu()"><i class="fa-solid fa-bars"></i></div>
            <a href="dashboard.php" class="logo">
                <div class="logo-icon"><i class="fa-solid fa-utensils"></i></div>
                Byte Menu
            </a>
        </div>
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
                <div class="mini-info"><h4><?php echo $_SESSION['usuario_nome']; ?></h4><span><?php echo $_SESSION['usuario_matricula']; ?></span></div>
            </div>
        </div>
    </header>

    <div class="container">
        
        <div class="page-header-custom">
            <div class="header-icon-box"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="header-texts">
                <h2>Controle Parental</h2>
                <p>Gerencie limites e restrições de compras</p>
            </div>
        </div>

        <?php if($mensagem): ?>
            <div class="alert <?php echo $tipo_msg; ?>"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="control-grid">
                
                <div class="control-card">
                    <div class="card-title"><i class="fa-solid fa-lock"></i> Segurança</div>
                    <label class="input-label">Definir PIN Parental</label>
                    <input type="text" name="pin_parental" class="input-box" placeholder="Digite 4-6 dígitos" maxlength="6" value="<?php echo $user['pin_parental']; ?>">
                    <p class="helper-text">Este PIN será solicitado para alterar configurações.</p>
                </div>

                <div class="control-card">
                    <div class="card-title"><i class="fa-solid fa-dollar-sign"></i> Limite Diário</div>
                    <label class="input-label">Valor máximo por dia</label>
                    <input type="number" step="0.01" name="limite_diario" class="input-box" value="<?php echo $user['limite_diario']; ?>">
                    <p class="helper-text">Limite total de compras permitidas por dia</p>

                    <div class="progress-container">
                        <div class="progress-text">
                            <span>Hoje: R$ <?php echo number_format($gasto_hoje, 2, ',', '.'); ?></span>
                            <span>Max: R$ <?php echo number_format($user['limite_diario'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: <?php echo $porcentagem; ?>%;"></div>
                        </div>
                    </div>
                </div>

                <div class="control-card">
                    <div class="card-title"><i class="fa-solid fa-cart-shopping"></i> Limite por Compra</div>
                    <label class="input-label">Valor máximo por pedido</label>
                    <input type="number" step="0.01" name="limite_por_pedido" class="input-box" value="<?php echo $user['limite_por_pedido']; ?>">
                    <p class="helper-text">Valor máximo permitido em um único pedido</p>

                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Aprovação dos pais</span>
                            <span class="toggle-desc">Pedidos acima do limite requerem aprovação</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="aprovacao_pais" <?php echo ($user['aprovacao_pais'] == 1) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="control-card">
                    <div class="card-title"><i class="fa-regular fa-clock"></i> Horários Permitidos</div>
                    
                    <div class="toggle-row" style="margin-top: 20px;">
                        <div>
                            <span class="toggle-label">Restringir horário de compras</span>
                            <span class="toggle-desc">Definir período permitido (Ex: Intervalo)</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="restricao_horario" <?php echo ($user['restricao_horario'] == 1) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <p class="helper-text" style="margin-top: 15px;">
                        <i class="fa-solid fa-circle-info"></i> Quando ativado, compras só serão permitidas durante os intervalos cadastrados.
                    </p>
                </div>
            </div>

            <div class="save-footer">
                <div class="save-text">
                    <h4>Salvar Configurações</h4>
                    <p>As configurações serão aplicadas imediatamente.</p>
                </div>
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-shield-halved"></i> Salvar
                </button>
            </div>
        </form>

    </div>

    <script>
        function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
}
    </script>
</body>
</html>