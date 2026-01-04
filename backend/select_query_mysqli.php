<?php
require_once "../backend/connection.php";

/* =========================
   MARIADB - APPOINTMENT
========================= */

/* ---------- ALL APPOINTMENTS ---------- */
function getAllAppointmentsMaria() {
    $conn = getMariaDBConnection();

    $stmt = $conn->query("
        SELECT
            appointment_id,
            owner_id,
            pet_id,
            vet_id,
            service_id,
            date,
            time,
            status,
            created_at
        FROM appointment
        ORDER BY date DESC, time DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------- APPOINTMENTS BY OWNER ---------- */
function getAppointmentsByOwnerMaria($ownerID) {
    $conn = getMariaDBConnection();

    $stmt = $conn->prepare("
        SELECT
            appointment_id,
            pet_id,
            vet_id,
            service_id,
            date,
            time,
            status,
            created_at
        FROM appointment
        WHERE owner_id = ?
        ORDER BY date DESC, time DESC
    ");
    $stmt->execute([$ownerID]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------- APPOINTMENTS BY VET ---------- */
function getAppointmentsByVetMaria($vetID) {
    $conn = getMariaDBConnection();

    $stmt = $conn->prepare("
        SELECT
            appointment_id,
            owner_id,
            pet_id,
            service_id,
            date,
            time,
            status,
            created_at
        FROM appointment
        WHERE vet_id = ?
        ORDER BY date DESC, time DESC
    ");
    $stmt->execute([$vetID]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------- SINGLE APPOINTMENT ---------- */
function getAppointmentByIdMaria($appointmentID) {
    $conn = getMariaDBConnection();

    $stmt = $conn->prepare("
        SELECT
            appointment_id,
            owner_id,
            pet_id,
            vet_id,
            service_id,
            date,
            time,
            status,
            created_at
        FROM appointment
        WHERE appointment_id = ?
    ");
    $stmt->execute([$appointmentID]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ---------- UPDATE APPOINTMENT STATUS (ADDED FOR SYNC) ---------- */
function updateAppointmentStatusMaria($appointmentID, $status) {
    $conn = getMariaDBConnection();
    if (!$conn) return false;

    try {
        // Map Treatment Status to Appointment Status
        // If Treatment is 'Completed', Appointment becomes 'Done'
        $apptStatus = 'Pending';
        if ($status === 'Completed') {
            $apptStatus = 'Done';
        } else if ($status === 'In Progress') {
            $apptStatus = 'In Progress'; 
        }

        $stmt = $conn->prepare("UPDATE appointment SET status = ? WHERE appointment_id = ?");
        $stmt->execute([$apptStatus, $appointmentID]);
        return true;
    } catch (PDOException $e) {
        // Log error internally if needed, don't stop execution
        error_log("Error updating appointment status: " . $e->getMessage());
        return false;
    }
}
?>