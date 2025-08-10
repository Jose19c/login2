<?php
include("conexion.php");
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['id_usuario']) && isset($_SESSION['tipo'])) {
    if ($_SESSION['tipo'] === 'maestro') {
        header("Location: maestro.php");
    } else {
        header("Location: alumno.php");
    }
    exit;
}

// LOGIN
if (isset($_POST["ingresar"])) {
    $usuario = mysqli_real_escape_string($conexion, $_POST['user']);
    $password = $_POST['pass'];

    $sql = "SELECT idusuarios, password, tipo FROM usuarios WHERE usuario = '$usuario'";
    $resultado = $conexion->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['id_usuario'] = $row['idusuarios'];
            $_SESSION['tipo'] = $row['tipo'];

            if ($row['tipo'] === 'maestro') {
                header("Location: maestro.php");
            } else {
                header("Location: alumno.php");
            }
            exit;
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location = 'index copy.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.location = 'index copy.php';</script>";
    }
}

// REGISTRO
if (isset($_POST["registrar"])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['user']);
    $password = $_POST['pass'];
    $passwordr = $_POST['passr'];
    $tipo = mysqli_real_escape_string($conexion, $_POST['Tipo']);

    if ($password !== $passwordr) {
        echo "<script>alert('Las contraseñas no coinciden'); window.location = 'index copy.php';</script>";
        exit;
    }

    $password_encriptada = password_hash($password, PASSWORD_DEFAULT);

    $sqluser = "SELECT idusuarios FROM usuarios WHERE usuario = '$usuario'";
    $resultadouser = $conexion->query($sqluser);

    if ($resultadouser && $resultadouser->num_rows > 0) {
        echo "<script>alert('El usuario ya existe'); window.location = 'index copy.php';</script>";
    } else {
        $sqlusuario = "INSERT INTO usuarios (NombreC, Correo, usuario, password, tipo)
                       VALUES ('$nombre','$correo','$usuario','$password_encriptada','$tipo')";
        $resultadousuario = $conexion->query($sqlusuario);

        if ($resultadousuario) {
            echo "<script>alert('Registro exitoso'); window.location = 'index copy.php';</script>";
        } else {
            echo "<script>alert('Error al registrarse: " . $conexion->error . "'); window.location = 'index copy.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Login - Sistema de Registro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="assets/css/estilos.css"/>
    <link rel="icon" href="assets/img/logo.png" type="logo.png">
</head>

<body class="login-layout" class="img-bg" style="background: url('assets/img/login.jpeg') no-repeat center center fixed; background-size: cover;">
    <div class="main-container">
        <div class="main-content">
            <div class="login-container">
                <div class="center">
                    <h1>
                        <span class="red">Control de </span>
                        <span class="black" id="id-text2">Asistencia</span>
                    </h1>
                    <h4 class="blue" id="id-company-text">&copy; System Access Control</h4>
                </div>

                <div class="space-6"></div>

                <div class="position-relative">
                    <!-- Login Box -->
                    <div id="login-box" class="login-box visible widget-box no-border">
                        <div class="widget-body">
                            <div class="widget-main">
                                <h4 class="header blue lighter bigger">Ingresa tu Información</h4>
                                <div class="space-6"></div>
                                <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <fieldset>
                                        <label class="block clearfix">
                                            <span class="block input-icon input-icon-right">
                                                <input type="text" class="form-control" name="user" placeholder="Usuario" required />
                                            </span>
                                        </label>
                                        <label class="block clearfix">
                                            <span class="block input-icon input-icon-right" style="position: relative;">
                                                <input type="password" id="passwordLogin" name="pass" class="form-control" placeholder="password" required />
                                                <img id="toggleLogin" src="assets/img/c-.png" alt="Mostrar contraseña" 
                                                    style="position: absolute; right: 5px; top: 59%; transform: translateY(-50%); cursor: pointer; width: 24px;" />
                                            </span>
                                        </label>

                                        <div class="clearfix">
                                            <label class="inline">
                                                <input type="checkbox" class="ace" />
                                                <span class="lbl"> Recuérdame</span>
                                            </label>
                                            <button type="submit" name="ingresar" class="pull-right btn btn-sm btn-primary">Ingresar</button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <div class="toolbar clearfix">
                                <a href="#" data-target="#signup-box" class="user-signup-link">Nuevo Registro</a>
                            </div>
                        </div>
                    </div>

                    <!-- Sign-Up Box -->
                    <div id="signup-box" class="signup-box widget-box no-border">
                        <div class="widget-body">
                            <div class="widget-main">
                                <h4 class="header blue lighter bigger">Registro de Nuevos Usuarios</h4>
                                <p>Ingresa los datos solicitados a continuación:</p>

                                <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                                    <fieldset>
                                        <label class="block clearfix">
                                            <input type="text" class="form-control" name="nombre" placeholder="Nombre Completo" required />
                                        </label>
                                        <label class="block clearfix">
                                            <input type="email" class="form-control" name="correo" placeholder="Email" required />
                                        </label>
                                        <label class="block clearfix">
                                            <input type="text" class="form-control" name="user" placeholder="Usuario" required />
                                        </label>
                                        <label class="block clearfix">
                                            <span class="block input-icon input-icon-right" style="position: relative;">
                                                <input type="password" id="passwordRegistro" name="pass" class="form-control" placeholder="password" required />
                                                <img id="toggleRegistro" src="assets/img/c-.png" alt="Mostrar contraseña" 
                                                    style="position: absolute; right: -460px; top: 19%; transform: translateY(-50%); cursor: pointer; width: 24px;" />
                                            </span>
                                        </label>

                                        <label class="block clearfix">
                                            <input type="password" id="passwordRepetir" name="passr" class="form-control" placeholder="Repetir password" required />
                                        </label>

                                        <label for="TIPO">Tipo de cuenta:</label>
                                        <select name="Tipo" id="TIPOcuenta">
                                            <option value="maestro">Maestro</option>
                                            <option value="alumno">Estudiante</option>
                                        </select>

                                        <div class="clearfix">
                                            <button type="reset" class="pull-left btn btn-sm">Reset</button>
                                            <button type="submit" name="registrar" class="pull-right btn btn-sm btn-primary">Registrar</button>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <div class="toolbar center">
                                <a href="#" data-target="#login-box" class="back-to-login-link">Regresar al Login</a>
                            </div>
                        </div>
                    </div>
                </div> <!-- /.position-relative -->
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-2.1.4.min.js"></script>
    <script>
        jQuery(function ($) {
            $(document).on('click', '.toolbar a[data-target]', function (e) {
                e.preventDefault();
                const target = $(this).data('target');
                $('.widget-box.visible').removeClass('visible');
                $(target).addClass('visible');
            });
        });

        document.getElementById("toggleLogin").addEventListener("click", function () {
            const passwordInput = document.getElementById("passwordLogin");
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            this.src = type === "password" ? "assets/img/c-.png" : "assets/img/c+.png";
        });

        document.getElementById("toggleRegistro").addEventListener("click", function () {
            const pass1 = document.getElementById("passwordRegistro");
            const pass2 = document.getElementById("passwordRepetir");

            const type = pass1.getAttribute("type") === "password" ? "text" : "password";

            pass1.setAttribute("type", type);
            pass2.setAttribute("type", type);

            this.src = type === "password" ? "assets/img/c-.png" : "assets/img/c+.png";
        });
    </script>
</body>
</html>