<?php
require_once '../include/config.php';

if (isset($_SESSION['admin_id'])) {
    logActivity($_SESSION['admin_id'], 'logout', 'auth', 'Admin logged out');
}

session_unset();
session_destroy();

header('Location: login.php');
exit();
