<?php
include_once 'session.php';
include_once 'config.php'; // Inclui o config para pegar a BASE_URL

session_unset();
session_destroy();
// --- CORREÇÃO DO REDIRECIONAMENTO ---
header('Location: ' . BASE_URL . '/pages/index.php');
exit;
?>