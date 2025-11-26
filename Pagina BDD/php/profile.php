<?php
include("save_revisor.php");
include("delete_revisor.php");
include_once('../DB/db.php');

#nombre para la sesion
if (isset($_SESSION['rut_persona'])) {
    $rut = $_SESSION['rut_persona'];
    $rol = $_SESSION['rol'] ?? null;


    $sql = "SELECT nombre, email FROM persona WHERE rut_persona = :rut";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':rut', $rut);
    $stmt->execute();

    $queryrol = "SELECT Rol_ID as rol from rol_personas WHERE rut_persona = :rut";
    $stmt1 = $pdo->prepare($queryrol);
    $stmt1->bindParam(':rut', $rut);
    $stmt1->execute();
    $resultado_rol = $stmt1->fetch(PDO::FETCH_ASSOC);
    $rol_persona = $resultado_rol['rol'];
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $nombre = $resultado['nombre'];
        $email = $resultado['email'];
    }
}
    if (isset($_SESSION['mensaje'])): ?>
        <div id="mensaje" class="alert alert-success" role="alert" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
        </div>
        <?php unset($_SESSION['mensaje']); // Eliminar el mensaje después de mostrarlo ?>
    <?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/profile.css">

    <title>Edit Profile</title>
</head>
<body>
    <div class="container">
        <!-- Imagen del perfil -->
        <div class="profile__image"></div>

        <!-- Formulario del perfil -->
        <div class="profile__form-container">
            <form action="save_edit_perfil.php" method="POST" class="profile__form">
                <div>
                    <h1 class="profile__title"><span>Perfil</span></h1>
                    <p class="profile__description">Puedes actualizar tu información.</p>
                </div>
                <div class="profile__inputs">
                    <div>
                        <label for="rut" class="profile__label">Rut</label>
                        <input type="int" class="profile__input" id="rut" name="rut" value="<?php echo $rut ?>" readonly>
                    </div>
                    <div>
                        <label for="name" class="profile__label">Nombre</label>
                        <input type="text" class="profile__input" id="name" name="name" value="<?php echo $nombre ?>" required>
                    </div>
                    <div>
                        <label for="email" class="profile__label">Email</label>
                        <input type="email" class="profile__input" id="email" name="email" value="<?php echo $email ?>"required>
                    </div>
                    <p class="change-password text-primary" data-toggle="modal" data-target="#passwordModal">Cambia tu contraseña</p>
                </div>
                <div class="profile__buttons">
                    <button class="profile__button" type="submit" name="update">Guardar Cambios</button>
                    <button class="profile__button profile__button-ghost" type="button" onclick="redirectBasedOnRole()">Volver</button>
                    
                </div>
            </form>
            <form method="POST" action="delete_profile.php">
                <button class="btn btn-danger" type="submit">
                    Eliminar Perfil
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Cambiar Contraseña</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="save_edit_perfil.php" method="POST" id="changePasswordForm">
                        <div class="form-group">
                            <label for="current-password">Contraseña Actual</label>
                            <input type="password" class="form-control" id="current-password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new-password">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new-password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Guardar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="includes/vendor/jquery/jquery.min.js"></script>
    <script src="includes/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mensaje = document.getElementById('mensaje');
            if (mensaje) {
                setTimeout(() => {
                    mensaje.style.transition = "opacity 0.5s";
                    mensaje.style.opacity = "0";
                    setTimeout(() => mensaje.remove(), 500);
                }, 3000); // Ocultar después de 3 segundos
            }
        });
    </script>
    <script>
    function redirectBasedOnRole() {
        // El valor de PHP se pasa al script
        const rolPersona = <?php echo json_encode($rol_persona); ?>;

        // Lógica de redirección según el valor
        if (rolPersona === 0) {
            window.location.href = 'dashboard_admin.php';
        } else if (rolPersona === 1) {
            window.location.href = 'dashboard_autores.php';
        } else if (rolPersona === 2) {
            window.location.href = 'dashboard_revisores.php';
        } else {
            window.location.href = 'dashboard_ambos.php';
        }
    }
    </script>
</body>
</html>
