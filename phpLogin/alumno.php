<?php
include("conexion.php");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Verifica conexión
if (!$conexion) {
    die("<script>alert('No se pudo conectar a la base de datos.');</script>");
}

$iduser = $_SESSION['id_usuario'];

// Obtener nombre del alumno
$sql = "SELECT NombreC FROM usuarios WHERE idusuarios = '$iduser'";
$resultado = $conexion->query($sql);
if (!$resultado) {
    die("Error al consultar el nombre: " . $conexion->error);
}
$row = $resultado->fetch_assoc();
$nombre = $row['NombreC'];

// Obtener asistencias y participaciones
$sqlAsistencias = "SELECT fecha, asistencia, participacion FROM asistencias WHERE id_usuario = '$iduser' ORDER BY fecha DESC";
$resultadoAsistencias = $conexion->query($sqlAsistencias);
if (!$resultadoAsistencias) {
    die("Error al consultar asistencias: " . $conexion->error);
}

// Contar faltas
$sqlFaltas = "SELECT COUNT(*) AS faltas FROM asistencias WHERE id_usuario = '$iduser' AND asistencia = 'Falta'";
$resFaltas = $conexion->query($sqlFaltas);
$cantidadFaltas = 0;
if ($resFaltas) {
    $filaFaltas = $resFaltas->fetch_assoc();
    $cantidadFaltas = (int)$filaFaltas['faltas'];
}

// Contar retardos
$sqlRetardos = "SELECT COUNT(*) AS retardos FROM asistencias WHERE id_usuario = '$iduser' AND asistencia = 'Retardo'";
$resRetardos = $conexion->query($sqlRetardos);
$cantidadRetardos = 0;
if ($resRetardos) {
    $filaRetardos = $resRetardos->fetch_assoc();
    $cantidadRetardos = (int)$filaRetardos['retardos'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistencias y Participaciones</title>
    <link rel="stylesheet" href="assets/css/alumno.css"/>
    <link rel="icon" href="assets/img/alumno.png" type="alumno.png">
</head>
<body>
    <div class="container mt-5">
        <div class="bienvenida-alumno">
    <div class="contenedor-horario">
        <img src="assets/img/Horario.jpeg" alt="Horario" class="img-horario">
    </div>
    <h3>Bienvenido, <?php echo htmlspecialchars($nombre); ?></h3>
        <div id="timer-box">
    <div id="timer-title">⏳ Tiempo restante:</div>
    <div id="timer">00:00</div>
    </div>
</div>
        <h4>Historial de Asistencias y Participaciones</h4>
        <table class="table table-bordered table-striped mt-3">
            <thead class="thead-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Asistencia</th>
                    <th>Participación</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultadoAsistencias->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($fila['asistencia']); ?></td>
                        <td><?php echo htmlspecialchars($fila['participacion']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="salir.php" class="btn btn-danger">Cerrar sesión</a>
    </div>

    <!-- ALERTAS DE RETARDOS/FALTAS -->
    <script>
    window.onload = function() {
        let faltas = <?php echo $cantidadFaltas; ?>;
        let retardos = <?php echo $cantidadRetardos; ?>;

        if (retardos === 2) {
            alert("⚠️ Un retardo más y se convierte en una falta.");
        }

        let totalFaltas = faltas + Math.floor(retardos / 3);

        if (totalFaltas === 2) {
            alert("⚠️ Solo puedes faltar una vez más.");
        } else if (totalFaltas >= 3) {
            alert("❌ Ya no puedes volver a faltar.");
        }
    };
    </script>

    <!-- NOTIFICACIONES + TEMPORIZADOR -->
    <script>
    const horarios = {
        1: "12:00", // Lunes
        2: "07:00", // Martes
        3: "10:00", // Miércoles
        4: "07:00", // Jueves
        5: "07:00"  // Viernes
    };

    function obtenerHoraDeHoy() {
        const dia = new Date().getDay();
        return horarios[dia] || null;
    }

    function programarNotificaciones() {
        const horaClase = obtenerHoraDeHoy();
        if (!horaClase) return;

        const [hora, minuto] = horaClase.split(":").map(Number);
        const ahora = new Date();

        const inicioClase = new Date();
        inicioClase.setHours(hora, minuto, 0, 0);

        const tiempoAsistencia = new Date(inicioClase.getTime() + 5 * 60000);
        const tiempoRetardo = new Date(inicioClase.getTime() + 10 * 60000);

        const msAsistencia = tiempoAsistencia - ahora;
        const msRetardo = tiempoRetardo - ahora;

        if (Notification.permission === "granted") {
            if (msAsistencia > 0) {
                setTimeout(() => {
                    new Notification("⏰ Asistencia", {
                        body: "Tienes 5 minutos para llegar con asistencia ⏳",
                        icon: "assets/img/reloj.png"
                    });
                }, msAsistencia);
            }

            if (msRetardo > 0) {
                setTimeout(() => {
                    new Notification("⚠️ Retardo", {
                        body: "Tienes 5 minutos para llegar con retardo 🕒",
                        icon: "assets/img/reloj.png"
                    });
                }, msRetardo);
            }
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    programarNotificaciones();
                }
            });
        }
    }

    function startCountdown(durationSeconds, displayElementId) {
        let timer = durationSeconds;
        const display = document.getElementById(displayElementId);

        function updateTimer() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            display.textContent = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');

            if (timer > 0) {
                timer--;
                setTimeout(updateTimer, 1000);
            } else {
                display.textContent = "¡00:00!";
                alert("⏰ El tiempo ha terminado. Se registrará un retardo o falta.");
            }
        }

        updateTimer();
    }

    function iniciarTemporizadorSiEsClase() {
        const hoy = new Date();
        const dia = hoy.getDay(); // 0 = domingo, 1 = lunes...

        if (!(dia in horarios)) return;

        const [horaClase, minutoClase] = horarios[dia].split(":").map(Number);
        const inicioClase = new Date();
        inicioClase.setHours(horaClase, minutoClase, 0, 0);

        const ahora = new Date();
        const tiempoTranscurrido = Math.floor((ahora - inicioClase) / 1000); // en segundos

        const tiempoTotal = 900; // 15 minutos (5 asistencia + 10 retardo)

        if (tiempoTranscurrido >= 0 && tiempoTranscurrido < tiempoTotal) {
            const tiempoRestante = tiempoTotal - tiempoTranscurrido;
            startCountdown(tiempoRestante, "timer");
            document.getElementById("timer-box").style.display = "block";
        } else {
            document.getElementById("timer-box").style.display = "none";
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        programarNotificaciones();
        iniciarTemporizadorSiEsClase();
    });
</script>
</body>
</html>
