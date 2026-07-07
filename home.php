<?php
session_start();

// ============================================================
// CARREGAR VARIÁVEIS DO .ENV
// ============================================================
function loadEnv($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (strlen($value) > 1) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
    return true;
}

loadEnv();

// ============================================================
// ⏱ INÍCIO DA MEDIÇÃO DE TEMPO DO SERVIDOR
// ============================================================
$serverStartTime = microtime(true);
$serverStartMemory = memory_get_usage();

// ============================================================
// FUNÇÕES DE CACHE E CONTROLE DE COTA
// ============================================================

// Verificar limite diário de requisições
function verificarLimiteDiario() {
    $cacheDir = __DIR__ . '/cache';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $hoje = date('Y-m-d');
    $arquivo = $cacheDir . '/limite_' . $hoje . '.json';
    
    if (!file_exists($arquivo)) {
        // Primeira requisição do dia
        $dados = [
            'requisicoes' => 1,
            'limite' => 1400,
            'data' => $hoje
        ];
        file_put_contents($arquivo, json_encode($dados));
        return true;
    }
    
    $dados = json_decode(file_get_contents($arquivo), true);
    
    if ($dados['requisicoes'] >= $dados['limite']) {
        return false; // Limite diário atingido
    }
    
    // Incrementar contador
    $dados['requisicoes']++;
    file_put_contents($arquivo, json_encode($dados));
    return true;
}

// Obter resposta do cache
function obterRespostaCache($userMessage) {
    $cacheDir = __DIR__ . '/cache/respostas';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/' . md5($userMessage) . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        return $data['reply'] ?? null;
    }
    return null;
}

// Guardar resposta no cache
function guardarRespostaCache($userMessage, $reply) {
    $cacheDir = __DIR__ . '/cache/respostas';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/' . md5($userMessage) . '.json';
    file_put_contents($cacheFile, json_encode([
        'reply' => $reply,
        'timestamp' => time()
    ]));
}

// Obter resposta de fallback (quando a cota acaba)
function obterRespostaFallback($userMessage) {
    $palavras = explode(' ', strtolower($userMessage));
    
    // Detectar perguntas sobre monografias
    if (in_array('monografias', $palavras) || in_array('tese', $palavras) || in_array('dissertação', $palavras)) {
        return "O SIBAM possui um acervo de monografias disponíveis para consulta. " .
               "Acesse a página de monografias para pesquisar por autor, tema ou área do conhecimento. " .
               "Para mais detalhes, contacte a biblioteca: sibam@uniluanda.edu.ao";
    }
    
    // Detectar perguntas sobre autores
    if (in_array('autor', $palavras) || in_array('autores', $palavras)) {
        return "Pode pesquisar monografias por autor na página de monografias do SIBAM. " .
               "Utilize os filtros de pesquisa para encontrar trabalhos de autores específicos.";
    }
    
    // Detectar perguntas sobre orientadores/tutores
    if (in_array('orientador', $palavras) || in_array('tutor', $palavras) || in_array('orientadores', $palavras)) {
        return "Pode pesquisar monografias por orientador na página de monografias do SIBAM. " .
               "Utilize os filtros de pesquisa para encontrar trabalhos orientados por professores específicos.";
    }
    
    // Detectar perguntas sobre submissão
    if (in_array('submeter', $palavras) || in_array('submissão', $palavras) || in_array('enviar', $palavras)) {
        return "Para submeter uma monografia no SIBAM: " .
               "1. Faça login com a sua conta de estudante\n" .
               "2. Clique no botão 'Submissão' na página inicial\n" .
               "3. Preencha todos os metadados obrigatórios\n" .
               "4. Carregue o PDF da monografia\n" .
               "5. Clique em 'Registar'\n" .
               "A biblioteca analisa a submissão em até 5 dias úteis.";
    }
    
    // Resposta genérica
    return "O SIBAM é o Sistema de Busca Avançada de Monografias da UNILUANDA. " .
           "Posso ajudar com informações sobre monografias, autores, orientadores, submissão e consulta ao acervo. " .
           "Para mais informações, consulte o menu Ajuda ou contacte: sibam@uniluanda.edu.ao";
}

