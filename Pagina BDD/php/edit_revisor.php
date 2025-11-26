<?php

    session_start();
    include_once('../DB/db.php');


    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $rut = $_POST['rut_persona'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $nueva_pass = $_POST['nueva_password'];
        

        #cambio de password
        if (!empty($nueva_pass)) {
            $hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
            $sql = "UPDATE persona SET nombre = ?, email = ?, password = ? WHERE rut_persona = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $email, $hash, $rut]);
        }

        #cambio de nombre
        $stmt = $pdo->prepare("UPDATE persona SET nombre = ?, email = ? WHERE rut_persona = ?");
        $stmt->execute([$nombre, $email, $rut]);
    
        header("Location: gestion_revisores.php?success=1");
        exit();
    }
    

?>