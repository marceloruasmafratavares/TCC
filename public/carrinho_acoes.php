<?php
session_start();

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

switch ($acao) {
    case 'add':
        if ($id > 0) {
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]++;
            } else {
                $_SESSION['carrinho'][$id] = 1;
            }
        }
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
        break;

    case 'diminuir':
        if ($id > 0 && isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id]--;
            if ($_SESSION['carrinho'][$id] <= 0) {
                unset($_SESSION['carrinho'][$id]);
            }
        }
        header("Location: carrinho.php");
        exit;
        break;

    case 'remover':
        if ($id > 0 && isset($_SESSION['carrinho'][$id])) {
            unset($_SESSION['carrinho'][$id]);
        }
        header("Location: carrinho.php");
        exit;
        break;

    case 'limpar':
        unset($_SESSION['carrinho']);
        header("Location: carrinho.php");
        exit;
        break;
        
    default:
        header("Location: dashboard.php");
        break;
}
?>