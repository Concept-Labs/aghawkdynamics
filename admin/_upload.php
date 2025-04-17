<?php
// Enable error reporting for troubleshooting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '_dbconn.php'; // database connection 

// FILE UPLOADS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    // Input values
    $type = $_POST['type']; // should be 'block' or 'service_request'
    $block_id = isset($_POST['block_id']) ? intval($_POST['block_id']) : null;
    $service_request_id = isset($_POST['service_request_id']) ? intval($_POST['service_request_id']) : null;
    $account_id = intval($_POST['account_id']);
    $comments = isset($_POST['comments']) ? htmlspecialchars($_POST['comments'], ENT_QUOTES) : '';

    // Determine the upload directory based on type
    $base_dir = dirname(__DIR__, 2) . '/uploads'; // Go two levels up from 'public_html/admin'

    // Create the base directory if it does not exist
    if (!file_exists($base_dir)) {
        if (!mkdir($base_dir, 0755, true)) {
            die('Failed to create base upload directory.');
        }
    }

    // Determine specific directory for block or service request
    if ($type == 'block' && $block_id) {
        $upload_dir = $base_dir . '/' . $account_id . '/blocks/' . $block_id;
    } elseif ($type == 'service_request' && $service_request_id) {
        $upload_dir = $base_dir . '/' . $account_id . '/service_requests/' . $service_request_id;
    } else {
        die('Invalid type or missing IDs.');
    }

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            die('Failed to create folders...');
        }
    }

    // File handling
    $original_filename = basename($_FILES['file']['name']);
    $file_type = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));  // Set the file type correctly

    // Construct target file path
    $target_file_full = $upload_dir . '/' . $original_filename;

    // Check if a file with the same name already exists, if yes, append a timestamp
    if (file_exists($target_file_full)) {
        $timestamp = time();
        $filename_without_extension = pathinfo($original_filename, PATHINFO_FILENAME);
        $target_file_full = $upload_dir . '/' . $filename_without_extension . '_' . $timestamp . '.' . $file_type;
    }

    // Construct relative path to store in the database
    $target_file = '/uploads/' . $account_id;

    if ($type == 'block' && $block_id) {
        $target_file .= '/blocks/' . $block_id . '/' . basename($target_file_full);
    } elseif ($type == 'service_request' && $service_request_id) {
        $target_file .= '/service_requests/' . $service_request_id . '/' . basename($target_file_full);
    }

    // Validate file type
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'pdf', 'doc', 'docx'];
    if (!in_array($file_type, $allowed_types)) {
        die('Invalid file type.');
    }

    // Move file to the target location
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file_full)) {
        // Save file details to database using the existing $conn connection
        try {
            if ($type == 'block') {
                $stmt = $conn->prepare("INSERT INTO BlockAttachments (block_id, account_id, file_path, file_type, comments, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iisss", $block_id, $account_id, $target_file, $file_type, $comments);
            } elseif ($type == 'service_request') {
                $stmt = $conn->prepare("INSERT INTO ServiceRequestAttachments (service_request_id, account_id, file_path, file_type, comments, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iisss", $service_request_id, $account_id, $target_file, $file_type, $comments);
            }

            if ($stmt->execute()) {
                echo "File has been uploaded and recorded successfully.";
            } else {
                echo "Database error: " . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            die("Database error: " . $e->getMessage());
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
        // Log more details for debugging
        if (!is_writable($upload_dir)) {
            echo "Upload directory is not writable.";
        } else {
            echo "Error moving the uploaded file to the target location.";
        }
    }
}
?>
