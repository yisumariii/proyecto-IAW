<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../app/pdo.php';
require_once '../app/auth.php';


require_login();

echo "<h2>Ejecutando Prueba de Transacción y Rollback (PDO)</h2>";
echo "<p>Esta prueba simulará un fallo en mitad de un proceso para demostrar cómo el sistema revierte los cambios (Rollback).</p>";
echo "<hr>";

try {
    
    echo "1. Iniciando transacción con <strong>\$pdo->beginTransaction()</strong>...<br>";
    $pdo->beginTransaction();

    
    echo "2. Insertando una incidencia temporal de prueba...<br>";
    $stmt1 = $pdo->prepare("INSERT INTO incidencias (titulo, descripcion, prioridad, estado) VALUES (?, ?, ?, ?)");
    $stmt1->execute([
        'Incidencia Fantasma de Prueba',
        'Esta incidencia nunca debería llegar a guardarse en la BD si el rollback funciona.',
        'Alta',
        'Abierta'
    ]);
    $ultimo_id = $pdo->lastInsertId();
    echo "   * Registro temporal preparado con ID provisional: <strong>$ultimo_id</strong>.<br>";

    
    echo "3. Provocando un error de SQL a propósito (Insertar en tabla inexistente)...<br>";
    $stmt2 = $pdo->prepare("INSERT INTO tabla_que_no_existe (campo_ficticio) VALUES ('error')");
    $stmt2->execute(); 

    
    $pdo->commit();
    echo "Éxito inesperado (No debería ocurrir).<br>";

} catch (PDOException $e) {
    
    echo "<br><span style='color: red;'>¡ERROR DETECTADO! Mensaje de MySQL: " . $e->getMessage() . "</span><br>";
    echo "4. Ejecutando <strong>\$pdo->rollBack()</strong> para deshacer todos los cambios...<br>";
    $pdo->rollBack();

    
    echo "<br>5. Verificando integridad de la base de datos...<br>";
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM incidencias WHERE id = ?");
    $check_stmt->execute([$ultimo_id]);
    $existe = $check_stmt->fetchColumn();

    if ($existe == 0) {
        echo "<span style='color: green; font-size: 18px; font-weight: bold;'>¡ÉXITO! El Rollback ha funcionado perfectamente. La incidencia provisional con ID $ultimo_id NO se ha guardado en la base de datos.</span>";
    } else {
        echo "<span style='color: red; font-size: 18px; font-weight: bold;'>FALLO: La incidencia se ha guardado. Revisa la configuración del motor de tablas (Debe ser InnoDB).</span>";
    }
}
?>
<br><br>
<a href="items_list.php" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Volver al panel</a>
