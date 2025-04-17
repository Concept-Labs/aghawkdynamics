<?php
require('_inc.php'); // Include common settings, session check, and database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (isset($_GET['account_id'])) {
    $account_id = intval($_GET['account_id']);

    if ($account_id) {
        $sql = "SELECT parcel_id, nickname FROM Parcels WHERE account_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Statement preparation failed: " . $conn->error);
            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
            exit();
        }

        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            error_log("Query execution failed: " . $stmt->error);
            http_response_code(500);
            echo json_encode(["error" => "Internal Server Error"]);
            exit();
        }

        $parcels = [];
        while ($row = $result->fetch_assoc()) {
            $parcels[] = $row;
        }

        echo json_encode($parcels);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid account_id"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "account_id is required"]);
}

$conn->close();
?>
