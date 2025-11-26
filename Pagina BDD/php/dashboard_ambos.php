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

    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>


                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $nombre; ?></span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>

    <!-- Botones centrados -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-4 text-center">
                <a href="dashboard_autores.php" class="btn btn-primary btn-lg btn-block">
                    Autor
                </a>
            </div>
            <div class="col-md-4 text-center">
                <a href="dashboard_revisores.php" class="btn btn-success btn-lg btn-block">
                    Revisor
                </a>
            </div>
        </div>
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
