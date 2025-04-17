<?php
require('_inc.php'); // Include common settings, session check, and database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (isset($_GET['parcel_id'])) {
    $parcel_id = intval($_GET['parcel_id']);
    
    if ($parcel_id) {
        $sql = "SELECT block_id, nickname FROM Blocks WHERE parcel_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Statement preparation failed: " . $conn->error);
            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
            exit();
        }

        $stmt->bind_param("i", $parcel_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            error_log("Query execution failed: " . $stmt->error);
            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
            exit();
        }

        $blocks = [];
        while ($row = $result->fetch_assoc()) {
            $blocks[] = $row;
        }

        echo json_encode($blocks);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid parcel_id"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "parcel_id is required"]);
}

$conn->close();
?>
