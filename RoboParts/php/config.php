<?php
/**
 * Configuração geral da loja + usuários.
 * Tudo vem da sessão, preparada em init.php:
 *   $_SESSION['infoSite'] (constantes LOJA_*) e $_SESSION['usuarios'] (contas).
 */

require_once __DIR__ . '/init.php';


const LOJA_PUBLICA = false;

function carregarUsuarios(): array
{
    return $_SESSION['usuarios'] ?? [];
}

function salvarUsuarios(array $usuarios): bool
{
    $_SESSION['usuarios'] = array_values($usuarios);
    return dados_salvar();
}

function buscarUsuarioPorEmail(string $email): ?array
{
    foreach (carregarUsuarios() as $usuario) {
        if (mb_strtolower($usuario['email']) === mb_strtolower($email)) {
            return $usuario;
        }
    }
    return null;
}

function buscarUsuarioPorId(int $id): ?array
{
    foreach (carregarUsuarios() as $usuario) {
        if ((int) ($usuario['id'] ?? 0) === $id) {
            return $usuario;
        }
    }
    return null;
}

function criarUsuario(string $nome, string $email, string $senha, bool $localizacao = false): bool
{
    $usuarios = carregarUsuarios();
    $novoId   = 1;

    foreach ($usuarios as $u) {
        if (mb_strtolower($u['email']) === mb_strtolower($email)) {
            return false;
        }
        $novoId = max($novoId, ($u['id'] ?? 0) + 1);
    }

    $usuarios[] = [
        'id'          => $novoId,
        'nome'        => $nome,
        'email'       => $email,
        'senha'       => password_hash($senha, PASSWORD_DEFAULT),
        'admin'       => false,
        'localizacao' => $localizacao,
    ];
    return salvarUsuarios($usuarios);
}

function atualizarLocalizacao(int $id, bool $permitir): bool
{
    $usuarios = carregarUsuarios();
    foreach ($usuarios as &$u) {
        if ((int) ($u['id'] ?? 0) === $id) {
            $u['localizacao'] = $permitir;
        }
    }
    unset($u);
    return salvarUsuarios($usuarios);
}
