<?php

exigir_acesso();

$categoriaAtual = $categoriaAtual ?? null;
$produtos = produtos_recentes(null, $categoriaAtual['id'] ?? null);

// Busca por texto
$busca = trim((string) ($_GET['q'] ?? ''));
if ($busca !== '') {
    $produtos = array_values(array_filter($produtos, function ($p) use ($busca) {
        $texto = $p['nome'] . ' ' . ($p['descricao'] ?? '') . ' ' . nome_categoria($p['categoria']);
        return mb_stripos($texto, $busca) !== false;
    }));
}

// Ordenação
$ordens = [
    'recentes' => 'Mais recentes',
    'menor'    => 'Menor preço',
    'maior'    => 'Maior preço',
];
$ordem = (string) ($_GET['ordem'] ?? 'recentes');
if (!isset($ordens[$ordem])) {
    $ordem = 'recentes';
}
if ($ordem === 'menor') {
    usort($produtos, fn($a, $b) => $a['preco'] <=> $b['preco']);
} elseif ($ordem === 'maior') {
    usort($produtos, fn($a, $b) => $b['preco'] <=> $a['preco']);
}

$total    = count($produtos);
$novoLink = pg('criar_produto.php') . ($categoriaAtual ? '?c=' . rawurlencode($categoriaAtual['slug']) : '');

$titulo = $categoriaAtual['nome'] ?? 'Todos os produtos';
require __DIR__ . '/header.php';
?>

<main class="pagina">
    <p class="trilha">
        <a href="<?= e(asset('index.php')) ?>">Início</a> /
        <?php if ($categoriaAtual): ?>
            <a href="<?= e(pg('produtos.php')) ?>">Produtos</a> / <?= e($categoriaAtual['nome']) ?>
        <?php else: ?>
            Produtos
        <?php endif; ?>
    </p>

    <h1><?= e($titulo) ?></h1>
    <?php if (!empty($categoriaAtual['descricao'])): ?>
        <p class="texto-suave"><?= e($categoriaAtual['descricao']) ?></p>
    <?php endif; ?>

    <form class="barra-lista" method="get">
        <?php if ($categoriaAtual): ?><input type="hidden" name="c" value="<?= e($categoriaAtual['slug']) ?>"><?php endif; ?>
        <?php if ($busca !== ''): ?><input type="hidden" name="q" value="<?= e($busca) ?>"><?php endif; ?>
        <span class="barra-total">
            <?= $total ?> <?= $total === 1 ? 'produto' : 'produtos' ?>
            <?php if ($busca !== ''): ?> para "<?= e($busca) ?>" · <a href="<?= e(basename($_SERVER['SCRIPT_NAME'])) ?><?= $categoriaAtual ? '?c=' . e($categoriaAtual['slug']) : '' ?>">limpar busca</a><?php endif; ?>
        </span>
        <label>
            Ordenar por
            <select name="ordem" onchange="this.form.submit()">
                <?php foreach ($ordens as $valor => $rotulo): ?>
                    <option value="<?= e($valor) ?>" <?= $ordem === $valor ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <noscript><button class="btn" type="submit">Aplicar</button></noscript>
    </form>

    <?php if ($produtos): ?>
        <div class="grade-produtos">
            <?php foreach ($produtos as $p) echo card_produto($p); ?>
        </div>
        <?php if (admin()): ?>
            <p class="acao-admin"><a href="<?= e($novoLink) ?>">+ Novo produto</a></p>
        <?php endif; ?>
    <?php else: ?>
        <div class="vazio">
            <h3>Nenhum produto encontrado</h3>
            <p><?= $busca !== '' ? 'Tente outro termo de busca.' : 'Os produtos aparecem aqui assim que forem adicionados.' ?></p>
            <?php if (admin()): ?><a class="btn btn-cheio" href="<?= e($novoLink) ?>">+ Novo produto</a><?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/footer.php';
