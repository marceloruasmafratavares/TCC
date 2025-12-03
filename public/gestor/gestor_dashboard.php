<?php
require_once('../../backend/conection.php');
session_start();

if (!isset($_SESSION['gestor_id'])) { header("Location: gestor_login.php"); exit; }

if (isset($_GET['acao']) && isset($_GET['id'])) {
    $id_pedido = intval($_GET['id']);
    $novo_status = $_GET['acao']; 

    if ($novo_status == 'cancelado') {
        $busca = $conn->query("SELECT aluno_id, total, status FROM pedidos WHERE id = $id_pedido");
        $dados_pedido = $busca->fetch_assoc();

        if ($dados_pedido && $dados_pedido['status'] != 'cancelado') {
            $id_aluno = $dados_pedido['aluno_id'];
            $valor_estorno = $dados_pedido['total'];

            $conn->query("UPDATE usuarios SET saldo = saldo + $valor_estorno WHERE id = $id_aluno");
            
        }
    }
    
    $conn->query("UPDATE pedidos SET status = '$novo_status' WHERE id = $id_pedido");
    header("Location: gestor_dashboard.php");
    exit;
}

$sql_count = "SELECT COUNT(*) as qtd FROM pedidos WHERE DATE(data_criacao) = CURDATE()";
$res_count = $conn->query($sql_count);
$pedidos_hoje = $res_count->fetch_assoc()['qtd'];

$sql_sum = "SELECT SUM(total) as total FROM pedidos WHERE DATE(data_criacao) = CURDATE() AND status != 'cancelado'";
$res_sum = $conn->query($sql_sum);
$row_sum = $res_sum->fetch_assoc();
$faturamento_hoje = $row_sum['total'] ? $row_sum['total'] : 0;

$sql = "SELECT p.*, u.nome as nome_aluno, u.matricula 
        FROM pedidos p 
        JOIN usuarios u ON p.aluno_id = u.id 
        ORDER BY p.data_criacao DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Pedidos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/gestor_dashboard.css">
</head>
<body>
    <aside>
        <div class="brand"><i class="fa-solid fa-utensils"></i> Gestor</div>
        <nav class="menu">
            <a href="gestor_dashboard.php" class="active"><i class="fa-solid fa-list-check"></i> Pedidos</a>
            <a href="gestor_produtos.php"><i class="fa-solid fa-box"></i> Produtos</a>
            <a href="gestor_perfil.php"><i class="fa-solid fa-user-gear"></i> Perfil</a>
            <a href="gestor_login.php" style="margin-top: auto; color: #ef4444;"><i class="fa-solid fa-power-off"></i> Sair</a>
        </nav>
    </aside>

    <main>
        <h2>Painel de Controle</h2>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Pedidos Hoje</span>
                <span class="stat-value"><?php echo $pedidos_hoje; ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Faturamento Hoje</span>
                <span class="stat-value">R$ <?php echo number_format($faturamento_hoje, 2, ',', '.'); ?></span>
            </div>
        </div>

        <h3>Pedidos Recentes</h3>
        
        <?php if($result->num_rows > 0): ?>
            <?php while($pedido = $result->fetch_assoc()): ?>
                <div class="order-card status-<?= $pedido['status'] ?>">
                    <div class="order-info">
                        <h4>Pedido #<?= $pedido['id'] ?> - <?= $pedido['nome_aluno'] ?></h4>
                        <span>Status: <strong><?= strtoupper(str_replace('_', ' ', $pedido['status'])) ?></strong></span>
                    </div>
                    
                    <div style="display:flex; align-items:center;">
                        <span class="order-price">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                        
                        <div class="actions">
                            
                            <?php if($pedido['status'] == 'aguardando_pagamento' || $pedido['status'] == 'pago'): ?>
                                <a href="?id=<?= $pedido['id'] ?>&acao=em_preparo" class="btn-status btn-preparo">Preparar</a>
                                <a href="?id=<?= $pedido['id'] ?>&acao=cancelado" class="btn-status btn-cancelar" onclick="return confirm('Cancelar este pedido e estornar o valor?')">Cancelar</a>
                            
                            <?php elseif($pedido['status'] == 'em_preparo'): ?>
                                <a href="?id=<?= $pedido['id'] ?>&acao=pronto" class="btn-status btn-pronto">Pronto</a>
                                <a href="?id=<?= $pedido['id'] ?>&acao=cancelado" class="btn-status btn-cancelar" onclick="return confirm('Cancelar este pedido e estornar o valor?')">Cancelar</a>
                            
                            <?php elseif($pedido['status'] == 'pronto'): ?>
                                <a href="?id=<?= $pedido['id'] ?>&acao=retirado" class="btn-status btn-retirar">Entregar</a>
                                <a href="?id=<?= $pedido['id'] ?>&acao=cancelado" class="btn-status btn-cancelar" onclick="return confirm('Cancelar este pedido e estornar o valor?')">Cancelar</a>
                            
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #666; margin-top: 20px;">Nenhum pedido encontrado.</p>
        <?php endif; ?>

    </main>
</body>
</html>