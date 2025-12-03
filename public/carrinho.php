<?php
require_once('../backend/conection.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$qtd_carrinho = 0;
if(isset($_SESSION['carrinho'])) {
    foreach($_SESSION['carrinho'] as $qtd) {
        $qtd_carrinho += $qtd;
    }
}

$produtos_no_carrinho = [];
$total_pedido = 0;

if (isset($_SESSION['carrinho']) && count($_SESSION['carrinho']) > 0) {
    $ids = implode(',', array_keys($_SESSION['carrinho']));
    
    $sql = "SELECT * FROM produtos WHERE id IN ($ids)";
    $result = $conn->query($sql);
    
    while ($prod = $result->fetch_assoc()) {
        $produtos_no_carrinho[] = $prod;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Carrinho - Byte Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/carrinho.css">

</head>
<body>

    <header>
        <a href="dashboard.php" class="logo">
            <div class="logo-icon"><i class="fa-solid fa-utensils"></i></div>
            Byte Menu
        </a>
        <div class="header-right">
            <a href="#" class="cart-btn">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if($qtd_carrinho > 0): ?>
                    <span class="badge-count"><?= $qtd_carrinho ?></span>
                <?php endif; ?>
            </a>
            <div style="text-align:right; font-size:13px;">
                <strong><?= $_SESSION['usuario_nome'] ?></strong><br>
                <span style="color:#666;"><?= $_SESSION['usuario_matricula'] ?></span>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="top-actions">
            <a href="dashboard.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Continuar Comprando
            </a>
            <?php if(count($produtos_no_carrinho) > 0): ?>
                <a href="carrinho_acoes.php?acao=limpar" class="clear-cart">
                    <i class="fa-regular fa-trash-can"></i> Limpar Carrinho
                </a>
            <?php endif; ?>
        </div>

        <h2>Seu Carrinho</h2>

        <?php if(count($produtos_no_carrinho) > 0): ?>
            
            <?php foreach($produtos_no_carrinho as $prod): 
                $qtd = $_SESSION['carrinho'][$prod['id']];
                $subtotal = $prod['preco'] * $qtd;
                $total_pedido += $subtotal;

                $img = $prod['imagem_url'];
                if(strpos($img, 'http') === false) $img = "gestor/" . $img;
            ?>
                <div class="cart-item">
                    <img src="<?= $img ?>" class="item-img">
                    
                    <div class="item-info">
                        <span class="item-name"><?= $prod['nome'] ?></span>
                        <span class="item-desc"><?= $prod['descricao'] ?></span>
                        
                        <div class="qty-control">
                            <a href="carrinho_acoes.php?acao=diminuir&id=<?= $prod['id'] ?>" class="btn-qty">-</a>
                            <span><?= $qtd ?></span>
                            <a href="carrinho_acoes.php?acao=add&id=<?= $prod['id'] ?>" class="btn-qty">+</a>
                        </div>
                    </div>

                    <div class="item-price-actions">
                        <span class="price">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></span>
                        <a href="carrinho_acoes.php?acao=remover&id=<?= $prod['id'] ?>" class="btn-remove">
                            <i class="fa-regular fa-trash-can"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="summary-card">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>R$ <?= number_format($total_pedido, 2, ',', '.') ?></span>
                </div>
                <div class="total-row">
                    <span>Total</span>
                    <span>R$ <?= number_format($total_pedido, 2, ',', '.') ?></span>
                </div>

                <a href="checkout.php" class="btn-checkout">Finalizar Pedido</a>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <i class="fa-solid fa-basket-shopping"></i>
                <p>Seu carrinho está vazio.</p>
                <a href="dashboard.php" style="color:#2563eb; text-decoration:none; font-weight:600; margin-top:10px; display:block;">Ir para o cardápio</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>