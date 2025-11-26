<?php
include_once('../DB/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['rut_persona'])) {
        $_SESSION['mensaje'] = "Error: No se encontró el RUT en la sesión.";
        header("Location: profile.php");
        exit();
    }

    $rut_persona = $_SESSION['rut_persona'];

    // Comenzar transacción
    $pdo->beginTransaction();

    try {
        // Debug para verificar valores
        error_log("Eliminando perfil con RUT: " . $rut_persona);

        // Eliminar asignaciones de revisor en art_revisor
        $stmt = $pdo->prepare("DELETE FROM art_revisor WHERE rut_revisor = :rut");
        $stmt->execute([':rut' => $rut_persona]);

        // Buscar artículos en los que el usuario sea autor
        $stmt = $pdo->prepare("SELECT id_art FROM art_autor WHERE rut_persona = :rut");
        $stmt->execute([':rut' => $rut_persona]);
        $articulos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($articulos as $id_art) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM art_autor WHERE id_art = :id_art");
            $stmt->execute([':id_art' => $id_art]);
            $num_autores = $stmt->fetchColumn();

            if ($num_autores == 1) {
                $stmt = $pdo->prepare("DELETE FROM articulo WHERE id_art = :id_art");
                $stmt->execute([':id_art' => $id_art]);

                $stmt = $pdo->prepare("DELETE FROM art_topico WHERE id_art = :id_art");
                $stmt->execute([':id_art' => $id_art]);

                $stmt = $pdo->prepare("DELETE FROM art_autor WHERE id_art = :id_art");
                $stmt->execute([':id_art' => $id_art]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM art_autor WHERE id_art = :id_art AND rut_persona = :rut");
                $stmt->execute([':id_art' => $id_art, ':rut' => $rut_persona]);
            }
        }

        // Eliminar roles del usuario en rol_personas
        $stmt = $pdo->prepare("DELETE FROM rol_personas WHERE rut_persona = :rut");
        $stmt->execute([':rut' => $rut_persona]);

        // Finalmente, eliminar al usuario de la tabla persona
        $stmt = $pdo->prepare("DELETE FROM persona WHERE rut_persona = :rut");
        $stmt->execute([':rut' => $rut_persona]);

        // Confirmar transacción
        $pdo->commit();

        $_SESSION['mensaje'] = "Perfil eliminado correctamente.";
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        // Revertir transacción
        $pdo->rollBack();
        error_log("Error al eliminar el perfil: " . $e->getMessage());
        $_SESSION['mensaje'] = "Error al eliminar el perfil: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}
?>
