<?php

require_once __DIR__ . '/../config.php';


function asset(string $caminho): string
{
    return (defined('NA_RAIZ') ? '' : '../') . $caminho;
}


function pg(string $pagina): string
{
    return (defined('NA_RAIZ') ? 'php/' : '') . $pagina;
}

function voltar_atual(): string
{
    if (defined('NA_RAIZ')) {
        return '../index.php';
    }
    $arquivo = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');
    $query   = $_SERVER['QUERY_STRING'] ?? '';
    return $arquivo . ($query !== '' ? '?' . $query : '');
}

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function preco(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function ir(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function paraNumero(string $v): ?float
{
    $v = trim(str_ireplace(['R$', ' '], '', $v));
    if ($v === '') {
        return null;
    }
    if (str_contains($v, ',')) {
        $v = str_replace(['.', ','], ['', '.'], $v);
    }
    return is_numeric($v) ? (float) $v : null;
}

function slug(string $texto): string
{
    $texto = strtr(mb_strtolower($texto), [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n',
    ]);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-') ?: 'item';
}

function flash(string $chave, mixed $valor = null): mixed
{
    if ($valor !== null) {
        $_SESSION['flash'][$chave] = $valor;
        return null;
    }
    $v = $_SESSION['flash'][$chave] ?? null;
    unset($_SESSION['flash'][$chave]);
    return $v;
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $esperado = $_SESSION['csrf'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $esperado === ''
        || !hash_equals($esperado, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Requisição inválida. Volte e tente novamente.');
    }
}

function logado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

function admin(): bool
{
    return logado() && !empty($_SESSION['usuario_admin']);
}

function exigir_login(): void
{
    if (!logado()) {
        ir(pg('login.php'));
    }
}

function exigir_acesso(): void
{
    if (!LOJA_PUBLICA) {
        exigir_login();
    }
}

function exigir_admin(): void
{
    exigir_login();
    if (!admin()) {
        http_response_code(403);
        exit('Acesso restrito a administradores. <a href="' . e(asset('index.php')) . '">Voltar</a>');
    }
}


function catalogo(): array
{
    return [
        'categorias' => $_SESSION['categorias'] ?? [],
        'produtos'   => $_SESSION['produtos'] ?? [],
    ];
}

function catalogo_atualizar(callable $alterar): bool
{
    $novo = $alterar(catalogo());
    if (!is_array($novo)) {
        return false;
    }
    $_SESSION['categorias'] = array_values($novo['categorias']);
    $_SESSION['produtos']   = array_values($novo['produtos']);
    return dados_salvar();
}

function categorias(): array
{
    return catalogo()['categorias'];
}

function categoria_por_slug(string $slug): ?array
{
    foreach (categorias() as $c) {
        if ($c['slug'] === $slug) {
            return $c;
        }
    }
    return null;
}

function categoria_por_id(string $id): ?array
{
    foreach (categorias() as $c) {
        if ($c['id'] === $id) {
            return $c;
        }
    }
    return null;
}

function produto_por_id(string $id): ?array
{
    foreach (catalogo()['produtos'] as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function produtos_da_categoria(string $id): array
{
    return array_values(array_filter(catalogo()['produtos'], fn($p) => $p['categoria'] === $id));
}

function contar_produtos(string $categoriaId): int
{
    return count(produtos_da_categoria($categoriaId));
}


function produtos_recentes(?int $limite = null, ?string $categoriaId = null): array
{
    $lista = $categoriaId === null ? catalogo()['produtos'] : produtos_da_categoria($categoriaId);
    usort($lista, fn($a, $b) => ($b['criado'] ?? 0) <=> ($a['criado'] ?? 0));
    return $limite ? array_slice($lista, 0, $limite) : $lista;
}

function nome_categoria(string $id): string
{
    static $mapa = null;
    $mapa ??= array_column(categorias(), 'nome', 'id');
    return $mapa[$id] ?? '';
}


function imagem_categoria(array $cat): ?string
{
    if (!empty($cat['imagem'])) {
        return url_upload($cat['imagem']) ?: null;
    }
    // Sem upload: usa img/categorias/<slug>.(png|jpg|webp|svg) se o arquivo existir
    foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
        $arquivo = 'img/categorias/' . $cat['slug'] . '.' . $ext;
        if (is_file(RAIZ . '/' . $arquivo)) {
            return asset($arquivo);
        }
    }
    return null;
}

/**
 * URL da imagem de um produto. Procura em uploads/ (enviadas pelo painel) e em img/produtos/
 * (imagens que você coloca à mão; a extensão pode ser omitida no cadastro). Se não achar nada,
 * devolve '' e as páginas mostram um espaço reservado.
 */
function url_upload(?string $arquivo): string
{
    $nome = basename((string) $arquivo);
    if ($nome === '') {
        return '';
    }
    foreach (['uploads', 'img/produtos'] as $dir) {
        $tentativas = [$nome];
        if (!str_contains($nome, '.')) {
            foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
                $tentativas[] = $nome . '.' . $ext;
            }
        }
        foreach ($tentativas as $t) {
            if (is_file(RAIZ . '/' . $dir . '/' . $t)) {
                return asset($dir . '/' . rawurlencode($t));
            }
        }
    }
    return '';
}

/** <img> do produto, ou um quadro vazio quando a imagem ainda não existe. */
function img_produto(array $p, string $classe = '', string $loading = 'lazy'): string
{
    $url = url_upload($p['imagem'] ?? null);
    if ($url === '') {
        return '<span class="sem-foto ' . e($classe) . '" role="img" aria-label="' . e($p['nome']) . '"><span>sem imagem</span></span>';
    }
    return '<img class="' . e($classe) . '" src="' . e($url) . '" alt="' . e($p['nome']) . '" loading="' . e($loading) . '">';
}

/** Valor da parcela em 12x (só mostra a partir de R$ 120, para a parcela não ficar minúscula). */
function parcela(float $valor): ?string
{
    return $valor >= 120 ? '12x de ' . preco($valor / 12) : null;
}

/** Transforma o texto "Chave: valor" (uma por linha) numa lista de pares. */
function specs_lista(?string $texto): array
{
    $saida = [];
    foreach (preg_split('/\R/u', (string) $texto) as $linha) {
        $linha = trim($linha);
        if ($linha === '') {
            continue;
        }
        if (str_contains($linha, ':')) {
            [$k, $v] = array_map('trim', explode(':', $linha, 2));
            $saida[] = [$k, $v];
        } else {
            $saida[] = ['', $linha];
        }
    }
    return $saida;
}

function apagar_upload(?string $arquivo): void
{
    $caminho = DIR_UPLOADS . '/' . basename((string) $arquivo);
    $iniciais = array_merge(array_column(produtos_iniciais(), 'imagem'), array_column(categorias_iniciais(), 'imagem'));
    if ($arquivo && !in_array($arquivo, $iniciais, true) && is_file($caminho)) {
        unlink($caminho);
    }
}

function salvar_imagem(?array $arquivo, bool $obrigatoria, ?string &$erro): ?string
{
    $erro = null;
    $permitidas = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    if (!$arquivo || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        $erro = $obrigatoria ? 'Envie uma imagem.' : null;
        return null;
    }
    if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE) {
        $erro = 'A imagem é grande demais (máximo 5 MB).';
        return null;
    }
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Não foi possível enviar a imagem. Tente novamente.';
        return null;
    }
    if ($arquivo['size'] > 5 * 1024 * 1024) {
        $erro = 'A imagem deve ter no máximo 5 MB.';
        return null;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
    if (!isset($permitidas[$mime]) || @getimagesize($arquivo['tmp_name']) === false) {
        $erro = 'O arquivo enviado não é uma imagem válida (use JPG, PNG, WEBP ou GIF).';
        return null;
    }
    if (!is_dir(DIR_UPLOADS) && !mkdir(DIR_UPLOADS, 0755, true)) {
        $erro = 'Pasta uploads/ inexistente e não pôde ser criada.';
        return null;
    }
    $nome = bin2hex(random_bytes(8)) . '.' . $permitidas[$mime];
    if (!move_uploaded_file($arquivo['tmp_name'], DIR_UPLOADS . '/' . $nome)) {
        $erro = 'Falha ao salvar a imagem. Verifique a permissão da pasta uploads/.';
        return null;
    }
    return $nome;
}
function carrinho_qtd(): int
{
    $ids = array_column(catalogo()['produtos'], 'id');
    $qtd = 0;
    foreach ($_SESSION['carrinho'] ?? [] as $id => $n) {
        if (in_array((string) $id, $ids, true)) {
            $qtd += (int) $n;
        }
    }
    return $qtd;
}

function card_produto(array $p): string
{
    $desconto = !empty($p['preco_antigo']) ? (int) round((1 - $p['preco'] / $p['preco_antigo']) * 100) : 0;
    $novo     = (time() - ($p['criado'] ?? 0)) < 30 * 86400;
    $link     = pg('produto.php') . '?id=' . rawurlencode($p['id']);
    $parcela  = parcela((float) $p['preco']);

    ob_start(); ?>
    <article class="card" data-cat="<?= e($p['categoria']) ?>">
        <a class="card-foto" href="<?= e($link) ?>">
            <?= img_produto($p) ?>
            <?php if ($desconto > 0): ?><span class="selo selo-desc">-<?= $desconto ?>%</span>
            <?php elseif ($novo): ?><span class="selo selo-novo">Novo</span><?php endif; ?>
        </a>
        <div class="card-corpo">
            <p class="card-cat"><?= e(nome_categoria($p['categoria'])) ?></p>
            <h3 class="card-nome"><a href="<?= e($link) ?>"><?= e($p['nome']) ?></a></h3>
            <p class="card-preco">
                <?php if (!empty($p['preco_antigo'])): ?><s><?= preco($p['preco_antigo']) ?></s><?php endif; ?>
                <strong><?= preco($p['preco']) ?></strong>
                <?php if ($parcela): ?><small>ou <?= e($parcela) ?></small><?php endif; ?>
            </p>
            <form method="post" action="<?= e(pg('carrinho.php')) ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="acao" value="mais">
                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                <input type="hidden" name="voltar" value="<?= e(voltar_atual()) ?>">
                <button class="btn btn-cheio" type="submit">Adicionar ao carrinho</button>
            </form>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
