<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/pdo.php';
require_once '../app/auth.php';
require_once '../app/utils.php';


require_login();


$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;


$search = isset($_GET['search']) ? trim($_GET['search']) : '';


if (!empty($search)) {
    
    $count_stmt = $pdo->prepare('SELECT COUNT(*) FROM incidencias WHERE titulo LIKE ? OR descripcion LIKE ?');
    $count_stmt->execute(["%$search%", "%$search%"]);
    $total_records = $count_stmt->fetchColumn();

    
    $stmt = $pdo->prepare('SELECT * FROM incidencias WHERE titulo LIKE ? OR descripcion LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bindValue(1, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    
    $total_records = $pdo->query('SELECT COUNT(*) FROM incidencias')->fetchColumn();
    
    
    $stmt = $pdo->prepare('SELECT * FROM incidencias ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$incidencias = $stmt->fetchAll();
$total_pages = ceil($total_records / $limit); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Incidencias - Helpdesk</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f8f9fa; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-box { margin-bottom: 20px; }
        .search-box input[type="text"] { padding: 8px; width: 300px; }
        .search-box button { padding: 8px 12px; background: #007bff; color: white; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #e9ecef; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-alta { background: #f8d7da; color: #721c24; }
        .badge-media { background: #fff3cd; color: #856404; }
        .badge-baja { background: #d4edda; color: #155724; }
        .pagination { margin-top: 20px; display: flex; gap: 5px; }
        .pagination a { padding: 8px 12px; border: 1px solid #ddd; color: #007bff; text-decoration: none; border-radius: 4px; }
        .pagination a.active { background: #007bff; color: white; border-color: #007bff; }
        .btn-logout { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; }
        .btn-action { text-decoration: none; font-weight: bold; margin-right: 8px; }
    </style>
</head>
<body>

<div class="header">
    <h2>Hola, <?php echo e($_SESSION['user']); ?> Bienvenido al Gestor de Incidencias</h2>
    <div>
        <a href="items_form.php" style="background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; margin-right: 10px;">+ Nueva Incidencia</a>
        <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
    </div>
</div>

<div class="search-box">
    <form action="items_list.php" method="GET">
        <input type="text" name="search" placeholder="Buscar por título o descripción..." value="<?php echo e($search); ?>">
        <button type="submit">Buscar</button>
        <?php if (!empty($search)): ?>
            <a href="items_list.php" style="margin-left: 10px; color: #6c757d;">Limpiar filtro</a>
        <?php endif; ?>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Descripción</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th> </tr>
    </thead>
    <tbody>
        <?php if (count($incidencias) > 0): ?>
            <?php foreach ($incidencias as $incidencia): ?>
                <tr>
                    <td><?php echo e($incidencia['id']); ?></td>
                    <td><strong><?php echo e($incidencia['titulo']); ?></strong></td>
                    <td><?php echo e($incidencia['descripcion']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo strtolower(e($incidencia['prioridad'])); ?>">
                            <?php echo e($incidencia['prioridad']); ?>
                        </span>
                    </td>
                    <td><?php echo e($incidencia['estado']); ?></td>
                    <td><?php echo e($incidencia['created_at']); ?></td>
                    <td>
                        <a href="items_show.php?id=<?php echo e($incidencia['id']); ?>" class="btn-action" style="color: #17a2b8;"> Ver</a>
                        <a href="items_form.php?id=<?php echo e($incidencia['id']); ?>" class="btn-action" style="color: #ffc107;">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center; color: #6c757d;">No se encontraron incidencias.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="items_list.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>

</body>
</html>
