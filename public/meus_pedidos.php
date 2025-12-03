<?php
require_once('../backend/conection.php'); 
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

$sql_pedidos = "SELECT * FROM pedidos WHERE aluno_id = $id_usuario ORDER BY id DESC";
$result_pedidos = $conn->query($sql_pedidos);

function getStatusBadge($status) {
    switch ($status) {
        case 'aguardando_pagamento':
            return ['classe' => 'badge-gray', 'icon' => 'fa-regular fa-clock', 'texto' => 'Pendente'];
        case 'pago':
            return ['classe' => 'badge-blue', 'icon' => 'fa-solid fa-check', 'texto' => 'Pago'];
        case 'em_preparo':
            return ['classe' => 'badge-yellow', 'icon' => 'fa-solid fa-fire-burner', 'texto' => 'Em Preparo'];
        case 'pronto':
            return ['classe' => 'badge-green', 'icon' => 'fa-solid fa-utensils', 'texto' => 'Pronto para Retirar'];
        case 'retirado':
            return ['classe' => 'badge-gray', 'icon' => 'fa-solid fa-check-double', 'texto' => 'Concluído'];
        case 'cancelado':
            return ['classe' => 'badge-red', 'icon' => 'fa-solid fa-xmark', 'texto' => 'Cancelado'];
        default:
            return ['classe' => 'badge-gray', 'icon' => 'fa-circle', 'texto' => $status];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Byte Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/meus_pedidos.css">    
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
            <a href="dashboard.php" class="menu-link">
                <i class="fa-solid fa-book-open"></i>
                <div class="link-text"><span class="link-title">Cardápio</span><span class="link-desc">Ver produtos disponíveis</span></div>
            </a>
            <a href="meus_pedidos.php" class="menu-link active">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <div class="link-text"><span class="link-title">Meus Pedidos</span><span class="link-desc">Histórico de compras</span></div>
            </a>
            <a href="perfil.php" class="menu-link">
                <i class="fa-solid fa-wallet"></i>
                <div class="link-text"><span class="link-title">Saldo</span><span class="link-desc">Gerenciar créditos</span></div>
            </a>
            <a href="controle_parental.php" class="menu-link">
                <i class="fa-solid fa-shield-halved"></i>
                <div class="link-text"><span class="link-title">Controle Parental</span><span class="link-desc">Configurações e limites</span></div>
            </a>
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
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-book-open"></i> Cardápio</a>
            <a href="meus_pedidos.php" class="nav-item active"><i class="fa-solid fa-clock-rotate-left"></i> Pedidos</a>
            <a href="perfil.php" class="nav-item"><i class="fa-solid fa-wallet"></i> Saldo</a>
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
                <div class="mini-info"><h4><?php echo $_SESSION['usuario_nome']; ?></h4><span><?php echo $_SESSION['usuario_matricula']; ?></span></div>
            </div>
        </div>
    </header>

    <div class="container">
        <h2>Histórico de Pedidos</h2>

        <?php if($result_pedidos->num_rows > 0): ?>
            <?php while($pedido = $result_pedidos->fetch_assoc()): 
                $status_info = getStatusBadge($pedido['status']);
                $data_formatada = date('d \d\e F \d\e Y \à\s H:i', strtotime($pedido['data_criacao']));
                
                $id_ped = $pedido['id'];
                $sql_itens = "SELECT i.*, p.nome 
                              FROM itens_do_pedido i 
                              JOIN produtos p ON i.produto_id = p.id 
                              WHERE i.pedido_id = $id_ped";
                $res_itens = $conn->query($sql_itens);
            ?>
            
            <div class="order-card">
                <div class="order-header">
                    <div class="order-id-group">
                        <h3>
                            Pedido #<?php echo $pedido['id']; ?>
                            <span class="status-badge <?php echo $status_info['classe']; ?>">
                                <i class="<?php echo $status_info['icon']; ?>"></i> <?php echo $status_info['texto']; ?>
                            </span>
                        </h3>
                        <span class="order-date"><?php echo $data_formatada; ?></span>
                    </div>
                    <div class="order-total-group">
                        <span class="total-label">Total</span>
                        <span class="total-value">R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="order-items">
                    <span style="font-size: 13px; color: #666; display: block; margin-bottom: 10px;">Itens do pedido:</span>
                    <?php while($item = $res_itens->fetch_assoc()): ?>
                        <div class="item-row">
                            <div>
                                <span class="item-qty"><?php echo $item['quantidade']; ?>x</span>
                                <?php echo $item['nome']; ?>
                            </div>
                            <span>R$ <?php echo number_format($item['preco_no_momento'] * $item['quantidade'], 2, ',', '.'); ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="order-footer">
                    Pagamento: <strong>Saldo Da Conta</strong>
                </div>
            </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-clipboard-list"></i>
                <p>Você ainda não fez nenhum pedido.</p>
                <a href="dashboard.php" style="color:#2563eb; text-decoration:none; font-weight:600; margin-top:10px; display:block;">Fazer meu primeiro pedido</a>
            </div>
        <?php endif; ?>

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