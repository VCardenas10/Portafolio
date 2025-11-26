<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Formulario de Registro</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .gradient-custom {
      background: linear-gradient(to right, #6a11cb, #2575fc);
    }
  </style>
</head>
<body>

<?php
ob_start();
include_once('../DB/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['contraseña'] !== $_POST['confirmar_contraseña']) {
        echo "<div class='alert alert-danger'>Las contraseñas no coinciden.</div>";
    } else {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $rut_persona = $_POST['rut'];
        $password = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);

        if ($_POST['rol'] == "Autor") {
            $rol = 1;
        } elseif ($_POST['rol'] == "Revisor") {
            $rol = 2;
        } else {
            $rol = 3;
        }

        $verificar_sql = "SELECT COUNT(*) FROM PERSONA WHERE rut_persona = :rut";
        $verificar_stmt = $pdo->prepare($verificar_sql);
        $verificar_stmt->bindParam(':rut', $rut_persona);
        $verificar_stmt->execute();
        $existe = $verificar_stmt->fetchColumn();

        if ($existe > 0) {
            echo "<div class='alert alert-warning'>Ya existe una persona con ese RUT.</div>";
        } else {
            $sql_personas = "INSERT INTO PERSONA (rut_persona, nombre, email, password) 
                             VALUES (:rut, :nombre, :correo, :password)";
            $stmt1 = $pdo->prepare($sql_personas);
            $stmt1->bindParam(':rut', $rut_persona);
            $stmt1->bindParam(':nombre', $nombre);
            $stmt1->bindParam(':correo', $correo);
            $stmt1->bindParam(':password', $password);

            $sql_rol = "INSERT INTO rol_personas (rut_persona, Rol_ID) 
                        VALUES (:rut, :rol)";
            $stmt2 = $pdo->prepare($sql_rol);
            $stmt2->bindParam(':rut', $rut_persona);
            $stmt2->bindParam(':rol', $rol);

            if ($stmt1->execute() && $stmt2->execute()) {
                header("Location: index.php");
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error al registrar la persona.</div>";
            }
        }
    }
}
?>

<section class="vh-100 gradient-custom">
  <div class="container py-5 h-100">
    <div class="row justify-content-center align-items-center h-100">
      <div class="col-12 col-lg-9 col-xl-7">
        <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
          <div class="card-body p-4 p-md-5">
            <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Formulario de Registro</h3>
            <form action="signup.php" method="POST">
              <div class="row">
                <div class="col-md-6 mb-4">
                  <div class="form-outline">
                    <input type="text" name="nombre" id="firstName" class="form-control form-control-lg" required />
                    <label class="form-label" for="firstName">Nombre</label>
                  </div>
                </div>
                <div class="col-md-6 mb-4">
                  <div class="form-outline">
                    <input type="text" name="rut" id="lastName" class="form-control form-control-lg" required />
                    <label class="form-label" for="lastName">RUT</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-4">
                  <div class="form-outline">
                    <input type="email" name="correo" id="emailAddress" class="form-control form-control-lg" required />
                    <label class="form-label" for="emailAddress">Correo</label>
                  </div>
                </div>
                <div class="col-md-6 mb-4">
                  <div class="form-outline">
                    <input type="password" name="contraseña" id="password" class="form-control form-control-lg" required />
                    <label class="form-label" for="password">Contraseña</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-4">
                  <div class="form-outline">
                    <input type="password" name="confirmar_contraseña" id="confirmPassword" class="form-control form-control-lg" required />
                    <label class="form-label" for="confirmPassword">Confirmar Contraseña</label>
                  </div>
                </div>
                <div class="col-md-6 mb-4">
                  <div class="form-outline">
                    <select name="rol" class="form-select form-control-lg" required>
                      <option value="">--Selecciona un rol--</option>
                      <option value="Autor">Autor</option>
                      <option value="Revisor">Revisor</option>
                      <option value="Autor y Revisor">Autor y Revisor</option>
                    </select>
                    <label class="form-label">Rol</label>
                  </div>
                </div>
              </div>
              <div class="mt-4 pt-2 d-flex justify-content-between">
                <a href="index.php" class="btn btn-outline-secondary btn-lg">Volver</a>
                <input class="btn btn-primary btn-lg" type="submit" value="Registrar" />
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>