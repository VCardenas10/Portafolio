<?php
session_start();
include_once('../DB/db.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_art = $_POST['ID_art'];
    $titulo = $_POST['titulo'];
    $resumen = $_POST['resumen'];
    $topicos = $_POST['topicos'];


    // Actualizar los datos del artículo
    $sql = "UPDATE articulo SET titulo = :titulo, resumen = :resumen WHERE ID_art = :id_art";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':resumen', $resumen);
    $stmt->bindParam(':id_art', $id_art);
    $stmt->execute();

    // Actualizar los tópicos asociados al artículo
    // Elimina los tópicos existentes
    $deleteTopicosSql = "DELETE FROM art_topico WHERE ID_art = :id_art";
    $deleteStmt = $pdo->prepare($deleteTopicosSql);
    $deleteStmt->bindParam(':id_art', $id_art);
    $deleteStmt->execute();

    if (!empty($topicos)) {
        $insertTopicoSql = "INSERT INTO art_topico (ID_art, ID_top) 
                            VALUES (:id_art, (SELECT ID_top FROM topico_especialidad WHERE topico = :topico))";
        $insertStmt = $pdo->prepare($insertTopicoSql);

        foreach ($topicos as $topico) {
            $insertStmt->bindParam(':id_art', $id_art, PDO::PARAM_INT);
            $insertStmt->bindParam(':topico', $topico, PDO::PARAM_STR);
            $insertStmt->execute();
        }
    }

    // Confirmar la transacción

    // Redirigir con un mensaje de éxito
    $_SESSION['mensaje'] = "Artículo actualizado correctamente.";
    header("Location: dashboard_autores.php");
    exit;
}
