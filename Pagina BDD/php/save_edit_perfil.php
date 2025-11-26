<?php
session_start();
include_once('../DB/db.php'); // Incluye tu conexión a la base de datos

if (isset($_POST['update'])) {
    $rut = $_SESSION['rut_persona']; // Asegúrate de que el RUT esté almacenado en la sesión
    $name = $_POST['name'];
    $email = $_POST['email'];

    try {
        // Actualizar nombre y correo electrónico en la base de datos
        $sql = "UPDATE persona SET nombre = :nombre, email = :email WHERE rut_persona = :rut";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':rut', $rut, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Perfil actualizado exitosamente.";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar el perfil.";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    }
}

// Cambio de contraseña
if (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $rut = $_SESSION['rut_persona'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    try {
        // Obtén la contraseña actual de la base de datos
        $sql = "SELECT password FROM persona WHERE rut_persona = :rut";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':rut', $rut, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            $_SESSION['mensaje'] = "Usuario no encontrado.";
            header("Location: profile.php");
            exit();
        }

        $hashedPassword = $result['password'];

        // Verificar contraseña actual
        if (!password_verify($currentPassword, $hashedPassword)) {
            $_SESSION['mensaje'] = "La contraseña actual no es correcta.";
            header("Location: profile.php");
            exit();
        }

        // Verificar coincidencia de nuevas contraseñas
        if ($newPassword !== $confirmPassword) {
            $_SESSION['mensaje'] = "Las nuevas contraseñas no coinciden.";
            header("Location: profile.php");
            exit();
        }

        // Hash de la nueva contraseña
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $sql = "UPDATE persona SET password = :password WHERE rut_persona = :rut";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':password', $newHashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':rut', $rut, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Contraseña actualizada exitosamente.";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar la contraseña.";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    }
}

// Redirigir de vuelta al perfil
header("Location: profile.php");
exit();
?>
