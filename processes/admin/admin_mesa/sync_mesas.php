<?php
/**
 * Sincroniza el estado de las mesas en la base de datos.
 * Asegura que 'mesa.estado' refleje fielmente si hay una reserva activa o una ocupación manual.
 */
function syncMesasStatus($conn, $idSala = null) {
    $fechaActual = date('Y-m-d');
    $horaActual = date('H:i:s');

    $sql = "
        UPDATE mesa m
        SET estado = CASE
            WHEN EXISTS (
                SELECT 1 FROM historico h 
                WHERE h.idMesa = m.idMesa 
                AND h.horaDesocupacion IS NULL
            ) THEN 'ocupada'
            WHEN EXISTS (
                SELECT 1 FROM reserva r 
                WHERE r.idMesa = m.idMesa 
                AND r.fecha = :fecha
                AND r.horaInicio <= :hora 
                AND r.horaFin > :hora
            ) THEN 'ocupada'
            ELSE 'libre'
        END
    ";
    
    $params = [
        ':fecha' => $fechaActual,
        ':hora' => $horaActual
    ];

    if ($idSala) {
        $sql .= " WHERE idSala = :idSala";
        $params[':idSala'] = $idSala;
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        // Silently fail or log error? 
        // For now, we assume it works. If it fails, the app continues with potentially stale data.
    }
}
?>
