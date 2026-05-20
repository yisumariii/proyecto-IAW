<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/pdo.php';
require_once '../app/auth.php';
require_once '../app/utils.php';


require_login();


$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    die("Error: ID de incidencia no válido.");
}


$stmt = $pdo->prepare('SELECT * FROM incidencias WHERE id = ?');
$stmt->execute([$id]);
$incidencia = $stmt->fetch();


if (!$incidencia) {
    die("Error: La incidencia solicitada no existe en el sistema.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Incidencia #<?php echo e($incidencia['id']); ?></title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f8f9fa; color: #333; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        .meta-info { display: flex; gap: 15px; margin-bottom: 20px; font-size: 14px; color: #6c757d; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
        .badge-alta { background: #dc3545; }
        .badge-media { background: #ffc107; color: #212529; }
        .badge-baja { background: #28a745; }
        .badge-estado { background: #17a2b8; }
        .description-box { background: #f1f3f5; padding: 15px; border-radius: 4px; line-height: 1.6; margin-bottom: 25px; white-space: pre-wrap; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .btn-back { background: #6c757d; color: white; }
        .btn-edit { background: #ffc107; color: #212529; }
    </style>
</head>
<body>

<div class="card">
    <h2><?php echo e($incidencia['titulo']); ?></h2>
    
    <div class="meta-info">
        <span><strong>ID:</strong> #<?php echo e($incidencia['id']); ?></span>
        <span><strong>Prioridad:</strong> 
            <span class="badge badge-<?php echo strtolower(e($incidencia['prioridad'])); ?>">
                <?php echo e($incidencia['prioridad']); ?>
            </span>
        </span>
        <span><strong>Estado:</strong> 
            <span class="badge badge-estado"><?php echo e($incidencia['estado']); ?></span>
        </span>
    </div>

    <h4>Descripción detallada:</h4>
    <div class="description-box"><?php echo e($incidencia['descripcion']); ?></div>

    <p style="font-size: 12px; color: #adb5bd;">Registrada en el sistema el: <?php echo e($incidencia['created_at']); ?></p>

    <div class="actions">
        <a href="items_list.php" class="btn btn-back">Volver al listado</a>
        <a href="items_form.php?id=<?php echo e($incidencia['id']); ?>" class="btn btn-edit">Editar Datos</a>
    </div>
</div>

</body>
</html>
