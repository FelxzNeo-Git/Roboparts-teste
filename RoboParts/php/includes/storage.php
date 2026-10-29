<?php
// Caminhos do projeto. Os dados (usuários, produtos, categorias) ficam na sessão (ver php/init.php);
// as imagens enviadas ficam como arquivos em uploads/.

if (!defined('RAIZ')) {
    define('RAIZ', dirname(__DIR__, 2));
}
const DIR_UPLOADS = RAIZ . '/uploads';

/**
 * Persistência: a sessão continua sendo a fonte usada pelas páginas, mas cada alteração
 * (cadastro, produto, categoria) também é gravada em data/loja.json e relida a cada
 * requisição. Assim os produtos ficam salvos até serem apagados, mesmo depois que a
 * sessão expira, e todos os visitantes veem o mesmo catálogo.
 * Para voltar ao comportamento só-sessão, troque para false.
 */
const PERSISTIR       = true;
const ARQUIVO_DADOS   = RAIZ . '/data/loja.json';
const CHAVES_PERSISTIDAS = ['usuarios', 'categorias', 'produtos'];

function dados_carregar(): bool
{
    if (!PERSISTIR || !is_file(ARQUIVO_DADOS)) {
        return false;
    }
    $json = json_decode((string) file_get_contents(ARQUIVO_DADOS), true);
    if (!is_array($json)) {
        return false;
    }
    foreach (CHAVES_PERSISTIDAS as $chave) {
        if (isset($json[$chave]) && is_array($json[$chave])) {
            $_SESSION[$chave] = $json[$chave];
        }
    }
    return true;
}

function dados_salvar(): bool
{
    if (!PERSISTIR) {
        return true;
    }
    $dir = dirname(ARQUIVO_DADOS);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return false;
    }
    $dados = [];
    foreach (CHAVES_PERSISTIDAS as $chave) {
        $dados[$chave] = $_SESSION[$chave] ?? [];
    }
    $tmp = ARQUIVO_DADOS . '.' . bin2hex(random_bytes(4)) . '.tmp';
    $ok  = file_put_contents($tmp, json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false
        && rename($tmp, ARQUIVO_DADOS);
    if (!$ok && is_file($tmp)) {
        unlink($tmp);
    }
    return $ok;
}
