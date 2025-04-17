<?php
//session_start();


if (!isset($_SESSION['user_id'])) {
    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (
        isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    );

    if ($isAjax) {
        http_response_code(401); // Unauthorized
        exit;
    }

    header("Location: login.php");
    exit;
}


?>
