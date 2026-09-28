<?php

session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $session_csrf = $_SESSION["csrf"] ?? "";
    $post_csrf = $_POST["csrf"] ?? "";
    
    if (!hash_equals($session_csrf, $post_csrf)) {
        // Jika token tidak cocok, hentikan program dan berikan error 403 Forbidden
        http_response_code(403);
        exit("Error: Token CSRF tidak valid. Akses ditolak.");
    }

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    
    if ($id) {
        // 3. Eksekusi query DELETE dengan prepared statement
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(["id" => $id]);
    }

    header("Location: index.php?status=deleted");
    exit;
} else {

    header("Location: index.php");
    exit;
}
?>