// ============================================================
// FUNÇÕES PARA BUSCAR MONOGRAFIAS NO BANCO DE DADOS
// ============================================================
function buscarMonografiasRelacionadas($conn, $userMessage) {
    $resultados = [
        'monografias' => [],
        'total' => 0,
        'termos_buscados' => []
    ];
    
    // Extrair palavras-chave da pergunta (termos com mais de 3 letras)
    $palavras = preg_split('/\s+/', $userMessage);
    $termos = array_filter($palavras, function($p) {
        return strlen($p) > 3;
    });
    
    if (empty($termos)) {
        return $resultados;
    }
    
    $resultados['termos_buscados'] = array_values($termos);
    
    // Construir consulta para buscar monografias relacionadas
    $sql = "SELECT m.id, m.titulo, m.resumo, m.ano_defesa, m.data_inclusao,
                   a.nome as autor_nome, o.nome as orientador_nome,
                   ac.nome as area_nome, ac.descricao as area_descricao,
                   GROUP_CONCAT(DISTINCT p.palavra SEPARATOR ', ') as palavras_chave
            FROM monografias m
            LEFT JOIN monografia_autor ma ON m.id = ma.id_monografia
            LEFT JOIN autores a ON ma.id_autor = a.id_autor
            LEFT JOIN orientador o ON m.id_orientador = o.id_orientador
            LEFT JOIN area_conhecimento ac ON m.id_area = ac.id_area
            LEFT JOIN monografia_palavra mp ON m.id = mp.id_monografia
            LEFT JOIN palavra_chave p ON mp.id_palavra = p.id_palavra
            WHERE m.status = 'aprovado'
            AND (";
    
    $params = [];
    $conditions = [];
    
    // Buscar por termos no título, resumo, autor, orientador, área e palavras-chave
    foreach ($termos as $termo) {
        $conditions[] = "m.titulo LIKE ?";
        $params[] = "%$termo%";
        $conditions[] = "m.resumo LIKE ?";
        $params[] = "%$termo%";
        $conditions[] = "a.nome LIKE ?";
        $params[] = "%$termo%";
        $conditions[] = "o.nome LIKE ?";
        $params[] = "%$termo%";
        $conditions[] = "p.palavra LIKE ?";
        $params[] = "%$termo%";
        $conditions[] = "ac.nome LIKE ?";
        $params[] = "%$termo%";
    }
    
    $sql .= implode(" OR ", $conditions);
    $sql .= ") GROUP BY m.id ORDER BY m.ano_defesa DESC LIMIT 10";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $resultados['monografias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultados['total'] = count($resultados['monografias']);
    } catch(PDOException $e) {
        error_log("Erro ao buscar monografias relacionadas: " . $e->getMessage());
    }
    
    return $resultados;
}

function formatarMonografiasParaContexto($monografias) {
    if (empty($monografias)) {
        return "Nenhuma monografia encontrada no acervo relacionada à sua pergunta.";
    }
    
    $texto = "Encontrei " . count($monografias) . " monografias relacionadas:\n\n";
    
    foreach ($monografias as $i => $m) {
        $texto .= "### Monografia " . ($i + 1) . "\n";
        $texto .= "**Título:** " . ($m['titulo'] ?? 'Título não disponível') . "\n";
        $texto .= "**Autor:** " . ($m['autor_nome'] ?? 'Autor não disponível') . "\n";
        $texto .= "**Orientador:** " . ($m['orientador_nome'] ?? 'Orientador não disponível') . "\n";
        $texto .= "**Ano:** " . ($m['ano_defesa'] ?? 'Ano não disponível') . "\n";
        $texto .= "**Área:** " . ($m['area_nome'] ?? 'Área não disponível') . "\n";
        $texto .= "**Palavras-chave:** " . ($m['palavras_chave'] ?? 'Não disponíveis') . "\n";
        $texto .= "**Resumo:** " . ($m['resumo'] ?? 'Resumo não disponível') . "\n";
        $texto .= "---\n\n";
    }
    
    return $texto;
}

// ============================================================
// TRATAMENTO DA API DO GEMINI - Requisições do chat
// ============================================================
$isChatRequest = $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isChatRequest) {
    header('Content-Type: application/json; charset=utf-8');

    $rawBody = file_get_contents('php://input');
    $input = json_decode($rawBody, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
        echo json_encode(['error' => 'Corpo da requisição inválido.']);
        exit;
    }

    $userMessage = trim($input['message'] ?? '');

    if ($userMessage === '') {
        echo json_encode(['error' => 'Mensagem vazia']);
        exit;
    }

    // ============================================================
    // 1. VERIFICAR CACHE
    // ============================================================
    $respostaCache = obterRespostaCache($userMessage);
    if ($respostaCache) {
        echo json_encode([
            'reply' => $respostaCache,
            '_cache' => true,
            '_message' => 'Resposta do cache (economizou uma requisição)'
        ]);
        exit;
    }

    // ============================================================
    // 2. VERIFICAR LIMITE DIÁRIO
    // ============================================================
    if (!verificarLimiteDiario()) {
        // Usar fallback em vez de erro
        $respostaFallback = obterRespostaFallback($userMessage);
        guardarRespostaCache($userMessage, $respostaFallback);
        echo json_encode([
            'reply' => "⚠️ Limite diário de perguntas atingido. Usando resposta pré-definida.\n\n" . $respostaFallback,
            '_fallback' => true,
            '_message' => 'Limite diário atingido. As respostas voltam ao normal amanhã (08:00 WAT).'
        ]);
        exit;
    }

    // ============================================================
    // 3. 🔑 CARREGAR CHAVE DO .ENV
    // ============================================================
    $apiKey = getenv('GEMINI_API_KEY');
    if ($apiKey === false || $apiKey === '') {
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
    }

    if (empty($apiKey)) {
        error_log("GEMINI_API_KEY não encontrada no .env");
        echo json_encode(['error' => 'Chave da API não configurada.']);
        exit;
    }

    // ============================================================
    // 4. 🔍 BUSCAR MONOGRAFIAS RELACIONADAS NO BANCO DE DADOS
    // ============================================================
    require_once __DIR__ . '/includes/config.php';
    $database = new Database();
    $conn = $database->getConnection();

    $monografiasRelacionadas = buscarMonografiasRelacionadas($conn, $userMessage);
    
    // Limitar a 3 monografias para economizar tokens
    $monografiasLimitadas = array_slice($monografiasRelacionadas['monografias'], 0, 3);
    $contextoMonografias = formatarMonografiasParaContexto($monografiasLimitadas);

    // ============================================================
    // 5. 📝 PROMPT OTIMIZADO (menos tokens)
    // ============================================================
    $systemPrompt = "Assistente SIBAM-UNILUANDA. Ajuda com monografias, autores, orientadores, temas e plataforma. Responde direto, em pt-PT. Sem saudações.";

    $promptFinal = $systemPrompt . "\n\n" .
        "Pergunta: " . $userMessage . "\n\n" .
        "Monografias encontradas:\n" . $contextoMonografias . "\n\n" .
        "Responda com base nos dados acima:";

    // ============================================================
    // 6. 🔥 CHAMADA PARA A API GEMINI (com fallback de modelos)
    // ============================================================
    $modelos = [
        'gemini-2.0-flash-lite',
        'gemini-1.5-flash',
        'gemini-2.0-flash'
    ];
    
    $resposta = null;
    $ultimoErro = null;
    
    foreach ($modelos as $modelo) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key=" . $apiKey;

        $postData = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $promptFinal]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 300,  // Reduzido para economizar tokens
                'topP' => 0.95,
                'topK' => 40,
                'thinkingConfig' => [
                    'thinkingBudget' => 0
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $caBundle = __DIR__ . '/cacert.pem';
        if (file_exists($caBundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $ultimoErro = "cURL error: $curlError";
            continue;
        }

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $candidate = $result['candidates'][0] ?? null;
            $parts = $candidate['content']['parts'] ?? [];

            $reply = '';
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $reply .= $part['text'];
                }
            }
            $reply = trim($reply);

            if ($reply !== '') {
                $resposta = $reply;
                $modeloUsado = $modelo;
                break;
            }
        }
        
        // Verificar se é erro de quota
        $errorDetail = json_decode($response, true);
        $errorMsg = $errorDetail['error']['message'] ?? '';
        if (strpos($errorMsg, 'quota') !== false || strpos($errorMsg, 'Quota') !== false) {
            $ultimoErro = "Quota exceeded for model $modelo";
            continue; // Tentar próximo modelo
        }
        
        if ($httpCode !== 200) {
            $ultimoErro = "HTTP $httpCode: " . ($errorDetail['error']['message'] ?? 'Erro desconhecido');
        }
    }

    // ============================================================
    // 7. PROCESSAR RESPOSTA
    // ============================================================
    if ($resposta) {
        // Guardar no cache
        guardarRespostaCache($userMessage, $resposta);
        
        echo json_encode([
            'reply' => $resposta,
            '_model' => $modeloUsado ?? 'desconhecido',
            '_debug' => [
                'total_encontrado' => $monografiasRelacionadas['total'] ?? 0,
                'termos_buscados' => $monografiasRelacionadas['termos_buscados'] ?? []
            ]
        ]);
    } else {
        // Fallback quando todos os modelos falham
        $respostaFallback = obterRespostaFallback($userMessage);
        guardarRespostaCache($userMessage, $respostaFallback);
        
        echo json_encode([
            'reply' => "⚠️ Serviço temporariamente indisponível. Usando resposta pré-definida.\n\n" . $respostaFallback,
            '_fallback' => true,
            '_error' => $ultimoErro
        ]);
    }
    exit;
}

