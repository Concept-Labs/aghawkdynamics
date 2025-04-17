<?php
require_once('_inc.php'); // Load database connection and functions

if (!session_id()) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['account_id'])) {
    die('Unauthorized access. Please log in.');
}

$account_id = intval($_SESSION['account_id']);

// Get filter parameters
$parcel_id = isset($_GET['parcel_id']) ? intval($_GET['parcel_id']) : '';
$block_id = isset($_GET['block_id']) ? intval($_GET['block_id']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="blocks_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');
if (!$output) {
    die("Failed to open output stream for CSV.");
}

// Define column headers
fputcsv($output, ['Unique Block ID',  'Block Nickname', 'Business Name', 'Acres', 'Crop Type', 'Status']);

// Build query
$query = "SELECT 
            b.unique_id AS unique_block_id, 
            b.nickname AS block_nickname, 
            a.business_name, 
            b.acres, 
            b.crop_category AS crop_type, 
            b.status 
          FROM Blocks b
          JOIN Accounts a ON b.account_id = a.account_id
          WHERE b.account_id = ?";

$params = [$account_id];
$types = "i";

if ($parcel_id) {
    $query .= " AND b.parcel_id = ?";
    $params[] = $parcel_id;
    $types .= "i";
}

$query .= " ORDER BY b.nickname ASC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Fetch and output data
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>