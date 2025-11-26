<?php
    include_once('../DB/db.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ID_art = $_POST['id_art'] ?? null;
    $nuevos = $_POST['nuevos_revisores'] ?? [];
    $actuales = $_POST['ruts_actuales'] ?? [];

    // Validación mínima
    if (empty($ID_art)) {
        die("ID del artículo no proporcionado.");
    }

    // Verificar si ya existen entradas para este artículo
    $check_sql = "SELECT COUNT(*) FROM art_revisor WHERE ID_art = ?";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([$ID_art]);
    $existen_filas = $stmt->fetchColumn() > 0;

    if (!$existen_filas) {
        // No hay datos en la tabla todavía, insertamos
        foreach ($actuales as $nuevo) {
            if (!empty($nuevo)) {
                $insert_sql = "INSERT INTO art_revisor (ID_art, rut_revisor) VALUES (?, ?)";
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute([$ID_art, $nuevo]);
            }
        }
    } else {
        // Ya existen, actualizamos si cambió algo
        if ($nuevos == $actuales) {
            // Igual al anterior, intentamos insertar por si acaso (protegido con try)
            try {
                foreach ($nuevos as $nuevo) {
                    if (!empty($nuevo)) {
                        $insert_sql = "INSERT INTO art_revisor (ID_art, rut_revisor) VALUES (?, ?)";
                        $stmt = $pdo->prepare($insert_sql);
                        $stmt->execute([$ID_art, $nuevo]);
                    }
                }
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    // Ya existen, no pasa nada
                } else {
                    throw $e;
                }
            }
        } else {
            // Actualizamos diferencias
            $update_sql = "UPDATE art_revisor SET rut_revisor = ? WHERE ID_art = ? AND rut_revisor = ?";
            foreach ($actuales as $i => $rut_actual) {
                $nuevo = $nuevos[$i] ?? '';
                if (!empty($nuevo) && $nuevo !== $rut_actual) {
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute([$nuevo, $ID_art, $rut_actual]);
                }
            }
        }
    }

    header("Location: asignar_revisores.php?ID_art=" . urlencode($ID_art));
    exit();
}
?>