// ============================================================
// CÓDIGO NORMAL DA PÁGINA (HTML, SESSÕES, ETC.)
// ============================================================

$_SESSION['user_foto'] = $_SESSION['user_foto'] ?? 'User.jpg';
$_SESSION['user_nome'] = $_SESSION['user_nome'] ?? 'Usuário';
$_SESSION['user_tipo'] = $_SESSION['user_tipo'] ?? 'visitante';

require_once __DIR__ . '/includes/config.php';

$database = new Database();
$conn = $database->getConnection();

// Total de monografias aprovadas
$totalMonografias = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM monografias WHERE status = 'aprovado'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalMonografias = (int)$row['total'];
} catch(PDOException $e) {
    error_log("Erro na consulta: " . $e->getMessage());
}

// Inicializa variáveis de notificação
$unreadMessagesCount = 0;
$recentUnreadMessages = [];

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $user_tipo = $_SESSION['user_tipo'];

    if ($user_tipo === 'admin') {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM mensagens WHERE status = 'nao lida'");
            $unreadMessagesCount = $stmt->fetchColumn();

            $stmt = $conn->prepare("SELECT id, nome, assunto, data_envio FROM mensagens WHERE status = 'nao lida' ORDER BY data_envio DESC LIMIT 5");
            $stmt->execute();
            $recentUnreadMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar mensagens admin: " . $e->getMessage());
        }
    } elseif ($user_tipo === 'estudante') {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM mensagens_internas WHERE destinatario_id = ? AND lida = 0");
            $stmt->execute([$user_id]);
            $unreadMessagesCount = $stmt->fetchColumn();

            $stmt = $conn->prepare("
                SELECT mi.id, mi.conversa_id, c.assunto, mi.mensagem, mi.data_envio,
                       u.nome as remetente_nome
                FROM mensagens_internas mi
                JOIN conversas c ON mi.conversa_id = c.id
                LEFT JOIN usuarios u ON mi.remetente_id = u.id
                WHERE mi.destinatario_id = ? AND mi.lida = 0
                ORDER BY mi.data_envio DESC
                LIMIT 5
            ");
            $stmt->execute([$user_id]);
            $recentUnreadMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar mensagens estudante: " . $e->getMessage());
        }
    }
}

