<?php
define('NA_RAIZ', true);
require_once __DIR__ . '/php/includes/bootstrap.php';

// Sair
if (isset($_GET['logout'])) {
    // Só encerra o login: infoSite, usuarios e produtos continuam na sessão.
    unset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_admin'],
          $_SESSION['carrinho'], $_SESSION['pedido_ok'], $_SESSION['csrf']);
    session_regenerate_id(true);
    header('Location: php/login.php');
    exit;
}

// Visitante sem login vai para a tela de login (a não ser que LOJA_PUBLICA seja true).
exigir_acesso();

$categorias = categorias();
$temHero    = is_file(__DIR__ . '/img/hero.png');

$titulo = 'Início';
require __DIR__ . '/php/includes/header.php';
?>

<main>
    <section class="hero">
        <div class="hero-in">
            <div class="hero-texto">
                <p class="rotulo">Robótica para empresas e laboratórios</p>
                <h1>Robôs para cada tarefa.</h1>
                <p class="hero-sub">Industriais, humanoides, sociais, agrícolas e colaborativos. Escolha a categoria e monte o seu projeto.</p>
                <div class="hero-botoes">
                    <a class="btn btn-cheio" href="#catalogo">Ver catálogo</a>
                    <a class="btn btn-escuro" href="<?= e(pg('produtos.php')) ?>">Todos os produtos</a>
                </div>
            </div>
            <div class="hero-img">
                <?php if ($temHero): ?>
                    <img src="<?= e(asset('img/hero.png')) ?>" alt="">
                <?php else: ?>
                    <span class="reservado">img/hero.png</span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="secao" id="categorias">
        <div class="secao-in">
            <div class="secao-topo">
                <h2>Categorias</h2>
            </div>
            <div class="grade-categorias">
                <?php foreach ($categorias as $i => $c): $img = imagem_categoria($c); ?>
                    <a class="cat-card" href="<?= e(pg('categoria.php')) ?>?c=<?= e($c['slug']) ?>">
                        <?php if ($img): ?>
                            <img src="<?= e($img) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="cat-num"><?= sprintf('%02d', $i + 1) ?></span>
                        <?php endif; ?>
                        <strong><?= e($c['nome']) ?></strong>
                        <small><?php $n = contar_produtos($c['id']); echo $n . ($n === 1 ? ' produto' : ' produtos'); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (admin()): ?>
                <p class="acao-admin"><a href="<?= e(pg('nova_categoria.php')) ?>">+ Nova categoria</a></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="secao secao-cinza" id="catalogo">
        <div class="secao-in">
            <div class="secao-topo">
                <h2>Catálogo</h2>
                <a class="ver-todos" href="<?= e(pg('produtos.php')) ?>">Ver todos os produtos</a>
            </div>

            <?php foreach ($categorias as $c): $lista = produtos_recentes(4, $c['id']); ?>
                <div class="vitrine">
                    <div class="vitrine-topo">
                        <div>
                            <h3><a href="<?= e(pg('categoria.php')) ?>?c=<?= e($c['slug']) ?>"><?= e($c['nome']) ?></a></h3>
                            <?php if (!empty($c['descricao'])): ?><p><?= e($c['descricao']) ?></p><?php endif; ?>
                        </div>
                        <a class="ver-todos" href="<?= e(pg('categoria.php')) ?>?c=<?= e($c['slug']) ?>">Ver categoria</a>
                    </div>

                    <?php if ($lista): ?>
                        <div class="grade-produtos">
                            <?php foreach ($lista as $p) echo card_produto($p); ?>
                        </div>
                    <?php else: ?>
                        <div class="vazio">
                            <p>Nenhum produto nesta categoria ainda.</p>
                            <?php if (admin()): ?><a class="btn btn-cheio" href="<?= e(pg('criar_produto.php')) ?>?c=<?= e($c['slug']) ?>">+ Novo produto</a><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (!$categorias): ?>
                <div class="vazio">
                    <h3>Nenhuma categoria cadastrada</h3>
                    <?php if (admin()): ?><a class="btn btn-cheio" href="<?= e(pg('nova_categoria.php')) ?>">+ Nova categoria</a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="chamada">
        <div class="chamada-in">
            <div>
                <h2>Precisa de ajuda para escolher?</h2>
                <p>Fale com a nossa equipe pelo WhatsApp ou por e-mail.</p>
            </div>
            <div class="chamada-botoes">
                <a class="btn btn-cheio" href="https://wa.me/<?= e(preg_replace('/\D/', '', LOJA_TELEFONE)) ?>">WhatsApp</a>
                <a class="btn btn-escuro" href="mailto:<?= e(LOJA_EMAIL) ?>">E-mail</a>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/php/includes/footer.php';
