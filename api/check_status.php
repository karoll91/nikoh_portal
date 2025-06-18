<?php
/**
 * API: Ariza holatini tekshirish
 */

header('Content-Type: application/json');
session_start();

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    $application_number = $_GET['number'] ?? '';
    $passport = $_GET['passport'] ?? '';

    if (empty($application_number) || empty($passport)) {
        throw new Exception('Ariza raqami va pasport ma\'lumotlari talab qilinadi');
    }

    // Arizani topish
    $sql = "SELECT a.*, u.first_name, u.last_name, u.passport_series 
            FROM applications a 
            JOIN users u ON a.applicant_id = u.id 
            WHERE a.application_number = ? AND u.passport_series = ?";

    $application = fetchOne($sql, [$application_number, $passport]);

    if (!$application) {
        throw new Exception('Ariza topilmadi');
    }

    // Status ma'lumotlarini olish
    $status_info = getApplicationStatus($application['status']);
    $type_info = getApplicationType($application['application_type']);

    // Javob tayyorlash
    $response = [
        'success' => true,
        'application' => [
            'number' => $application['application_number'],
            'type' => $application['application_type'],
            'type_label' => $type_info['label'],
            'status' => $application['status'],
            'status_label' => $status_info['label'],
            'created_date' => formatDate($application['created_at']),
            'applicant_name' => $application['first_name'] . ' ' . $application['last_name'],
            'payment_required' => formatMoney($application['payment_required']),
            'payment_status' => $application['payment_status']
        ]
    ];

    // Qo'shimcha ma'lumotlar
    if ($application['preferred_date']) {
        $response['application']['preferred_date'] = formatDate($application['preferred_date']);
    }

    if ($application['appointment_date']) {
        $response['application']['appointment_date'] = formatDateTime($application['appointment_date']);
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>