<?php
require_once('../backend/conection.php');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) == 0) {
    header("Location: dashboard.php");
    exit;
}

$ids = implode(',', array_keys($_SESSION['carrinho']));
$sql = "SELECT * FROM produtos WHERE id IN ($ids)";
$result = $conn->query($sql);

$produtos_checkout = [];
$total_pedido = 0;

while($prod = $result->fetch_assoc()) {
    $qtd = $_SESSION['carrinho'][$prod['id']];
    $prod['qtd_carrinho'] = $qtd;
    $prod['subtotal'] = $prod['preco'] * $qtd;
    $total_pedido += $prod['subtotal'];
    $produtos_checkout[] = $prod;
}

$saldo_atual = $_SESSION['usuario_saldo'];
$tem_saldo = ($saldo_atual >= $total_pedido);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Pedido - Byte Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/checkout.css">

</head>
<body>

    <header>
        <a href="carrinho.php" class="logo">
            <div class="logo-icon"><i class="fa-solid fa-utensils"></i></div>
            Byte Menu
        </a>
    </header>

    <div class="container">
        <a href="carrinho.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao Carrinho</a>
        <h2>Finalizar Pedido</h2>

        <form action="processar_pedido.php" method="POST" class="checkout-grid">
            
            <div>
                <div class="card">
                    <h3>Resumo do Pedido</h3>
                    <?php foreach($produtos_checkout as $prod): ?>
                        <div class="summary-item">
                            <span><?= $prod['qtd_carrinho'] ?>x <?= $prod['nome'] ?></span>
                            <span>R$ <?= number_format($prod['subtotal'], 2, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span>R$ <?= number_format($total_pedido, 2, ',', '.') ?></span>
                    </div>
                </div>

                <div class="card">
                    <h3>Método de Pagamento</h3>
                    
                    <div class="payment-option selected">
                        <div class="radio-circle"><div class="radio-dot"></div></div>
                        <i class="fa-solid fa-wallet"></i>
                        <div>
                            <strong>Saldo da Conta</strong><br>
                            <span style="font-size: 12px; color: #666;">
                                Disponível: R$ <?= number_format($saldo_atual, 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>

                    <div class="payment-option" style="opacity: 0.5; cursor: not-allowed;">
                        <div class="radio-circle"></div>
                        <i class="fa-regular fa-credit-card"></i>
                        <div><strong>Cartão de Crédito</strong><br><span style="font-size:12px;">Indisponível</span></div>
                    </div>
                    <div class="payment-option" style="opacity: 0.5; cursor: not-allowed;">
                        <div class="radio-circle"></div>
                        <i class="fa-brands fa-pix"></i>
                        <div><strong>PIX</strong><br><span style="font-size:12px;">Indisponível</span></div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Informações de Entrega</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Aluno</span>
                        <div class="info-val"><?= $_SESSION['usuario_nome'] ?></div>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Matrícula</span>
                        <div class="info-val"><?= $_SESSION['usuario_matricula'] ?></div>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Local de Retirada</span>
                        <div class="info-val">Cantina - Balcão Principal</div>
                    </div>

                    <?php if($tem_saldo): ?>
                        <button type="submit" class="btn-confirm">
                            Confirmar Pedido - R$ <?= number_format($total_pedido, 2, ',', '.') ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-confirm btn-disabled" disabled>
                            Saldo Insuficiente
                        </button>
                        <div class="error-msg">
                            Você precisa de mais R$ <?= number_format($total_pedido - $saldo_atual, 2, ',', '.') ?>.<br>
                            <a href="perfil.php" style="color: #991b1b; font-weight: bold;">Clique aqui para recarregar</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </form>
    </div>

</body>
</html>