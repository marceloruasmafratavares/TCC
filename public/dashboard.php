<?php
require_once('../backend/conection.php'); 
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$qtd_carrinho = 0;
if(isset($_SESSION['carrinho'])) {
    foreach($_SESSION['carrinho'] as $qtd) {
        $qtd_carrinho += $qtd;
    }
}
$sql_categorias = "SELECT * FROM categorias ORDER BY nome ASC";
$result_categorias = $conn->query($sql_categorias);

$filtro_sql = "";
$categoria_ativa = 'Todos';

if (isset($_GET['categoria']) && $_GET['categoria'] != 'Todos') {
    $id_cat = intval($_GET['categoria']);
    $filtro_sql = "WHERE p.categoria_id = $id_cat";
    $categoria_ativa = $id_cat;
}

$sql_produtos = "SELECT p.*, c.nome as nome_categoria 
                 FROM produtos p 
                 LEFT JOIN categorias c ON p.categoria_id = c.id 
                 $filtro_sql
                 ORDER BY p.nome ASC";

$result_produtos = $conn->query($sql_produtos);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Byte Menu - Painel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/dashboard.css">
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
            <a href="dashboard.php" class="menu-link active">
                <i class="fa-solid fa-book-open"></i>
                <div class="link-text">
                    <span class="link-title">Cardápio</span>
                    <span class="link-desc">Ver produtos disponíveis</span>
                </div>
            </a>
            <a href="meus_pedidos.php" class="menu-link">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <div class="link-text">
                    <span class="link-title">Meus Pedidos</span>
                    <span class="link-desc">Histórico de compras</span>
                </div>
            </a>
            <a href="perfil.php" class="menu-link">
                <i class="fa-solid fa-wallet"></i>
                <div class="link-text">
                    <span class="link-title">Saldo</span>
                    <span class="link-desc">Gerenciar créditos</span>
                </div>
            </a>
            <a href="controle_parental.php" class="menu-link">
                <i class="fa-solid fa-shield-halved"></i>
                <div class="link-text">
                    <span class="link-title">Controle Parental</span>
                    <span class="link-desc">Configurações e limites</span>
                </div>
            </a>
        </nav>

        <a href="index.php" class="sidebar-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Sair do sistema
        </a>
    </aside>


    <header class="top-bar">
        <div class="header-left">
            <div class="menu-toggle" onclick="toggleMenu()">
                <i class="fa-solid fa-bars"></i>
            </div>
            
            <a href="dashboard.php" class="logo">
                <div class="logo-icon"><i class="fa-solid fa-utensils"></i></div>
                Byte Menu
            </a>
        </div>

        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item active"><i class="fa-solid fa-book-open"></i> Cardápio</a>
            <a href="meus_pedidos.php" class="nav-item"><i class="fa-solid fa-clock-rotate-left"></i> Pedidos</a>
            <a href="perfil.php" class="nav-item"><i class="fa-solid fa-wallet"></i> Saldo</a>
        </nav>

        <div class="header-right">
            <a href="carrinho.php" class="icon-btn">
                <i class="fa-solid fa-cart-shopping"></i>
                
                <?php if($qtd_carrinho > 0): ?>
                    <span class="badge-count"><?= $qtd_carrinho ?></span>
                <?php endif; ?>
            </a>

            <div class="user-mini-profile">
                <div class="mini-avatar"><i class="fa-regular fa-user"></i></div>
                <div class="mini-info">
                    <h4><?php echo $_SESSION['usuario_nome']; ?></h4>
                    <span><?php echo $_SESSION['usuario_matricula']; ?></span>
                </div>
                <a href="index.php" title="Sair"><i class="fa-solid fa-right-from-bracket logout-mini"></i></a>
            </div>
        </div>
    </header>


    <div class="container">
        <div class="page-header">
            <h2>Cardápio</h2>
            <p class="subtitle">Escolha seus produtos favoritos e adicione ao carrinho</p>
        </div>

        <div class="filters">
            <a href="dashboard.php?categoria=Todos" class="filter-btn <?php echo ($categoria_ativa == 'Todos') ? 'active' : ''; ?>">Todos</a>

            <?php if ($result_categorias->num_rows > 0): ?>
                <?php while($cat = $result_categorias->fetch_assoc()): 
                    $is_active = ($categoria_ativa == $cat['id']) ? 'active' : '';
                ?>
                    <a href="dashboard.php?categoria=<?php echo $cat['id']; ?>" class="filter-btn <?php echo $is_active; ?>">
                        <?php echo $cat['nome']; ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php if($result_produtos->num_rows > 0): ?>
                <?php while($produto = $result_produtos->fetch_assoc()): ?>
                    <div class="card">
                        <?php 
                        $imagem_final = $produto['imagem_url'];
                        if (strpos($imagem_final, 'http') === false) {
                            $imagem_final = "gestor/" . $imagem_final; 
                        }
                        ?>
                        <img src="<?php echo $imagem_final; ?>" alt="<?php echo $produto['nome']; ?>" class="card-img">
                        <div class="card-body">
                            <?php if($produto['nome_categoria']): ?>
                                <span class="badge badge-<?php echo strtolower($produto['nome_categoria']); ?>">
                                    <?php echo $produto['nome_categoria']; ?>
                                </span>
                            <?php endif; ?>

                            <h3 class="card-title"><?php echo $produto['nome']; ?></h3>
                            <p class="card-desc"><?php echo $produto['descricao']; ?></p>
                            
                            <div class="card-footer">
                                <span class="price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                
                                <a href="carrinho_acoes.php?acao=add&id=<?php echo $produto['id']; ?>" class="btn-add" style="text-decoration:none;">
                                    <i class="fa-solid fa-plus"></i> Adicionar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum produto encontrado.</p>
            <?php endif; ?>
        </div>
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