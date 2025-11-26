<?php
    session_start();
    include_once('../DB/db.php');

    if (isset($_SESSION['rut_persona'])) {
        $rut = $_SESSION['rut_persona'];
        $rol = $_SESSION['rol'] ?? null;
    
        $sql = "SELECT nombre FROM persona WHERE rut_persona = :rut";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':rut', $rut);
        $stmt->execute();
    
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($resultado) {
            $nombre = $resultado['nombre'];
        }

    }

    #Consulta de autores
    $cant_autores_query = "SELECT count(*) as total FROM rol_personas WHERE ROL_ID = '1' OR ROL_ID = '3'";

    $stmt = $pdo->prepare($cant_autores_query);
    $stmt->execute();

    $resultado_autores = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_autores = $resultado_autores['total'];


    #Consulta de revisores
    $cant_revisores_query = "SELECT count(*) as total FROM rol_personas WHERE ROL_ID = '2' OR ROL_ID = '3'";

    $stmt = $pdo->prepare($cant_revisores_query);
    $stmt->execute();

    $resultado_revisores = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_revisores = $resultado_revisores['total'];

    
    #Consulta de articulos revisados
    $cant_articulos_rev_query = "SELECT 
            ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM articulo)),2) AS porcentaje_completamente_revisados
        FROM (
            SELECT articulo.ID_art
            FROM art_revisor
            JOIN articulo ON articulo.ID_art = art_revisor.ID_art
            WHERE art_revisor.revisado = TRUE
            GROUP BY articulo.ID_art
            HAVING COUNT(art_revisor.rut_revisor) = 3
        ) AS subconsulta";

    $stmt = $pdo->prepare($cant_articulos_rev_query);
    $stmt->execute();


    $resultado_rev = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_art_rev = round($resultado_rev['porcentaje_completamente_revisados']);


    #Consulta de articulos sin revisar
    $cant_art_sr_query = "SELECT 
            (SELECT COUNT(*) FROM articulo) - COUNT(*) AS restantes
        FROM (
            SELECT articulo.ID_art
            FROM art_revisor
            JOIN articulo ON articulo.ID_art = art_revisor.ID_art
            WHERE art_revisor.revisado = TRUE
            GROUP BY articulo.ID_art
            HAVING COUNT(art_revisor.rut_revisor) = 3
        ) AS subconsulta";
    
    $stmt = $pdo->prepare($cant_art_sr_query);
    $stmt->execute();

    $resultado_sinrev = $stmt->fetch(PDO::FETCH_ASSOC);
    $cant_art_sinrev = $resultado_sinrev['restantes'];

    #Recibir los titulos
    $titulos_query = "SELECT 
    a.ID_art,
    a.titulo,
    a.fecha,
    obtener_autores(a.ID_art) AS autores,
    obtener_revisores(a.ID_art) AS revisores,
    obtener_topicos(a.ID_art) AS topicos
    FROM articulo a";

    
    $stmt = $pdo->prepare($titulos_query);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    <!-- Content Row -->
                    <div class="row">

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Autores</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $cant_autores; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Revisores</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $cant_revisores; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Articulos Revisados
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo "$cant_art_rev%"; ?></div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: <?php echo $cant_art_rev; ?>%" aria-valuenow="50" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pendientes de revision</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $cant_art_sinrev; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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
                                                    <th>Fecha de publicación</th>
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
                                                        <td><?= htmlspecialchars($articulo['fecha']) ?></td>
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

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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