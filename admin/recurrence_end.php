<?php

// Get the recurrence ID from the URL
$recurrence_id = intval($_GET['recurrence_id'] ?? 0);

if ($recurrence_id > 0) {
    // Set the end date to today to end the recurrence
    $sql = "UPDATE RecurringPatterns SET recurrence_end_date = CURDATE() WHERE recurrence_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recurrence_id);

    if ($stmt->execute()) {
        // Redirect to recurring services management page after successful end
        header("Location: ?view=recurring_services.php&message=Recurrence ended successfully");
        exit();
    } else {
        echo "Error ending recurrence: " . $conn->error;
    }
} else {
    echo "Invalid recurrence ID.";
}

$conn->close();
?>
