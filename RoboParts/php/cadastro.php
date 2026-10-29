<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta | <?= htmlspecialchars(LOJA_NOME) ?></title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body class="auth-cadastro">

<main class="auth">
    <div class="auth-arte" aria-hidden="true">
        <div class="auth-marca">
            <img src="../img/logo.svg" alt="">
            <span><?= htmlspecialchars(LOJA_NOME) ?></span>
        </div>
        <p class="auth-slogan"><?= htmlspecialchars(LOJA_SLOGAN) ?></p>
    </div>

    <section class="auth-form">
        <a class="auth-logo" href="../index.php"><img src="../img/logo.svg" alt=""><span><?= htmlspecialchars(LOJA_NOME) ?></span></a>

        <h1>Criar conta</h1>
        <p class="auth-sub">Leva menos de um minuto.</p>

        <?php if (!empty($_SESSION['erro'])): ?>
            <p class="msg msg-erro"><?= htmlspecialchars($_SESSION['erro']) ?></p>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <form method="post" action="processar_cadastro.php">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" required minlength="2" maxlength="100" autocomplete="name">

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required maxlength="150" autocomplete="email">

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required minlength="6" maxlength="100" autocomplete="new-password">

            <label for="confirmar_senha">Confirmar senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6" maxlength="100" autocomplete="new-password">

            <fieldset class="localizacao">
                <legend>Podemos acessar sua localização para facilitar o envio dos produtos?</legend>
                <p>Você pode mudar essa preferência depois, no seu perfil.</p>
                <label><input type="radio" name="permitir_localizacao" value="sim" required> Sim</label>
                <label><input type="radio" name="permitir_localizacao" value="nao" required> Não</label>
            </fieldset>

            <button type="submit" class="btn-acessar">Criar conta</button>
        </form>

        <p class="auth-troca">Já tem conta? <a href="login.php">Entrar</a></p>
    </section>
</main>

</body>
</html>
