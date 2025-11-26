<?php
include_once('../DB/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_art'])) {
    $id_art = intval($_POST['ID_art']);

    try {
        $pdo->beginTransaction();

        // Eliminar de las tablas relacionadas
        $tables = ['art_autor', 'art_revisor', 'art_topico'];
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM $table WHERE ID_art = :id_art");
            $stmt->bindParam(':id_art', $id_art, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Eliminar de la tabla principal
        $stmt = $pdo->prepare("DELETE FROM articulo WHERE ID_art = :id_art");
        $stmt->bindParam(':id_art', $id_art, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();

        // Guardar mensaje en la sesión
        $_SESSION['mensaje'] = 'Artículo eliminado correctamente.';
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $_SESSION['mensaje'] = 'Ocurrió un error al intentar eliminar el artículo.';
    }

    // Redirigir de nuevo a la misma página
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    $_SESSION['mensaje'] = 'Solicitud inválida.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
