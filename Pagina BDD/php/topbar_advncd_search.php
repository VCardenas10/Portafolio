<?php

include_once('../DB/db.php');



if (isset($_GET['categoria']) && isset($_GET['termino'])) {
    $categoria = $_GET['categoria'];
    $termino = $_GET['termino'];

    echo "Buscando por $categoria: $termino";

    if ($categoria == 'autor') {
        $encontrar_autor = "SELECT rut_persona from persona where nombre = ?";
        $stmt = $pdo->prepare($encontrar_autor);
        $stmt->execute([$termino]);

        $rut_autor = $stmt->fetch(PDO::FETCH_ASSOC);

        $info_art = "SELECT  
                a.titulo, 
                a.resumen, 
                (
                    SELECT GROUP_CONCAT(DISTINCT te.topico SEPARATOR ', ')
                    FROM (
                        SELECT ID_top FROM art_topico WHERE ID_art = a.ID_art
                    ) AS topicos
                    JOIN topico_especialidad te ON te.ID_top = topicos.ID_top
                ) AS topicos
            FROM articulo a
            JOIN art_autor aa ON a.ID_art = aa.ID_art
            WHERE aa.rut_persona = ?";

        $stmt = $pdo->prepare($info_art);
        $stmt->execute([$rut_autor['rut_persona']]);
        $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($articulos) {
            foreach ($articulos as $articulo) {
                echo "<pre>";
                print_r($articulo);
                echo "</pre>";
            }
        } else {
            echo "No se encontraron artículos para ese autor.";
        }
    } elseif ($categoria == 'fecha') {
        echo $categoria;
    } elseif ($categoria == 'topicos') {
        # code...
    } elseif ($categoria == 'revisor') {
        # code...
    } else {
        echo 'File not found';
    }

}
?>