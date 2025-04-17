<?php
require('_inc.php'); // Include common settings, session check, and database connection

header('Content-Type: application/json');

if (isset($_GET['contact_email'])) {
    $contact_email = $_GET['contact_email'];

    $sql = "SELECT 1 FROM Accounts_Users WHERE contact_email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $contact_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(["exists" => true]);
        } else {
            echo json_encode(["exists" => false]);
        }
    } else {
        error_log("Error preparing statement: " . $conn->error);
        echo json_encode(["error" => "Internal Server Error"]);
    }
} else {
    echo json_encode(["error" => "Missing contact_email parameter"]);
}
?>
