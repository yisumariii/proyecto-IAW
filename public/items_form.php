<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/pdo.php';
require_once '../app/auth.php';
require_once '../app/csrf.php';
require_once '../app/utils.php';


require_login();

$errors = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $id !== null;

$titulo = '';
$descripcion = '';
$prioridad = 'Media';
$estado = 'Abierta';


if ($is_edit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM incidencias WHERE id = ?');
    $stmt->execute([$id]);
    $incidencia = $stmt->fetch();
    
    if (!$incidencia) {
        die("Error: La incidencia no existe.");
    }
    
    $titulo = $incidencia['titulo'];
    $descripcion = $incidencia['descripcion'];
    $prioridad = $incidencia['prioridad'];
    $estado = $incidencia['estado'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    check_csrf($_POST['csrf_token']);

    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 'Abierta';

    
    if (empty($titulo)) {
        $errors['titulo'] = "El título de la incidencia es obligatorio.";
    }
    if (empty($descripcion)) {
        $errors['descripcion'] = "La descripción detallada es obligatoria.";
    }

    
    if (empty($errors)) {
        if ($is_edit) {
            
            $stmt = $pdo->prepare('UPDATE incidencias SET titulo = ?, descripcion = ?, prioridad = ?, estado = ? WHERE id = ?');
            $stmt->execute([$titulo, $descripcion, $prioridad, $estado, $id]);
        } else {
            
            $stmt = $pdo->prepare('INSERT INTO incidencias (titulo, descripcion, prioridad, estado) VALUES (?, ?, ?, ?)');
            $stmt->execute([$titulo, $descripcion, $prioridad, $estado]);
        }

        
        header("Location: items_list.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'Editar' : 'Nueva'; ?> Incidencia</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background: #f8f9fa; color: #333; }
        .form-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group textarea, .form-group select { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .error-msg { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .btn-submit { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-cancel { background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-size: 16px; margin-left: 10px; }
    </style>
</head>
<body>

<div class="form-box">
    <h2><?php echo $is_edit ? 'Editar Incidencia #' . e($id) : 'Crear Nueva Incidencia'; ?></h2>
    
    <form action="items_form.php<?php echo $is_edit ? '?id=' . e($id) : ''; ?>" method="POST">
        
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" id="titulo" value="<?php echo $_SERVER['REQUEST_METHOD'] === 'POST' ? old('titulo') : e($titulo); ?>">
            <?php if (isset($errors['titulo'])): ?>
                <div class="error-msg"><?php echo $errors['titulo']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="5"><?php echo $_SERVER['REQUEST_METHOD'] === 'POST' ? old('descripcion') : e($descripcion); ?></textarea>
            <?php if (isset($errors['descripcion'])): ?>
                <div class="error-msg"><?php echo $errors['descripcion']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="prioridad">Prioridad:</label>
            <select name="prioridad" id="prioridad">
                <option value="Baja" <?php echo $prioridad === 'Baja' ? 'selected' : ''; ?>>Baja</option>
                <option value="Media" <?php echo $prioridad === 'Media' ? 'selected' : ''; ?>>Media</option>
                <option value="Alta" <?php echo $prioridad === 'Alta' ? 'selected' : ''; ?>>Alta</option>
            </select>
        </div>

        <?php if ($is_edit): ?>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado">
                <option value="Abierta" <?php echo $estado === 'Abierta' ? 'selected' : ''; ?>>Abierta</option>
                <option value="Cerrada" <?php echo $estado === 'Cerrada' ? 'selected' : ''; ?>>Cerrada</option>
            </select>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn-submit">Guardar Cambios</button>
        <a href="items_list.php" class="btn-cancel">Cancelar</a>
    </form>
</div>

</body>
</html>
