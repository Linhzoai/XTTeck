<?php

require_once 'session_config.php';

// Xóa tất cả session
session_unset();
session_destroy();

// Chuyển hướng về trang login
header('Location: login.php?logout=1');
exit;