// ============================================================
// 👋 BOAS-VINDAS AO UTILIZADOR (mostrado uma vez após o login)
// ============================================================
$showWelcome = false;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if (empty($_SESSION['welcome_shown'])) {
        $showWelcome = true;
        $_SESSION['welcome_shown'] = true;
    }
}

// ============================================================
// ⏱ CÁLCULO DO TEMPO DE EXECUÇÃO DO SERVIDOR
// ============================================================
$serverEndTime = microtime(true);
$serverEndMemory = memory_get_usage();

$serverExecutionTime = $serverEndTime - $serverStartTime;
$serverMemoryUsed = $serverEndMemory - $serverStartMemory;
$serverMemoryPeak = memory_get_peak_usage();

$timeFormatted = number_format($serverExecutionTime, 4);
$memoryFormatted = number_format($serverMemoryUsed / 1024, 2);
$memoryPeakFormatted = number_format($serverMemoryPeak / 1024 / 1024, 2);

$message = '';
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBAM - UNILUANDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- ============================================================
    ⏱ MEDIÇÃO DE PERFORMANCE DO FRONTEND
    ============================================================ -->
    <script>
        const pageStartTime = performance.timing ? performance.timing.navigationStart : performance.now();
        let apiResponseTime = 0;
        
        window.addEventListener('load', function() {
            let totalTime;
            if (performance.timing) {
                totalTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            } else {
                totalTime = performance.now() - pageStartTime;
            }
            
            const totalSeconds = totalTime / 1000;
            const timeValue = document.querySelector('.perf-value-time');
            if (timeValue) {
                timeValue.textContent = totalSeconds.toFixed(3) + 's';
                timeValue.className = 'perf-value perf-value-time';
                if (totalSeconds > 1.5) {
                    timeValue.classList.add('danger');
                } else if (totalSeconds > 0.8) {
                    timeValue.classList.add('warning');
                }
            }
            console.log('⏱ Total Load Time: ' + totalSeconds.toFixed(3) + 's');
        });
    </script>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gold-color: #f1c40f;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .performance-panel {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.85);
            color: #00ff88;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(0, 255, 136, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            min-width: 200px;
            transition: all 0.3s ease;
            cursor: pointer;
            user-select: none;
        }
        .performance-panel:hover {
            background: rgba(0, 0, 0, 0.95);
            transform: scale(1.05);
            border-color: #00ff88;
        }
        .performance-panel .perf-title {
            color: #888;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .performance-panel .perf-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 1px 0;
        }
        .performance-panel .perf-label {
            color: #aaa;
        }
        .performance-panel .perf-value {
            color: #00ff88;
            font-weight: bold;
        }
        .performance-panel .perf-value.warning {
            color: #ffaa00;
        }
        .performance-panel .perf-value.danger {
            color: #ff4444;
        }
        .performance-panel .perf-value.memory {
            color: #66ccff;
        }
        .performance-panel .perf-value.api-time {
            color: #ff66aa;
        }
        .performance-panel .perf-value.api-time.fast {
            color: #4CAF50;
        }
        .performance-panel .perf-value.api-time.medium {
            color: #FF9800;
        }
        .performance-panel .perf-value.api-time.slow {
            color: #f44336;
        }
        .performance-panel .perf-toggle {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #333;
            color: #aaa;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .performance-panel .perf-toggle:hover {
            background: #ff4444;
            color: white;
        }
        .performance-panel .perf-details {
            display: none;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #333;
        }
        .performance-panel .perf-details.show {
            display: block;
        }
        .performance-panel .perf-detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }
        .performance-panel .perf-detail-row .label {
            color: #888;
        }
        .performance-panel .perf-detail-row .value {
            color: #ccc;
        }
        .performance-panel .perf-api-row {
            border-top: 1px solid #333;
            padding-top: 4px;
            margin-top: 4px;
        }
        .performance-panel .perf-api-row .label {
            color: #888;
        }
        .performance-panel .perf-api-row .value {
            color: #ff66aa;
            font-weight: bold;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 1rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--gold-color) !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--light-color) !important;
            transform: scale(1.02);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            background-color: rgba(107, 101, 12, 0.2);
            transform: translateY(-2px);
        }

        .nav-link i {
            margin-right: 8px;
        }

        .hero-section {
            background: linear-gradient(rgba(28, 39, 50, 0.9), rgba(39, 55, 72, 0.9)), url('assets/images/ipgest.png');
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 5rem 0;
            margin-bottom: 5rem;
        }

        .hero-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-lead {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-hero {
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            margin: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .feature-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--secondary-color);
        }

        footer {
            background: var(--dark-color);
            color: var(--gold-color) !important;
            margin-top: auto;
        }

        .footer-links a {
            color: var(--gold-color) !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--light-color) !important;
            padding-left: 5px;
        }

        .social-icon {
            color: var(--gold-color) !important;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            color: var(--light-color) !important;
            transform: scale(1.2);
        }

        .user-avatar {
            border: 2px solid white;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
            border-color: var(--gold-color);
        }

        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .dropdown-item {
            padding: 0.5rem 1.5rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--secondary-color);
            color: white;
            padding-left: 2rem;
        }

        .animate-delay-1 {
            animation-delay: 0.2s;
        }

        .animate-delay-2 {
            animation-delay: 0.4s;
        }

        .animate-delay-3 {
            animation-delay: 0.6s;
        }

        .notification-dropdown {
            width: 300px;
        }
        .notification-item {
            white-space: normal;
            border-bottom: 1px solid #f0f0f0;
        }
        .notification-item:last-child {
            border-bottom: none;
        }

        /* Welcome Toast */
        .welcome-toast {
            z-index: 2000;
        }

        /* Chat Widget Styles */
        .chat-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .chat-button {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .chat-button:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, #2c3e50, #3498db);
        }
        .chat-button i {
            color: white;
            font-size: 28px;
        }
        .chat-modal {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1001;
            font-family: 'Poppins', sans-serif;
        }
        .chat-modal.show {
            display: flex;
        }
        .chat-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-header h6 {
            margin: 0;
            font-weight: 600;
        }
        .chat-header .close-chat {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .message {
            max-width: 85%;
            padding: 8px 12px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
        }
        .user-message {
            background: #3498db;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }
        .bot-message {
            background: #e9ecef;
            color: #2c3e50;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }
        .bot-message .api-time-badge {
            font-size: 9px;
            opacity: 0.7;
            margin-left: 8px;
            background: rgba(0,0,0,0.1);
            padding: 1px 6px;
            border-radius: 10px;
            display: inline-block;
        }
        .bot-message .api-time-badge.fast {
            color: #4CAF50;
        }
        .bot-message .api-time-badge.medium {
            color: #FF9800;
        }
        .bot-message .api-time-badge.slow {
            color: #f44336;
        }
        .bot-message .cache-badge {
            font-size: 9px;
            color: #66ccff;
            margin-left: 8px;
            background: rgba(102, 204, 255, 0.2);
            padding: 1px 6px;
            border-radius: 10px;
            display: inline-block;
        }
        .bot-message .fallback-badge {
            font-size: 9px;
            color: #ffaa00;
            margin-left: 8px;
            background: rgba(255, 170, 0, 0.2);
            padding: 1px 6px;
            border-radius: 10px;
            display: inline-block;
        }
        .chat-input-area {
            display: flex;
            padding: 10px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        .chat-input-area input {
            flex: 1;
            border: 1px solid #ced4da;
            border-radius: 25px;
            padding: 8px 15px;
            outline: none;
        }
        .chat-input-area button {
            background: #3498db;
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .chat-input-area button:hover {
            background: #2c3e50;
        }
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            background: #e9ecef;
            border-radius: 18px;
            align-self: flex-start;
        }
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #6c757d;
            border-radius: 50%;
            animation: blink 1.4s infinite both;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink {
            0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
            40% { opacity: 1; transform: scale(1.2); }
        }

        .chat-loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #e9ecef;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Indicador de limite diário */
        .quota-indicator {
            font-size: 10px;
            color: #888;
            padding: 2px 8px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
        }
        .quota-indicator.warning {
            color: #ffaa00;
            background: rgba(255,170,0,0.2);
        }
        .quota-indicator.danger {
            color: #ff4444;
            background: rgba(255,68,68,0.2);
        }
        .quota-indicator.available {
            color: #4CAF50;
            background: rgba(76,175,80,0.2);
        }
    </style>
</head>
<body>

<!-- ============================================================
👋 TOAST DE BOAS-VINDAS (mostrado uma vez após o login)
============================================================ -->
<?php if ($showWelcome): ?>
<div class="toast-container position-fixed top-0 start-50 translate-middle-x mt-3 welcome-toast">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="welcomeToast">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-hand-sparkles me-2"></i>
                Bem-vindo(a) de volta, <strong><?php echo htmlspecialchars($_SESSION['user_nome']); ?></strong>!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================
⏱ PAINEL DE PERFORMANCE
============================================================ -->
<div class="performance-panel" id="performancePanel" onclick="togglePerformanceDetails()">
    <button class="perf-toggle" onclick="event.stopPropagation(); togglePerformancePanel()">✕</button>
    <div class="perf-title">⏱ Performance</div>
    <div class="perf-row">
        <span class="perf-label">Tempo:</span>
        <span class="perf-value perf-value-time <?php echo $serverExecutionTime > 1.0 ? 'warning' : ($serverExecutionTime > 2.0 ? 'danger' : ''); ?>">
            <?php echo $timeFormatted; ?>s
        </span>
    </div>
    <div class="perf-row">
        <span class="perf-label">Memória:</span>
        <span class="perf-value memory"><?php echo $memoryFormatted; ?> KB</span>
    </div>
    <div class="perf-row perf-api-row" id="apiTimeRow" style="display:none;">
        <span class="perf-label">API Gemini:</span>
        <span class="perf-value api-time" id="apiTimeValue">--</span>
    </div>
    <div class="perf-details" id="perfDetails">
        <div class="perf-detail-row">
            <span class="label">Pico de memória:</span>
            <span class="value"><?php echo $memoryPeakFormatted; ?> MB</span>
        </div>
        <div class="perf-detail-row">
            <span class="label">Página:</span>
            <span class="value"><?php echo basename($_SERVER['PHP_SELF']); ?></span>
        </div>
        <div class="perf-detail-row">
            <span class="label">Data/Hora:</span>
            <span class="value"><?php echo date('d/m/Y H:i:s'); ?></span>
        </div>
        <div class="perf-detail-row">
            <span class="label">PHP versão:</span>
            <span class="value"><?php echo phpversion(); ?></span>
        </div>
        <div class="perf-detail-row">
            <span class="label">Servidor:</span>
            <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
        </div>
        <div class="perf-detail-row">
            <span class="label">Cache ativo:</span>
            <span class="value" style="color:#66ccff;">✅ Sim</span>
        </div>
    </div>
</div>

<!-- Chat Widget -->
<div class="chat-widget">
    <div class="chat-button" id="chatButton">
        <i class="fas fa-comment-dots"></i>
    </div>
    <div class="chat-modal" id="chatModal">
        <div class="chat-header">
            <h6><i class="fas fa-robot me-2"></i>Agente SIBAM <span style="font-size:10px;opacity:0.7;">(Gemini)</span></h6>
            <button class="close-chat" id="closeChatBtn">&times;</button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="message bot-message">
                Olá! Sou o assistente virtual do SIBAM-UNILUANDA. 
                Posso ajudar com pesquisas sobre monografias, autores, orientadores, temas e tudo sobre a plataforma.
                <span style="display:block;font-size:10px;color:#888;margin-top:4px;">
                    ⚡ Limite diário: 1400 perguntas (reinicia às 08:00 WAT)
                </span>
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chatInput" placeholder="Pesquisar monografias, autores, temas..." autocomplete="off">
            <button id="sendChatBtn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="fas fa-book-open me-2"></i>SIBAM-UNILUANDA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLinks">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarLinks">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="sobre.php"><i class="fas fa-info-circle"></i> Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="monografias.php"><i class="fas fa-book"></i> Monografias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contactos.php"><i class="fas fa-envelope"></i> Contactos</a>
                </li>
            </ul>
        </div>
        <div class="d-flex align-items-center">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>

                <?php if ($unreadMessagesCount > 0): ?>
                <div class="dropdown me-3">
                    <button class="btn btn-outline-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $unreadMessagesCount > 9 ? '9+' : $unreadMessagesCount; ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                        <li><h6 class="dropdown-header">Mensagens não lidas (<?php echo $unreadMessagesCount; ?>)</h6></li>
                        <?php if (!empty($recentUnreadMessages)): ?>
                            <?php foreach ($recentUnreadMessages as $msg): ?>
                                <li>
                                    <?php if ($_SESSION['user_tipo'] === 'admin'): ?>
                                        <a class="dropdown-item notification-item" href="admin/mensagens.php#mensagem-<?php echo $msg['id']; ?>">
                                            <strong><?php echo htmlspecialchars($msg['nome']); ?></strong>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($msg['assunto']); ?></small>
                                            <small class="text-muted"><?php echo date('d/m H:i', strtotime($msg['data_envio'])); ?></small>
                                        </a>
                                    <?php else: ?>
                                        <a class="dropdown-item notification-item" href="minhas_mensagens.php?conversa=<?php echo $msg['conversa_id']; ?>">
                                            <strong><?php echo htmlspecialchars($msg['remetente_nome'] ?? 'Administrador'); ?></strong>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($msg['assunto']); ?></small>
                                            <small class="text-muted"><?php echo date('d/m H:i', strtotime($msg['data_envio'])); ?></small>
                                        </a>
                                    <?php endif; ?>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endforeach; ?>
                            <li>
                                <?php if ($_SESSION['user_tipo'] === 'admin'): ?>
                                    <a class="dropdown-item text-center" href="admin/mensagens.php">Ver todas as mensagens</a>
                                <?php else: ?>
                                    <a class="dropdown-item text-center" href="minhas_mensagens.php">Ver todas as mensagens</a>
                                <?php endif; ?>
                            </li>
                        <?php else: ?>
                            <li><span class="dropdown-item-text text-muted">Nenhuma mensagem não lida</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="assets/images/avatar/<?php echo htmlspecialchars($_SESSION['user_foto']); ?>"
                             alt="Foto de Perfil"
                             class="rounded-circle me-2 user-avatar"
                             width="32"
                             height="32"
                             onerror="this.src='assets/images/avatar/default.png'">
                        <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a></li>
                        <li><a class="dropdown-item" href="minhas_mensagens.php"><i class="fas fa-envelope me-2"></i> Minhas Mensagens</a></li>
                        <?php if ($_SESSION['user_tipo'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Painel</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <button class="btn btn-light btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-sign-in-alt me-2"></i> Entrar
                </button>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="hero-title animate__animated animate__fadeInDown"><i class="fas fa-graduation-cap me-3"></i>Sistema de Busca Avançada de Monografias</h1>
        <p class="hero-lead animate__animated animate__fadeIn animate__delay-1s">Acesse, envie e gerencie monografias com facilidade. Plataforma acadêmica da UNILUANDA.</p>

        <div class="mt-4 animate__animated animate__fadeIn animate__delay-2s">
            <a href="monografias.php" class="btn btn-primary btn-hero mx-2"><i class="fas fa-search me-2"></i> Explorar Monografias</a>

            <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_tipo'] === 'admin'): ?>
                <button type="button" class="btn btn-outline-light btn-hero mx-2" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="fas fa-user-plus me-2"></i> Registar-se</button>
            <?php else: ?>
                <button class="btn btn-outline-light btn-hero mx-2" disabled title="Funcionalidade disponível apenas para visitantes e administradores"><i class="fas fa-user-lock me-2"></i> Registar-se</button>
            <?php endif; ?>

            <a href="galerias.php" class="btn btn-warning btn-hero mx-2"><i class="fas fa-images me-2"></i> Explorar Galerias</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card feature-card animate__animated animate__fadeInUp animate-delay-1">
                    <div class="card-body p-4">
                        <i class="fas fa-upload feature-icon"></i>
                        <h3 class="mb-3">Submissão</h3>
                        <p class="mb-4">Envie sua monografia de forma simples e rápida.</p>

                        <?php
                        $canSubmit = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && ($_SESSION['user_tipo'] === 'estudante' || $_SESSION['user_tipo'] === 'admin');
                        ?>

                        <?php if ($canSubmit): ?>
                           <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSubmissao">Saiba mais</button>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled title="Faça login como estudante ou administrador para acessar"><i class="fas fa-lock me-2"></i> Saiba mais</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card animate__animated animate__fadeInUp animate-delay-2">
                    <div class="card-body p-4">
                        <i class="fas fa-search feature-icon"></i>
                        <h3 class="mb-3">Consulta</h3>
                        <p class="mb-4">Acesse o acervo completo de monografias disponíveis.</p>
                        <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_tipo'] === 'visitante' || $_SESSION['user_tipo'] === 'estudante' || $_SESSION['user_tipo'] === 'admin'): ?>
                            <a href="monografias.php" class="btn btn-outline-primary">Consultar</a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled title="Funcionalidade disponível para todos os usuários"><i class="fas fa-lock me-2"></i> Consultar</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card animate__animated animate__fadeInUp animate-delay-3">
                    <div class="card-body p-4" >
                        <i class="fas fa-question-circle feature-icon"></i>
                        <h3 class="mb-3">Ajuda</h3>
                        <p class="mb-4">Tire suas dúvidas sobre o processo de submissão.</p>
                        <a href="ajuda.php" class="btn btn-outline-primary">Ajuda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Info Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-4">Explore o conhecimento acadêmico da UNILUANDA</h2>
                <p class="lead mb-4">Nosso sistema oferece acesso a centenas de monografias produzidas por estudantes e pesquisadores da nossa universidade.</p>
                <div class="d-flex">
                    <div class="me-4">
                        <h3 class="text-primary"><?php echo $totalMonografias; ?></h3>
                        <p>Monografias submetidas</p>
                    </div>
                    <div class="me-4">
                        <h3 class="text-primary">24/7</h3>
                        <p>Acesso permanente</p>
                    </div>
                    <div>
                        <h3 class="text-primary">100%</h3>
                        <p>Gratuito para alunos</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/biblioteca.jpg" alt="Biblioteca UNILUANDA" class="img-fluid rounded shadow" width="350px">
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-4">Links Rápidos</h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2"><a href="home.php"><i class="fas fa-chevron-right me-2"></i>Página Inicial</a></li>
                    <li class="mb-2"><a href="sobre.php"><i class="fas fa-chevron-right me-2"></i>Sobre o SIBAM</a></li>
                    <li class="mb-2"><a href="regulamento.php"><i class="fas fa-chevron-right me-2"></i>Regulamento</a></li>
                    <li class="mb-2"><a href="contactos.php"><i class="fas fa-chevron-right me-2"></i>Contactos</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-4">Contactos</h5>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-envelope me-2"></i> sibam@uniluanda.edu.ao</li>
                    <li class="mb-3"><i class="fas fa-phone me-2"></i> +244 937 161 868</li>
                    <li class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> Luanda, Angola</li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-4">Redes Sociais</h5>
                <div class="mb-4">
                    <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <small>© <?php echo date("Y"); ?> SIBAM - UNILUANDA. Todos os direitos reservados.</small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Modais -->
<?php include 'modals/login.php'; ?>
<?php include 'modals/register.php'; ?>
<?php include 'modals/submissao.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="js/script.js"></script>
<script>
    // ============================================================
    // 👋 EXIBIR TOAST DE BOAS-VINDAS
    // ============================================================
    <?php if ($showWelcome): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const welcomeToastEl = document.getElementById('welcomeToast');
        if (welcomeToastEl) {
            const welcomeToast = new bootstrap.Toast(welcomeToastEl, { delay: 5000 });
            welcomeToast.show();
        }
    });
    <?php endif; ?>

    // ============================================================
    // ⏱ FUNÇÕES DO PAINEL DE PERFORMANCE
    // ============================================================
    function togglePerformanceDetails() {
        const details = document.getElementById('perfDetails');
        details.classList.toggle('show');
    }

    function togglePerformancePanel() {
        const panel = document.getElementById('performancePanel');
        panel.style.display = 'none';
        const btn = document.createElement('div');
        btn.style.cssText = `
            position: fixed; bottom: 20px; left: 20px;
            background: rgba(0,0,0,0.7); color: #00ff88;
            padding: 8px 12px; border-radius: 50%;
            font-size: 18px; z-index: 9999;
            cursor: pointer; display: none;
            font-family: monospace;
        `;
        btn.innerHTML = '⏱';
        btn.id = 'perfReopenBtn';
        btn.title = 'Mostrar painel de performance';
        btn.onclick = function() {
            document.getElementById('performancePanel').style.display = 'block';
            this.style.display = 'none';
        };
        document.body.appendChild(btn);
        setTimeout(() => { btn.style.display = 'block'; }, 100);
    }

    // ============================================================
    // ⏱ ATUALIZAR TEMPO DA API NO PAINEL
    // ============================================================
    function updateApiTime(apiTime) {
        const apiRow = document.getElementById('apiTimeRow');
        const apiValue = document.getElementById('apiTimeValue');
        
        if (apiRow && apiValue) {
            apiRow.style.display = 'flex';
            apiValue.textContent = apiTime.toFixed(2) + 's';
            
            apiValue.className = 'perf-value api-time';
            if (apiTime < 1.5) {
                apiValue.classList.add('fast');
            } else if (apiTime < 3) {
                apiValue.classList.add('medium');
            } else {
                apiValue.classList.add('slow');
            }
        }
    }

    // ============================================================
    // SCROLL SUAVE
    // ============================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
        });
    });

    // ============================================================
    // ANIMAÇÃO DOS CARDS
    // ============================================================
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px)';
            card.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
        });
    });

    // ============================================================
    // CHAT WIDGET - COM CACHE E GESTÃO DE COTA
    // ============================================================
    const chatButton = document.getElementById('chatButton');
    const chatModal = document.getElementById('chatModal');
    const closeChatBtn = document.getElementById('closeChatBtn');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendChatBtn');

    function toggleChat() {
        chatModal.classList.toggle('show');
    }
    chatButton.addEventListener('click', toggleChat);
    closeChatBtn.addEventListener('click', toggleChat);

    function addMessage(text, isUser, apiTime = null, isCache = false, isFallback = false) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        
        if (!isUser) {
            let badges = '';
            if (isCache) {
                badges += `<span class="cache-badge">💾 Cache</span>`;
            }
            if (isFallback) {
                badges += `<span class="fallback-badge">⚡ Fallback</span>`;
            }
            if (apiTime !== null) {
                let badgeClass = 'fast';
                if (apiTime > 2) badgeClass = 'medium';
                if (apiTime > 4) badgeClass = 'slow';
                badges += `<span class="api-time-badge ${badgeClass}">⏱${apiTime.toFixed(1)}s</span>`;
            }
            msgDiv.innerHTML = text + badges;
        } else {
            msgDiv.textContent = text;
        }
        
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function hideTyping() {
        const typing = document.getElementById('typingIndicator');
        if (typing) typing.remove();
    }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        addMessage(message, true);
        chatInput.value = '';
        showTyping();

        const apiStartTime = performance.now();

        try {
            const response = await fetch('home.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: message })
            });

            const apiEndTime = performance.now();
            const apiResponseTime = (apiEndTime - apiStartTime) / 1000;

            let data;
            try {
                data = await response.json();
            } catch (parseErr) {
                hideTyping();
                addMessage('⚠️ O servidor respondeu de forma inesperada.', false);
                console.error('Resposta não era JSON válido:', parseErr);
                return;
            }

            hideTyping();
            
            if (data.error) {
                addMessage('⚠️ ' + data.error, false);
            } else {
                updateApiTime(apiResponseTime);
                const isCache = data._cache || false;
                const isFallback = data._fallback || false;
                addMessage(data.reply, false, apiResponseTime, isCache, isFallback);
                
                if (data._debug) {
                    console.log('📚 Monografias encontradas: ' + (data._debug.total_encontrado || 0));
                    console.log('🔍 Termos buscados:', data._debug.termos_buscados || []);
                }
                if (data._model) {
                    console.log('🤖 Modelo usado: ' + data._model);
                }
                if (data._message) {
                    console.log('ℹ️ ' + data._message);
                }
            }
        } catch (error) {
            hideTyping();
            addMessage('⚠️ Erro de conexão. Tente novamente.', false);
            console.error('Erro:', error);
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    document.addEventListener('click', function(e) {
        const widget = document.querySelector('.chat-widget');
        if (chatModal.classList.contains('show') && !widget.contains(e.target)) {
            chatModal.classList.remove('show');
        }
    });

    console.log('✅ SIBAM carregado em <?php echo $timeFormatted; ?>s');
    console.log('📊 Cache ativo: Sim');
    console.log('⏱ Limite diário: 1400 perguntas (reinicia às 08:00 WAT)');
</script>
</body>
</html>