<?php
require_once __DIR__ . '/includes/bootstrap.php';
exigir_acesso();

$p = produto_por_id((string) ($_GET['id'] ?? ''));
if (!$p) {
    ir('produtos.php');
}

$categoriaAtual = categoria_por_id($p['categoria']);
$desconto = !empty($p['preco_antigo']) ? (int) round((1 - $p['preco'] / $p['preco_antigo']) * 100) : 0;
$economia = !empty($p['preco_antigo']) ? $p['preco_antigo'] - $p['preco'] : 0;
$parcela  = parcela((float) $p['preco']);
$specs    = specs_lista($p['specs'] ?? '');
$relacionados = array_slice(array_filter(
    produtos_recentes(null, $p['categoria']),
    fn($r) => $r['id'] !== $p['id']
), 0, 4);

$titulo = $p['nome'];
require __DIR__ . '/includes/header.php';
?>

<main class="pagina">
    <p class="trilha">
        <a href="<?= e(asset('index.php')) ?>">Início</a> /
        <a href="produtos.php">Produtos</a> /
        <?php if ($categoriaAtual): ?>
            <a href="categoria.php?c=<?= e($categoriaAtual['slug']) ?>"><?= e($categoriaAtual['nome']) ?></a> /
        <?php endif; ?>
        <?= e($p['nome']) ?>
    </p>

    <section class="detalhe">
        <div class="detalhe-foto">
            <?= img_produto($p, '', 'eager') ?>
            <?php if ($desconto > 0): ?><span class="selo selo-desc">-<?= $desconto ?>%</span><?php endif; ?>
        </div>

        <div class="detalhe-info">
            <?php if ($categoriaAtual): ?>
                <a class="detalhe-cat" href="categoria.php?c=<?= e($categoriaAtual['slug']) ?>"><?= e($categoriaAtual['nome']) ?></a>
            <?php endif; ?>
            <h1><?= e($p['nome']) ?></h1>
            <p class="detalhe-codigo">Código <?= e(strtoupper($p['id'])) ?></p>

            <div class="detalhe-preco">
                <?php if (!empty($p['preco_antigo'])): ?>
                    <s><?= preco($p['preco_antigo']) ?></s>
                <?php endif; ?>
                <strong><?= preco($p['preco']) ?></strong>
                <?php if ($parcela): ?><span>ou <?= e($parcela) ?></span><?php endif; ?>
                <?php if ($economia > 0): ?><span class="economia">Você economiza <?= preco($economia) ?></span><?php endif; ?>
            </div>

            <?php if (!empty($p['descricao'])): ?>
                <p class="detalhe-desc"><?= nl2br(e($p['descricao'])) ?></p>
            <?php endif; ?>

            <form class="detalhe-form" method="post" action="carrinho.php">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="adicionar">
                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                <input type="hidden" name="voltar" value="<?= e(voltar_atual()) ?>">
                <div class="detalhe-qtd">
                    <label for="qtd">Quantidade</label>
                    <input type="number" id="qtd" name="qtd" value="1" min="1" max="20">
                </div>
                <button class="btn btn-cheio btn-grande" type="submit">Adicionar ao carrinho</button>
            </form>

            <?php if (admin()): ?>
                <form class="detalhe-admin" method="post" action="excluir.php" onsubmit="return confirm('Excluir este produto?')">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="tipo" value="produto">
                    <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                    <button class="del" type="submit">Excluir produto</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($specs): ?>
        <section class="secao-interna">
            <h2>Especificações</h2>
            <table class="specs">
                <?php foreach ($specs as [$chave, $valor]): ?>
                    <tr>
                        <?php if ($chave !== ''): ?>
                            <th><?= e($chave) ?></th><td><?= e($valor) ?></td>
                        <?php else: ?>
                            <td colspan="2"><?= e($valor) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    <?php endif; ?>

    <?php if ($relacionados): ?>
        <section class="secao-interna">
            <h2>Mais de <?= e($categoriaAtual['nome'] ?? 'esta categoria') ?></h2>
            <div class="grade-produtos">
                <?php foreach ($relacionados as $r) echo card_produto($r); ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php';
