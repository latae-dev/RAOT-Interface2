<?php
session_start(); // เริ่มต้น Session

function checkAuth()
{
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(["message" => "Unauthorized"]);
        exit;
    }
}