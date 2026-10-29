<?php
/**
 * Inicialização do site.
 *
 * Inicia a sessão e, se ainda não existirem, cria os arrays principais guardados nela:
 *   $_SESSION['infoSite']   -> dados institucionais (nome, slogan, contato)
 *   $_SESSION['usuarios']   -> contas (nome, email, senha)
 *   $_SESSION['categorias'] -> categorias da loja (cada uma tem página própria)
 *   $_SESSION['produtos']   -> acervo de produtos
 * Usuários, categorias e produtos também são espelhados em data/loja.json (ver includes/storage.php).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/storage.php';

if (!isset($_SESSION['infoSite'])) {
    $_SESSION['infoSite'] = [
        'nome'     => 'RoboParts',
        'slogan'   => 'Beyond Human.',
        'telefone' => '+55 11 99600-2280',
        'email'    => 'Roboparts@yahoo.com.br',
    ];
}

$dadosExistiam = dados_carregar();

if (!isset($_SESSION['usuarios'])) {
    $_SESSION['usuarios'] = [
        [
            'id'    => 1,
            'nome'  => 'Administrador',
            'email' => 'admin@roboparts.com',
            'senha' => password_hash('admin123', PASSWORD_DEFAULT),
            'admin' => true,
        ],
    ];
}

if (!isset($_SESSION['categorias'])) {
    $_SESSION['categorias'] = categorias_iniciais();
}

if (!isset($_SESSION['produtos'])) {
    $_SESSION['produtos'] = produtos_iniciais();
}

if (!$dadosExistiam) {
    dados_salvar();
}

define('LOJA_NOME',     $_SESSION['infoSite']['nome']);
define('LOJA_SLOGAN',   $_SESSION['infoSite']['slogan']);
define('LOJA_TELEFONE', $_SESSION['infoSite']['telefone']);
define('LOJA_EMAIL',    $_SESSION['infoSite']['email']);


function categorias_iniciais(): array
{
    $lista = [
        ['robos-industriais',   'Robôs industriais',   'Braços articulados, SCARA, delta e robôs de solda para linhas de produção.'],
        ['robos-humanoides',    'Robôs humanoides',    'Plataformas bípedes e sobre rodas, mãos robóticas e kits de pesquisa.'],
        ['robos-sociais',       'Robôs sociais',       'Robôs de atendimento, recepção, companhia e educação.'],
        ['robos-agricolas',     'Robôs agrícolas',     'Drones, robôs de monitoramento, capina e colheita para o campo.'],
        ['robos-colaborativos', 'Robôs colaborativos',  'Cobots que trabalham ao lado de pessoas, com garras e estações prontas.'],
    ];
    $saida = [];
    foreach ($lista as $i => [$slug, $nome, $descricao]) {
        $saida[] = [
            'id'        => $slug,
            'slug'      => $slug,
            'nome'      => $nome,
            'descricao' => $descricao,
            'imagem'    => null,
            'criado'    => $i + 1,
        ];
    }
    return $saida;
}


function produtos_iniciais(): array
{
    $agora = time();
    $itens = [

        ['robos-industriais', 'industrial-1', 'Braço robótico industrial 6 eixos RP-IR600', 89900, 104900,
            'Braço de seis eixos para solda ponto, manipulação e montagem. Controlador compacto com programação por teach pendant.',
            "Eixos: 6\nCarga útil: 6 kg\nAlcance: 900 mm\nRepetibilidade: ±0,03 mm\nAlimentação: 220 V"],
        ['robos-industriais', 'industrial-2', 'Robô SCARA de alta velocidade RP-SC400', 62500, 71900,
            'Robô SCARA para montagem rápida de componentes e inserção em placas, com baixa vibração em alta cadência.',
            "Eixos: 4\nCarga útil: 3 kg\nAlcance: 400 mm\nRepetibilidade: ±0,01 mm"],
        ['robos-industriais', 'industrial-3', 'Robô delta para picking RP-DL250', 74800, null,
            'Robô de cinemática paralela para separação e embalagem de itens leves em esteira.',
            "Eixos: 4\nCarga útil: 1 kg\nÁrea de trabalho: 250 mm\nCiclos: até 120 por minuto"],
        ['robos-industriais', 'industrial-4', 'Robô de solda MIG/MAG RP-WD800 com fonte', 118900, 134000,
            'Conjunto de robô, tocha e fonte de solda para cordões contínuos em estruturas metálicas.',
            "Eixos: 6\nCarga útil: 8 kg\nAlcance: 1.450 mm\nProteção: IP54"],

        ['robos-humanoides', 'humanoide-1', 'Robô humanoide bípede RP-H1', 248900, 279900,
            'Humanoide de pesquisa com marcha estável, câmera de profundidade e SDK aberto para desenvolvimento.',
            "Altura: 1,32 m\nPeso: 35 kg\nGraus de liberdade: 23\nAutonomia: 2 h\nSDK: Python e C++"],
        ['robos-humanoides', 'humanoide-2', 'Robô humanoide com rodas RP-H2 Wheel', 160000, 178000,
            'Corpo humanoide sobre base de rodas, para navegação autônoma em ambientes internos.',
            "Altura: 1,30 m\nGraus de liberdade: 18\nNavegação: LiDAR e câmera RGB\nAutonomia: 4 h"],
        ['robos-humanoides', 'humanoide-3', 'Mão robótica articulada de 5 dedos RP-HAND5', 18900, 21500,
            'Mão com cinco dedos independentes e sensores de força nas pontas, compatível com braços de 6 eixos.',
            "Dedos: 5\nGraus de liberdade: 15\nForça de preensão: 80 N\nComunicação: CAN e RS-485"],
        ['robos-humanoides', 'humanoide-4', 'Robô humanoide educacional RP-H0 Mini', 12990, 15490,
            'Humanoide de 45 cm para aulas de robótica e programação em escolas e laboratórios.',
            "Altura: 45 cm\nServos: 20\nProgramação: blocos e Python\nBateria: 1 h de uso"],

        ['robos-sociais', 'social-1', 'Robô de atendimento RP-S300 com tela de 10"', 69900, 79900,
            'Robô de balcão com reconhecimento de voz e facial, ideal para lojas, bancos e eventos.',
            "Tela: 10 polegadas\nIdiomas: português, inglês e espanhol\nConectividade: Wi-Fi e 4G\nAutonomia: 8 h"],
        ['robos-sociais', 'social-2', 'Robô recepcionista RP-S200', 38500, null,
            'Recepcionista móvel que recebe visitantes, informa horários e guia até o local desejado.',
            "Altura: 1,10 m\nNavegação: autônoma\nTela: 8 polegadas\nAutonomia: 10 h"],
        ['robos-sociais', 'social-3', 'Robô companheiro de mesa RP-Buddy', 1397, 2999,
            'Pequeno robô de mesa com expressões animadas, que reage a toque e voz.',
            "Altura: 12 cm\nSensores: toque, som e queda\nBateria: 6 h\nCarregamento: USB-C"],
        ['robos-sociais', 'social-4', 'Robô educacional de programação RP-Kid', 987, 1290,
            'Robô para crianças a partir de 6 anos aprenderem lógica de programação brincando.',
            "Idade: 6+\nProgramação: blocos visuais\nSensores: distância e linha\nBateria: 5 h"],

        ['robos-agricolas', 'agricola-1', 'Drone pulverizador agrícola RP-AG40 de 40 L', 84900, 96000,
            'Drone para pulverização e distribuição de insumos com mapeamento de rota por GPS RTK.',
            "Tanque: 40 L\nAutonomia: 20 min com carga\nFaixa de aplicação: 9 m\nPosicionamento: GPS RTK"],
        ['robos-agricolas', 'agricola-2', 'Robô autônomo de monitoramento de lavoura RP-FieldScout', 56700, null,
            'Robô de esteiras que percorre as fileiras e registra imagens para análise de pragas e falhas de plantio.',
            "Tração: esteiras\nAutonomia: 8 h\nCâmeras: RGB e infravermelho\nConectividade: 4G e rádio"],
        ['robos-agricolas', 'agricola-3', 'Robô de capina autônomo RP-Weeder', 92400, 105000,
            'Identifica plantas daninhas por visão computacional e remove sem uso de herbicida.',
            "Largura de trabalho: 2 m\nAlimentação: solar e bateria\nVelocidade: 2 km/h\nÁrea: até 3 ha por dia"],
        ['robos-agricolas', 'agricola-4', 'Robô de colheita de morangos RP-Berry', 138000, 154900,
            'Braço com visão para colher frutos maduros em estufas, sem danificar a planta.',
            "Ciclo: 5 s por fruto\nBraços: 2\nAmbiente: estufa\nAutonomia: 10 h"],

        ['robos-colaborativos', 'cobot-1', 'Cobot 6 eixos RP-C10 de 10 kg', 79900, 92900,
            'Cobot que trabalha sem grades de proteção, com parada automática ao detectar contato.',
            "Eixos: 6\nCarga útil: 10 kg\nAlcance: 1.300 mm\nRepetibilidade: ±0,05 mm\nNorma: ISO/TS 15066"],
        ['robos-colaborativos', 'cobot-2', 'Cobot 6 eixos RP-C5 de 5 kg', 54900, 62900,
            'Cobot leve para parafusamento, inspeção e alimentação de máquinas. Programação por arraste.',
            "Eixos: 6\nCarga útil: 5 kg\nAlcance: 850 mm\nPeso: 20 kg"],
        ['robos-colaborativos', 'cobot-3', 'Garra elétrica adaptativa RP-GR80 para cobots', 6490, 7490,
            'Garra de dois dedos com curso ajustável e controle de força, para vários formatos de peça.',
            "Curso: 80 mm\nForça: 10 a 80 N\nComunicação: Modbus RTU\nFixação: ISO 9409"],
        ['robos-colaborativos', 'cobot-4', 'Estação de paletização com cobot RP-PAL', 124900, 139900,
            'Estação pronta com cobot, base móvel e software de montagem de paletes sem programação.',
            "Altura de pilha: até 1,6 m\nCarga por caixa: 10 kg\nSetup: menos de 1 dia"],
    ];

    $saida = [];
    foreach ($itens as $i => [$cat, $img, $nome, $preco, $antigo, $desc, $specs]) {
        $saida[] = [
            'id'           => substr(md5($img), 0, 12),
            'categoria'    => $cat,
            'nome'         => $nome,
            'preco'        => (float) $preco,
            'preco_antigo' => $antigo ? (float) $antigo : null,
            'descricao'    => $desc,
            'specs'        => $specs,
            'imagem'       => $img,
            'criado'       => $agora - $i * 60,
        ];
    }
    return $saida;
}
