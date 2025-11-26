<?php
include_once('../DB/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Datos del formulario
        $titulo = $_POST['titulo'];
        $resumen = $_POST['resumen'];
        $autores = $_POST['autor_nombre'];
        $rut_autores = $_POST['rut_autor'];
        $autor_contacto_index = $_POST['autor_contacto'];
        $topicos = $_POST['topicos'];
        $fecha = date('Y-m-d');

        // Iniciar transacción
        $pdo->beginTransaction();

        // Insertar artículo
        $stmt = $pdo->prepare("CALL insertar_articulo(:titulo, :resumen, :fecha, @new_id)");
        $stmt->execute([':titulo' => $titulo, ':resumen' => $resumen, ':fecha' => $fecha]);

        // Obtener el ID del nuevo artículo
        $new_id = $pdo->query("SELECT @new_id")->fetchColumn();

        // Insertar autores
        foreach ($autores as $index => $autor_nombre) {
            $rut = $rut_autores[$index];
            $es_contacto = ($index == $autor_contacto_index) ? 1 : 0;

            $stmt = $pdo->prepare("CALL insertar_autores(:id_art, :rut, :es_contacto)");
            $stmt->execute([':id_art' => $new_id, ':rut' => $rut, ':es_contacto' => $es_contacto]);
        }

        // Insertar tópicos
        foreach ($topicos as $topico_nombre) {
            $stmt = $pdo->prepare("CALL insertar_topico(:id_art, :topico_nombre)");
            $stmt->execute([':id_art' => $new_id, ':topico_nombre' => $topico_nombre]);
        }

        // Confirmar transacción
        $pdo->commit();
        $_SESSION['mensaje'] = 'Artículo subido correctamente.';
    } catch (Exception $e) {
        // Revertir transacción
        $pdo->rollBack();
        $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
