<?php
// Sanitizar entrada de dados
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Gerar hash de senha
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Upload de arquivo
function uploadFile($file, $destination) {
    // Criar diretório se não existir
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $destination . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    return false;
}

// Formatar data
function formatDate($date) {
    if (empty($date) || $date == '0000-00-00') {
        return 'N/A';
    }
    return date('d/m/Y', strtotime($date));
}

// Gerar paginação
function generatePagination($currentPage, $totalPages, $url) {
    if ($totalPages <= 1) return '';
    
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Botão anterior
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . ($currentPage - 1) . '">Anterior</a></li>';
    }
    
    // Páginas
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $startPage + 4);
    
    if ($endPage - $startPage < 4) {
        $startPage = max(1, $endPage - 4);
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = $i == $currentPage ? ' active' : '';
        $pagination .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . '&page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Próximo botão
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '&page=' . ($currentPage + 1) . '">Próximo</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    return $pagination;
}

// Redirecionar com mensagem
function redirectWithMessage($url, $type, $message) {
    header("Location: $url?$type=" . urlencode($message));
    exit();
}

// Verificar se é uma requisição AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Gerar token CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
if (!function_exists('formatDate')) {
    function formatDate($date){
        return date("d/m/Y H:i", strtotime($date));
    }
}

if (!function_exists('generatePagination')) {
    function generatePagination($current, $total, $url){
        $html = '<nav><ul class="pagination justify-content-center">';
        for($i=1;$i<=$total;$i++){
            $active = $i==$current ? 'active' : '';
            $html .= "<li class='page-item $active'><a class='page-link' href='$url?page=$i'>$i</a></li>";
        }
        $html .= '</ul></nav>';
        return $html;
    }
}
?>