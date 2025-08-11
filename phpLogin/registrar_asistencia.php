<?php
include("conexion.php");
session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id_alumno = $_POST['id_alumno'];
$tipo = $_POST['tipo'];
$fecha = date("Y-m-d");

$asistencia = null;

// Verifica si ya existe registro del día para ese alumno
$sqlVerifica = "SELECT * FROM asistencias WHERE id_usuario = ? AND fecha = ?";
$stmtVerifica = $conexion->prepare($sqlVerifica);
$stmtVerifica->bind_param("is", $id_alumno, $fecha);
$stmtVerifica->execute();
$resultado = $stmtVerifica->get_result();

if ($resultado->num_rows > 0) {
    // Ya existe: actualizar
    $row = $resultado->fetch_assoc();

    $nuevaAsistencia = ($tipo === 'asistencia') ? 'Asistencia' :
                       (($tipo === 'retardo') ? 'Retardo' :
                       (($tipo === 'falta') ? 'Falta' : $row['asistencia']));

    $nuevaParticipacion = ($tipo === 'participacion') ? $row['participacion'] + 1 : $row['participacion'];

    $sqlActualizar = "UPDATE asistencias SET asistencia = ?, participacion = ? WHERE id_usuario = ? AND fecha = ?";
    $stmtActualizar = $conexion->prepare($sqlActualizar);
    $stmtActualizar->bind_param("siis", $nuevaAsistencia, $nuevaParticipacion, $id_alumno, $fecha);
    $stmtActualizar->execute();

} else {
    // No existe: insertar
    $valorAsistencia = ($tipo === 'asistencia') ? 'Asistencia' :
                       (($tipo === 'retardo') ? 'Retardo' :
                       (($tipo === 'falta') ? 'Falta' : 'Sin registro'));

    $valorParticipacion = ($tipo === 'participacion') ? 1 : 0;

    $sqlInsertar = "INSERT INTO asistencias (id_usuario, fecha, asistencia, participacion) VALUES (?, ?, ?, ?)";
    $stmtInsertar = $conexion->prepare($sqlInsertar);
    $stmtInsertar->bind_param("issi", $id_alumno, $fecha, $valorAsistencia, $valorParticipacion);
    $stmtInsertar->execute();
}

header("Location: maestro.php");
exit;
?>