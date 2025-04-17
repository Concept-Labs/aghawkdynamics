<?php

require_once '_dbconn.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $block_id = $_GET['block_id'];
    $service_request_id = $_GET['service_request_id'];
    if ($action == 'load') {
        header('Content-Type: application/json');

        $query = "SELECT 'block' AS type, attachment_id AS id, file_path, comments FROM BlockAttachments WHERE block_id='$block_id' 
                  UNION 
                  SELECT 'service_request' AS type, attachment_id AS id, file_path, comments FROM ServiceRequestAttachments WHERE service_request_id='$service_request_id' 
                  ORDER BY id DESC";
        $result = $conn->query($query);

        $attachments = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $attachments[] = $row;
            }
        }

        echo json_encode($attachments);
    } elseif ($action == 'delete' && isset($_GET['id']) && isset($_GET['type'])) {
        $attachment_id = intval($_GET['id']);
        $type = $_GET['type'];

        if ($type === 'block') {
            // Delete from BlockAttachments
            $query = "SELECT file_path FROM BlockAttachments WHERE attachment_id = $attachment_id";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $file_path = dirname(__DIR__, 2) . $row['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path); // Delete the file from the server
                }
                $query = "DELETE FROM BlockAttachments WHERE attachment_id = $attachment_id";
                $conn->query($query);
            }
        } elseif ($type === 'service_request') {
            // Delete from ServiceRequestAttachments
            $query = "SELECT file_path FROM ServiceRequestAttachments WHERE attachment_id = $attachment_id";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $file_path = dirname(__DIR__, 2) . $row['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path); // Delete the file from the server
                }
                $query = "DELETE FROM ServiceRequestAttachments WHERE attachment_id = $attachment_id";
                $conn->query($query);
            }
        }

        echo "Attachment deleted successfully.";
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['type'])) {
        // Decode JSON input
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            // JSON decode failed, log the error
            error_log("Failed to decode JSON: " . json_last_error_msg());
            echo "Error: Failed to receive data.";
            exit();
        }

        if (isset($data['id']) && isset($data['comment'])) {
            $attachment_id = intval($data['id']);
            $new_comment = $conn->real_escape_string($data['comment']);
            $type = $_GET['type'];

            if ($type === 'block') {
                // Update the BlockAttachments table
                $query = "UPDATE BlockAttachments SET comments = '$new_comment' WHERE attachment_id = $attachment_id";
                $result = $conn->query($query);
            } elseif ($type === 'service_request') {
                // Update the ServiceRequestAttachments table
                $query = "UPDATE ServiceRequestAttachments SET comments = '$new_comment' WHERE attachment_id = $attachment_id";
                $result = $conn->query($query);
            }

            if ($conn->affected_rows > 0) {
                echo "Comment updated successfully.";
            } else {
                echo "Error: Comment could not be updated.";
            }
        } else {
            echo "Error: Missing 'id' or 'comment' in received data.";
        }
    } else {
        echo "Error: Invalid request.";
    }
}

?>
