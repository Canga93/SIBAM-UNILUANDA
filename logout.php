<?php
require_once 'includes/config.php';

// Destruir sessão
session_destroy();

// Redirecionar para home
header("Location: home.php?logout=success");
exit();