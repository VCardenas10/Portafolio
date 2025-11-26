<?php

include_once('../DB/db.php');


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $topicos = $_POST['topicos'] ?? [];

    $hash = password_hash($password, PASSWORD_DEFAULT);
        
    $sql_persona = "INSERT INTO persona (nombre, email, password, rut_persona) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql_persona);
    $stmt->execute([$nombre, $email, $hash, $rut]);

    $sql_rol_personas = "INSERT INTO rol_personas (rut_persona, Rol_ID) VALUES (?, 2)";
    $stmt = $pdo->prepare($sql_rol_personas);
    $stmt->execute([$rut]);

    if (!empty($topicos)) {
        $stmtTopico = $pdo->prepare("SELECT id_top FROM topico_especialidad WHERE topico = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO topico_revisor (rut_persona, ID_top) VALUES (?, ?)");

        foreach ($topicos as $nombreTopico) {
            $stmtTopico->execute([$nombreTopico]);
            $idTopico = $stmtTopico->fetchColumn();

            if ($idTopico) {
                $stmtInsert->execute([$rut, $idTopico]);
            }
        }
    } else {
        die("Error: Debes seleccionar al menos un tópico.");
    }

    header("Location: gestion_revisores.php");
    exit();
}

?>
