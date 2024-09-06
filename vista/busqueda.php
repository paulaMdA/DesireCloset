<?php
session_start();
require_once '../config/conexion.php';

$database = new Database();
$db = $database->getConnection();

$searchTerm = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Redirigir a la página correspondiente en función del término de búsqueda
if (!empty($searchTerm)) {
    // Redirigir a la página de la categoría correspondiente
    if (stripos($searchTerm, 'braga') !== false || stripos($searchTerm, 'tanga') !== false) {
        header('Location: braga.php?busqueda=' . urlencode($searchTerm));
    } elseif (stripos($searchTerm, 'sujetador') !== false) {
        header('Location: sujetadores.php?busqueda=' . urlencode($searchTerm));
    } elseif (stripos($searchTerm, 'pies') !== false) {
        header('Location: fotosdepie.php?busqueda=' . urlencode($searchTerm));
    } elseif (stripos($searchTerm, 'juguetes') !== false) {
        header('Location: juguetessexuales.php?busqueda=' . urlencode($searchTerm));
    } elseif (stripos($searchTerm, 'uso') !== false) {
        header('Location: uso.php');
    } elseif (stripos($searchTerm, 'condiciones') !== false) {
        header('Location: condiciones.php');
    } elseif (stripos($searchTerm, 'quienes') !== false) {
        header('Location: quienes.php');
    } elseif (stripos($searchTerm, 'faq') !== false) {
        header('Location: faq.php');
    } elseif (stripos($searchTerm, 'soporte') !== false) {
        header('Location: soporte.php');
    } elseif (stripos($searchTerm, 'seguridad') !== false) {
        header('Location: consejosseguridad.php');
    } else {
        // Si no coincide con ninguna categoría específica, buscar en todos los productos y usuarios
        header('Location: todos.php?busqueda=' . urlencode($searchTerm));
    }
} else {
    // Si no hay término de búsqueda, redirigir a la página principal
    header('Location: principal.php');
}
exit();
?>