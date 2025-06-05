<?php

session_start();

if (!isset($_SESSION['token']) && !isset($_SESSION['name'])) {
    header('Location: ../login/index.php');
    exit;
} else {
    header('Location: ../panel/index.php');
    exit;
}


?>