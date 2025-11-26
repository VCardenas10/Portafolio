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


#obtencion cosas para la asigancion
if (!isset($_GET['ID_art'])) {
    die("ID de artículo no proporcionado.");
}

$id_art = $_GET['ID_art'];

$info_articulo_query = "SELECT 
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
    FROM articulo a
    WHERE a.ID_art = ?";

$stmt = $pdo->prepare($info_articulo_query);
$stmt->execute([$id_art]);
$info_articulo = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$info_articulo) {
    echo "Artículo no encontrado.";
}

$topicos_query = "
    SELECT ID_top 
    FROM art_topico 
    WHERE ID_art = ?
";
$stmt = $pdo->prepare($topicos_query);
$stmt->execute([$id_art]);
$topicos = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($topicos)) {
    die("Este artículo no tiene tópicos asignados.");
}

$placeholders = implode(',', array_fill(0, count($topicos), '?'));

$revisores_query = "
    SELECT DISTINCT p.rut_persona, p.nombre, p.email
    FROM persona p
    JOIN topico_revisor tr ON p.rut_persona = tr.rut_persona
    WHERE tr.ID_top IN ($placeholders)
    AND NOT EXISTS (
        SELECT 1
        FROM art_autor aa
        WHERE aa.ID_art = ? AND aa.rut_persona = p.rut_persona
    )
";

$params = array_merge($topicos, [$id_art]); // Agrega el ID_art como último parámetro
$stmt = $pdo->prepare($revisores_query);
$stmt->execute($params);
$revisores = $stmt->fetchAll(PDO::FETCH_ASSOC);



#consulta por los revisores actuales:

$rev_actuales_query = "Select p.nombre, p.rut_persona from persona p
        join art_revisor ar on ar.rut_revisor = p.rut_persona
        where ar.ID_art = ?";

$stmt = $pdo->prepare($rev_actuales_query);
$stmt->execute([$id_art]);
$rev_actuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sin_revisores_actuales = empty($rev_actuales);

// Si no hay actuales, usar 3 sugerencias
if ($sin_revisores_actuales) {
    $rev_actuales = array_slice($revisores, 0, 3); // Solo 3 sugerencias
}



#Articulos faltantes
$articulos_faltantes = [];
$query = "SELECT articulo.ID_art, articulo.titulo
        FROM articulo
        LEFT JOIN art_revisor ON articulo.ID_art = art_revisor.ID_art
        WHERE art_revisor.ID_art IS NULL";

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

                <div class="container mt-5">
                    <h2>Asignar Revisores al Artículo</h2>
                    <p><strong>Título:</strong> <?= htmlspecialchars($info_articulo['titulo']) ?></p>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($info_articulo['fecha']) ?></p>

                    <form action="save_asignacion.php" method="POST">
                        <input type="hidden" name="id_art" value="<?= htmlspecialchars($id_art) ?>">

                        <div class="form-group">
                            <label><?= $sin_revisores_actuales ? "Revisores recomendados: " : "Revisores actuales:" ?></label>
                            <?php foreach ($rev_actuales as $index => $revisor): ?>
                                <div class="revisor-item" style="margin-bottom: 10px;">
                                    <button type="button" class="btn btn-outline-primary" onclick="toggleRevisores('revisor_select_<?= $index ?>')">
                                        <?= htmlspecialchars($revisor['nombre']) ?>
                                    </button>

                                    <!-- Siempre enviar como actual, incluso si es recomendado -->
                                    <input type="hidden" name="ruts_actuales[]" value="<?= htmlspecialchars($revisor['rut_persona']) ?>">

                                    <div id="revisor_select_<?= $index ?>" style="display:none; margin-top:5px;">
                                        <select name="nuevos_revisores[]" class="form-control">
                                            <option value="">-- Selecciona un revisor --</option>
                                            <?php foreach ($revisores as $rev): ?>
                                                <option value="<?= $rev['rut_persona'] ?>"><?= htmlspecialchars($rev['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-success">Guardar Asignaciones</button>
                        <a href="asignaciones_admin.php" class="btn btn-secondary">Volver</a>
                    </form>


                </div>
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



<script>
function toggleRevisores(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

window.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const selects = document.querySelectorAll('select[name="nuevos_revisores[]"]');
        const seleccionados = [];

        for (let select of selects) {
            const val = select.value.trim();
            if (val !== "") {
                if (seleccionados.includes(val)) {
                    alert("No puedes asignar el mismo revisor más de una vez.");
                    e.preventDefault(); // Detiene el envío
                    return;
                }
                seleccionados.push(val);
            }
        }
    });
});
</script>
        

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
