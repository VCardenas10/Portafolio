<?php

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    session_start();
    include_once('../DB/db.php');


    if (isset($_POST['rut_persona'])) {
        $rut = $_POST['rut_persona'];

        if ($rut !== '' && $rut !== null) {
            $stmt1 = $pdo->prepare("DELETE FROM topico_revisor WHERE rut_persona = ?");
            $stmt2 = $pdo->prepare("DELETE FROM rol_personas WHERE rut_persona = ?");
            $stmt3 = $pdo->prepare("DELETE FROM persona WHERE rut_persona = ?");
            $stmt1->execute([$rut]);
            $stmt2->execute([$rut]);
            $stmt3->execute([$rut]);
            header("Location: gestion_revisores.php?mensaje=eliminado");
            exit();
        } else {
            echo "Error: Rut inválido.";
        }
    }
?>
