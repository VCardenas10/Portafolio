<?php
include("save_revisor.php");
include("delete_revisor.php");
include_once('../DB/db.php');


#nombre para la sesion
if (isset($_SESSION['rut_persona'])) {
    $rut = $_SESSION['rut_persona'];

    $sql = "SELECT nombre FROM persona WHERE rut_persona = :rut";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':rut', $rut);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $nombre = $resultado['nombre'];
    }
}


# obtencion de archivos que debe revisar
$titulos_query = "SELECT 
    a.ID_art, 
    a.titulo,
    a.calificacion as Calificacion_total,

    -- Autores
    (
        SELECT GROUP_CONCAT(DISTINCT p1.nombre SEPARATOR ', ')
        FROM (
            SELECT rut_persona FROM art_autor WHERE ID_art = a.ID_art AND contacto = TRUE
        ) AS autores
        JOIN persona p1 ON p1.rut_persona = autores.rut_persona
    ) AS autores,

    -- Calificacion personal
	(
		SELECT ar.Calificacion
		FROM art_revisor ar
		JOIN persona p2 ON ar.rut_revisor = p2.rut_persona
		WHERE ar.ID_art = a.ID_art
		AND ar.rut_revisor = arx.rut_revisor
	) AS Calificacion_personal,

    -- Tópicos
    (
        SELECT GROUP_CONCAT(DISTINCT te.topico SEPARATOR ', ')
        FROM (
            SELECT ID_top FROM art_topico WHERE ID_art = a.ID_art
        ) AS topicos
        JOIN topico_especialidad te ON te.ID_top = topicos.ID_top
    ) AS topicos

    FROM articulo a
    JOIN art_revisor arx ON arx.ID_art = a.ID_art
    WHERE arx.rut_revisor = :rut";

    $stmt = $pdo->prepare($titulos_query);
    $stmt->bindParam(':rut', $rut);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    #Articulos faltantes
    $articulos_faltantes = [];
    $query = "SELECT articulo.ID_art, articulo.titulo, art_revisor.Revisado, art_revisor.Calificacion
    FROM articulo
    LEFT JOIN art_revisor ON articulo.ID_art = art_revisor.ID_art
    WHERE art_revisor.rut_revisor = :rut AND art_revisor.Revisado = 0";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':rut', $rut);
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultado as $fila) {
        $articulos_faltantes[] = $fila['titulo'];
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Tarea 2</title>

    <!-- Custom fonts for this template-->
    <link href="includes/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">


    <!-- Custom styles for this page -->
    <link href="includes/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include("includes/slidebar_revisor.php") ?> 
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include("includes/header.php") ?>
                <!-- End of Topbar -->
    
                <div class="container-fluid">
                        <!-- DataTales Example -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Articulos</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <div id="dataTable_wrapper" class="dataTables_wrapper dt-bootstrap4">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <table class="table table-bordered dataTable" id="dataTable" width="100%" cellspacing="0" role="grid" aria-describedby="dataTable_info" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Título</th>
                                                        <th>Autor</th>
                                                        <th>Tópicos</th>
                                                        <th>Calificación Total</th>
                                                        <th>Calificación Personal</th>
                                                        <th>Calificar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($articulos as $index => $articulo): ?>
                                                        <?php
                                                            $es_faltante = in_array($articulo['titulo'], $articulos_faltantes);
                                                            $clase_fila = $es_faltante ? 'table-warning' : '';
                                                        ?>
                                                        <tr class="<?= $clase_fila ?>">
                                                            <td><?= htmlspecialchars($articulo['ID_art']) ?></td>
                                                            <td><?= htmlspecialchars($articulo['titulo']) ?></td>
                                                            <td><?= htmlspecialchars($articulo['autores']) ?></td>
                                                            <td><?= htmlspecialchars($articulo['topicos']) ?></td>
                                                            <td><?= is_null($articulo['Calificacion_total']) ? 'null' : htmlspecialchars($articulo['Calificacion_total']) ?></td>
                                                            <td><?= htmlspecialchars($articulo['Calificacion_personal']) ?></td>
                                                            <?php if ((int)$articulo['Calificacion_personal'] === 0): ?>
                                                                <td>
                                                                    <a href="calificar.php?ID_art=<?= $articulo['ID_art'] ?>" class="btn btn-primary btn-asignar">
                                                                        Calificar
                                                                    </a>
                                                                </td>
                                                            <?php else: ?>
                                                                <td>
                                                                    <a href="editar_revision.php?ID_art=<?= $articulo['ID_art'] ?>" class="btn btn-primary btn-asignar">
                                                                        Editar
                                                                    </a>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>
                <!-- /.container-fluid -->

            </div>
        <!-- End of Content Wrapper -->

    </div>

    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Tarea 2 &copy; Victor Cardenas; Miguel Rivero</span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->
        

    </script>
    <!-- Bootstrap core JavaScript-->
    <script src="includes/vendor/jquery/jquery.min.js"></script>
    <script src="includes/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="includes/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="includes/vendor/chart.js/Chart.min.js"></script>
    <script src="includes/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="includes/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="assets/js/demo/chart-area-demo.js"></script>
    <script src="assets/js/demo/chart-pie-demo.js"></script>
    <script src="assets/js/demo/datatables-demo.js"></script>



</body>

</html>
