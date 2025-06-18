<?php
/**
 * Tizimdan chiqish
 */

require_once 'includes/auth.php';

// Logout qilish
logout();

$_SESSION['success_message'] = 'Tizimdan muvaffaqiyatli chiqdingiz!';
redirect('home');
?>