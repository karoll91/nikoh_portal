<?php
/**
 * API: Ariza topshirish
 */

header('Content-Type: application/json');
session_start();

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Faqat POST so\'rov qabul qilinadi']);
    exit;
}

try {
    // Foydalanuvchi tekshiruvi
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Tizimga kirish talab qilinadi');
    }

    $user = getUserById($_SESSION['user_id']);
    if (!$user) {
        throw new Exception('Foydalanuvchi topilmadi');
    }

    // JSON ma'lumotlarni olish
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Noto\'g\'ri ma\'lumot formati');
    }

    // Ariza ma'lumotlarini validatsiya qilish
    $required_fields = ['application_type'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception('Majburiy maydon: ' . $field);
        }
    }

    // To'lov miqdorini hisoblash
    $payment_amount = calculatePaymentAmount($input['application_type']);

    // Arizani saqlash
    $application_data = [
        'application_type' => $input['application_type'],
        'applicant_id' => $user['id'],
        'partner_passport' => $input['partner_passport'] ?? null,
        'partner_name' => $input['partner_name'] ?? null,
        'partner_birth_date' => $input['partner_birth_date'] ?? null,
        'partner_phone' => $input['partner_phone'] ?? null,
        'preferred_date' => $input['preferred_date'] ?? null,
        'ceremony_type' => $input['ceremony_type'] ?? 'oddiy',
        'payment_required' => $payment_amount,
        'status' => 'yangi'
    ];

    $application_id = insertRecord('applications', $application_data);

    // Ariza raqamini yangilash
    $application_number = date('Y') . str_pad($application_id, 6, '0', STR_PAD_LEFT);
    updateRecord('applications',
        ['application_number' => $application_number],
        'id = ?',
        [$application_id]
    );

    // Muvaffaqiyatli javob
    echo json_encode([
        'success' => true,
        'application_id' => $application_id,
        'application_number' => $application_number,
        'payment_amount' => $payment_amount,
        'message' => 'Ariza muvaffaqiyatli topshirildi'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>