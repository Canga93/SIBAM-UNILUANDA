<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

$filename = $_GET['file'] ?? '';
if (empty($filename) || strpos($filename, 'backup_') !== 0 || substr($filename, -4) !== '.sql') {
    die('Arquivo inválido');
}

$backup_dir = __DIR__ . '/../backups/';
$filepath = $backup_dir . basename($filename);

if (!file_exists($filepath)) {
    die('Arquivo não encontrado');
}

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($filepath);
exit;