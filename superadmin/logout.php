<?php
session_start();
require_once __DIR__ . '/_auth.php';
sa_log('logout_superadmin', 'Logout');
session_destroy();
header('Location: login.php');
exit;
