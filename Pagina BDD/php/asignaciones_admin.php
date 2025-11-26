<?php
    include("save_revisor.php");
    include("delete_revisor.php");
    include_once('../DB/db.php');


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

    #Obtener personas del comite

    $Participantes_query = "SELECT 
    a.ID_art, 
    a.titulo, 
    a.fecha, 
    a.calificacion,

    -- Autores
    obtener_autores(a.ID_art) AS autores,

    -- Revisores
    obtener_revisores(a.ID_art) AS revisores,

    -- Tópicos
    obtener_topicos(a.ID_art) AS topicos
    FROM articulo a";

    $stmt = $pdo->prepare($Participantes_query);
    $stmt->execute();
    $resultado_revisores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    #Articulos faltantes
    $articulos_faltantes = [];
    $query = "SELECT articulo.ID_art, articulo.titulo, COUNT(art_revisor.rut_revisor) AS total_revisores
FROM articulo
LEFT JOIN art_revisor ON articulo.ID_art = art_revisor.ID_art
GROUP BY articulo.ID_art, articulo.titulo
HAVING total_revisores <= 2";

    $stmt = $pdo->prepare($query);
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
        <?php include("includes/slidebar_admin.php") ?> 
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include("includes/header.php") ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
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
                                                    <th>Título del Artículo</th>
                                                    <th>Fecha de publicación</th>
                                                    <th>Califición</th>
                                                    <th>Autores</th>
                                                    <th>Revisores</th>
                                                    <th>Topicos</th>
                                                    <th>Asignaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($resultado_revisores as $index => $fila): ?>
                                                <?php
                                                    // Define el criterio para considerar una fila "incompleta"
                                                    $revisores = is_null($fila['revisores']) ? '' : $fila['revisores'];
                                                    $num_revisores = count(array_filter(array_map('trim', explode(',', $revisores))));
                                                    $clase_fila = $num_revisores < 2 ? 'table-warning' : ''; // Cambia 2 por el mínimo deseado
                                                ?>
                                                <tr class="<?= $clase_fila ?>">
                                                    <td><?= is_null($fila['ID_art']) ? 'null' : htmlspecialchars($fila['ID_art']) ?></td>
                                                    <td><?= is_null($fila['titulo']) ? 'null' : htmlspecialchars($fila['titulo']) ?></td>
                                                    <td><?= is_null($fila['fecha']) ? 'null' : htmlspecialchars($fila['fecha']) ?></td>
                                                    <td><?= is_null($fila['calificacion']) ? 'null' : htmlspecialchars($fila['calificacion']) ?></td>
                                                    <td><?= is_null($fila['autores']) ? 'null' : htmlspecialchars($fila['autores']) ?></td>
                                                    <td><?= is_null($fila['revisores']) ? 'null' : htmlspecialchars($fila['revisores']) ?></td>
                                                    <td><?= is_null($fila['topicos']) ? 'null' : htmlspecialchars($fila['topicos']) ?></td>
                                                    <td>
                                                        <a href="asignar_revisores.php?ID_art=<?= $fila['ID_art'] ?>" class="btn btn-primary btn-asignar">
                                                            Asignar
                                                        </a>
                                                    </td>
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
            <!-- End of Main Content -->

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