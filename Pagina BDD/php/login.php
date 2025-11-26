<?php
session_start();
include_once('../DB/db.php');

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $correo = $_POST['correo'];
    $rut = $_POST['rut'];
    $password = $_POST['contraseña'];

    $sql = "SELECT rut_persona, nombre, password FROM persona WHERE email = :correo AND rut_persona = :rut";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':rut', $rut);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    var_dump($correo, $rut, $password);
    var_dump($usuario);


    if ($usuario && password_verify($password, $usuario['password'])) {

        var_dump(password_verify($password, $usuario['password']));

        // Iniciar sesión
        $_SESSION['rut_persona'] = $usuario['rut_persona'];
        $_SESSION['nombre'] = $usuario['nombre'];
    
        // Nueva consulta para obtener el rol
        $query = "SELECT Rol_ID FROM rol_personas WHERE rut_persona = :rut";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':rut', $usuario['rut_persona']);
        $stmt->execute();
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($rol) {
            $_SESSION['rol'] = (int)$rol['Rol_ID'];


            switch ((int)$rol['Rol_ID']) {
                case 0:
                    header("Location: dashboard_admin.php");
                    break;
                case 1:
                    header("Location: dashboard_autores.php");
                    break;
                case 2:
                    header("Location: dashboard_revisores.php");
                    break;
                case 3:
                    header("Location: dashboard_ambos.php");
                    break;
                default:
                    header("Location: dashboard_ambos.php");
            }
        } else {
            echo "<p style='color:red;'>No se encontró rol asignado.</p>";
        }
    
        exit();
    } else {
        echo "<p style='color:red;'>Usuario o contraseña incorrecta.</p>";
    }
}
?>
