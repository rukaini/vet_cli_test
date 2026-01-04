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

function updateAppointmentStatusMaria($appointmentID, $treatmentStatus) {
    $conn = getMariaDBConnection(); 
    if (!$conn) return false;

    try {
        // --- MATCH ANIQ'S EXACT ENUM VALUES ---
        // His Allowed Values: 'Pending', 'Confirmed', 'Cancelled', 'Completed'
        
        $newStatus = 'Pending'; // Default fallback

        if ($treatmentStatus === 'Completed') {
            $newStatus = 'Completed'; // Perfect match!
        } 
        elseif ($treatmentStatus === 'In Progress') {
            // "In Progress" isn't in his list, so we use "Confirmed"
            // This tells him the appointment is active/confirmed.
            $newStatus = 'Confirmed'; 
        }
        elseif ($treatmentStatus === 'Deceased') {
            $newStatus = 'Cancelled'; // Appointment stops
        }

        $sql = "UPDATE appointment SET status = ? WHERE appointment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$newStatus, $appointmentID]);
        
        return true;

    } catch (PDOException $e) {
        // Log the error but don't crash the page
        error_log("Sync Error: " . $e->getMessage());
        return false;
    }
}