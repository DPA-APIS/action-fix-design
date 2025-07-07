<?php

$GREEN = "\033[32m";
$RED = "\033[31m";
$YELLOW = "\033[33m";
$RESET = "\033[0m";

// Obtener argumentos
$title = $argv[1] ?? '';
$version = $argv[2] ?? '';
$serverUrl = $argv[3] ?? '';
$apiType = $argv[4] ?? '';
$apiId = $argv[5] ?? '';

$errors = [];
if (empty($title)) {
    $errors[] = "info.title es requerido" 
}
if (empty($version)) {
    $errors[] = "info.version es requerido" 
}
if (empty($serverUrl)) {
    $errors[] = "servers[0].url es requerido";
}
if (empty($apiType)) {
    $errors[] = "Tipo de API(x-bcp-api-type ) es requerido" 
}
if (empty($apiId)) {
    $errors[] = "Identificador de API(x-bcp-api-id ) es requerido" 
}

$pattern = '/^\/channel\/([a-zA-Z0-9]{4})\/v(\d+)$/';
