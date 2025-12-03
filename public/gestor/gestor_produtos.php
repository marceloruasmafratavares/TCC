<?php
require_once('../../backend/conection.php');
session_start();

if (!isset($_SESSION['gestor_id'])) { header("Location: gestor_login.php"); exit; }

if (isset($_GET['acao'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['acao'] == 'deletar_produto') {
        $busca = $conn->query("SELECT imagem_url FROM produtos WHERE id = $id");
        $arquivo = $busca->fetch_assoc();
        if ($arquivo && file_exists($arquivo['imagem_url'])) {
            unlink($arquivo['imagem_url']);
        }

        $conn->query("DELETE FROM produtos WHERE id = $id");
        header("Location: gestor_produtos.php"); exit;
    }
    
    if ($_GET['acao'] == 'deletar_categoria') {
        $conn->query("DELETE FROM categorias WHERE id = $id");
        header("Location: gestor_produtos.php"); exit;
    }
}

if (isset($_POST['nova_categoria'])) {
    $nome_cat = $_POST['nome_cat'];
    $conn->query("INSERT INTO categorias (nome) VALUES ('$nome_cat')");
    header("Location: gestor_produtos.php"); exit;
}

if (isset($_POST['salvar_produto'])) {
    $nome = $_POST['nome'];
    $desc = $_POST['descricao'];
    $preco = $_POST['preco'];
    $cat_id = $_POST['categoria_id'];
    
    $caminho_imagem = "";
    
    if (isset($_FILES['imagem_arquivo']) && $_FILES['imagem_arquivo']['error'] == 0) {
        $extensao = pathinfo($_FILES['imagem_arquivo']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . "." . $extensao;
        $pasta = "uploads/";
        
        if(move_uploaded_file($_FILES['imagem_arquivo']['tmp_name'], $pasta . $novo_nome)){
            $caminho_imagem = $pasta . $novo_nome;
        }
    }

    if (!empty($_POST['produto_id'])) {
        $id_prod = intval($_POST['produto_id']);
        
        if (empty($caminho_imagem)) {
            $stmt = $conn->prepare("UPDATE produtos SET nome=?, descricao=?, preco=?, categoria_id=? WHERE id=?");
            $stmt->bind_param("ssdii", $nome, $desc, $preco, $cat_id, $id_prod);
        } else {
            $stmt = $conn->prepare("UPDATE produtos SET nome=?, descricao=?, preco=?, categoria_id=?, imagem_url=? WHERE id=?");
            $stmt->bind_param("ssdisi", $nome, $desc, $preco, $cat_id, $caminho_imagem, $id_prod);
        }
    } 
    else {
        if(empty($caminho_imagem)) $caminho_imagem = "https://via.placeholder.com/150";

        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, categoria_id, imagem_url, estoque) VALUES (?, ?, ?, ?, ?, 100)");
        $stmt->bind_param("ssdis", $nome, $desc, $preco, $cat_id, $caminho_imagem);
    }
    
    if(isset($stmt)) $stmt->execute();
    header("Location: gestor_produtos.php"); exit;
}

$produto_edit = null;
if (isset($_GET['acao']) && $_GET['acao'] == 'editar_produto') {
    $id_edit = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM produtos WHERE id = $id_edit");
    $produto_edit = $res->fetch_assoc();
}

$categorias = $conn->query("SELECT * FROM categorias");
$lista_categorias = $conn->query("SELECT * FROM categorias");
$produtos = $conn->query("SELECT p.*, c.nome as cat_nome FROM produtos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Produtos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/gestor_produtos.css">
</head>
<body>
    <aside>
        <div class="brand"><i class="fa-solid fa-utensils"></i> Gestor</div>
        <nav class="menu">
            <a href="gestor_dashboard.php"><i class="fa-solid fa-list-check"></i> Pedidos</a>
            <a href="gestor_produtos.php" class="active"><i class="fa-solid fa-box"></i> Produtos</a>
            <a href="gestor_perfil.php"><i class="fa-solid fa-user-gear"></i> Perfil</a>
            <a href="gestor_login.php" style="margin-top: auto; color: #ef4444;"><i class="fa-solid fa-power-off"></i> Sair</a>
        </nav>
    </aside>

    <main>
        <h2>Gerenciar Catálogo</h2>

        <div class="grid-layout">
            
            <div class="card">
                <h3><?php echo $produto_edit ? 'Editar Produto' : 'Novo Produto'; ?></h3>
                
                <form method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="produto_id" value="<?php echo $produto_edit ? $produto_edit['id'] : ''; ?>">

                    <div style="display:flex; gap:10px;">
                        <div style="flex:1">
                            <label>Nome</label>
                            <input type="text" name="nome" value="<?php echo $produto_edit ? $produto_edit['nome'] : ''; ?>" required>
                        </div>
                        <div style="width: 120px;">
                            <label>Preço</label>
                            <input type="number" step="0.01" name="preco" value="<?php echo $produto_edit ? $produto_edit['preco'] : ''; ?>" required>
                        </div>
                    </div>

                    <label>Categoria</label>
                    <select name="categoria_id" required>
                        <option value="">Selecione...</option>
                        <?php while($cat = $categorias->fetch_assoc()): 
                            $selected = ($produto_edit && $produto_edit['categoria_id'] == $cat['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= $cat['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Imagem do Produto</label>
                    <?php if($produto_edit && !empty($produto_edit['imagem_url'])): ?>
                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <img src="<?php echo $produto_edit['imagem_url']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <span style="font-size: 12px; color: #666;">Imagem Atual</span>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" name="imagem_arquivo" accept="image/*">
                    <p style="font-size: 11px; color: #888; margin-top: -10px; margin-bottom: 15px;">Formatos aceitos: JPG, PNG, GIF</p>

                    <label>Descrição</label>
                    <textarea name="descricao" rows="2"><?php echo $produto_edit ? $produto_edit['descricao'] : ''; ?></textarea>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="salvar_produto">
                            <?php echo $produto_edit ? 'Atualizar Produto' : 'Cadastrar Produto'; ?>
                        </button>
                        
                        <?php if($produto_edit): ?>
                            <a href="gestor_produtos.php" class="button btn-cancel" style="padding: 10px 20px; border-radius: 5px; color: white; text-decoration: none;">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3>Categorias</h3>
                <form method="POST" style="margin-bottom: 20px;">
                    <div style="display: flex; gap: 5px;">
                        <input type="text" name="nome_cat" placeholder="Nova Categoria" required style="margin:0;">
                        <button type="submit" name="nova_categoria"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </form>

                <table>
                    <tbody>
                        <?php if($lista_categorias->num_rows > 0): ?>
                            <?php while($c = $lista_categorias->fetch_assoc()): ?>
                            <tr>
                                <td><?= $c['nome'] ?></td>
                                <td style="text-align: right;">
                                    <a href="?acao=deletar_categoria&id=<?= $c['id'] ?>" class="btn-del" onclick="return confirm('Tem certeza?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2">Nenhuma categoria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3>Produtos Cadastrados</h3>
            <table>
                <thead>
                    <tr>
                        <th width="50">Img</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th width="100">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($produtos->num_rows > 0): ?>
                        <?php while($prod = $produtos->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?= $prod['imagem_url'] ?>">
                            </td>
                            <td><?= $prod['nome'] ?></td>
                            <td><?= $prod['cat_nome'] ?></td>
                            <td>R$ <?= number_format($prod['preco'], 2, ',', '.') ?></td>
                            <td class="actions">
                                <a href="?acao=editar_produto&id=<?= $prod['id'] ?>" class="btn-edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="?acao=deletar_produto&id=<?= $prod['id'] ?>" class="btn-del" onclick="return confirm('Excluir?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">Nenhum produto cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>