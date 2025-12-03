<?php
require_once('../backend/conection.php');
session_start();

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrinho'])) {
    header("Location: dashboard.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

$ids = implode(',', array_keys($_SESSION['carrinho']));
$result = $conn->query("SELECT id, preco, estoque FROM produtos WHERE id IN ($ids)");

$total_pedido = 0;
$itens_para_inserir = [];

while($prod = $result->fetch_assoc()) {
    $qtd = $_SESSION['carrinho'][$prod['id']];
    $total_pedido += ($prod['preco'] * $qtd);
    
    $itens_para_inserir[] = [
        'id' => $prod['id'],
        'qtd' => $qtd,
        'preco' => $prod['preco']
    ];
}

$res_user = $conn->query("SELECT saldo FROM usuarios WHERE id = $id_usuario");
$user = $res_user->fetch_assoc();

if ($user['saldo'] < $total_pedido) {
    header("Location: checkout.php?erro=saldo");
    exit;
}

$conn->begin_transaction();

try {
    $conn->query("UPDATE usuarios SET saldo = saldo - $total_pedido WHERE id = $id_usuario");

    $sql_pedido = "INSERT INTO pedidos (aluno_id, total, status) VALUES (?, ?, 'pago')";
    $stmt = $conn->prepare($sql_pedido);
    $stmt->bind_param("id", $id_usuario, $total_pedido);
    $stmt->execute();
    $id_pedido = $stmt->insert_id;

    $sql_item = "INSERT INTO itens_do_pedido (pedido_id, produto_id, quantidade, preco_no_momento) VALUES (?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);

    foreach ($itens_para_inserir as $item) {
        $stmt_item->bind_param("iiid", $id_pedido, $item['id'], $item['qtd'], $item['preco']);
        $stmt_item->execute();

        $conn->query("UPDATE produtos SET estoque = estoque - {$item['qtd']} WHERE id = {$item['id']}");
    }

    $conn->commit();

    unset($_SESSION['carrinho']);
    $_SESSION['usuario_saldo'] -= $total_pedido;

    header("Location: meus_pedidos.php?sucesso=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo "Erro ao processar pedido: " . $e->getMessage();
}
?>