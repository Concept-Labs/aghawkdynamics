<?php
// serve_attachment.php
require_once '_dbconn.php';

if (isset($_GET['path'])) {
    $file_path = realpath(dirname(__DIR__, 2) . '/' . $_GET['path']);

    // Verify the file exists and is within the expected uploads directory
    if ($file_path && strpos($file_path, realpath(dirname(__DIR__, 2) . '/uploads')) === 0 && file_exists($file_path)) {
        $mime_type = mime_content_type($file_path);
        header('Content-Type: ' . $mime_type);
        readfile($file_path);
    } else {
        header('HTTP/1.0 404 Not Found');
        echo "File not found.";
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    echo "Invalid request.";
}
?>
