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
header('Content-Disposition: attachment; filename="service_requests_' . date('Y-m-d_H-i-s') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');
if (!$output) {
    die("Failed to open output stream for CSV.");
}

// Define column headers
fputcsv($output, ['Service Request ID', 'Unique ID', 'Business Name', 'Parcel Nickname', 'Block Nickname', 'Contact First Name', 'Contact Last Name', 'Reason for Application', 'Type of Service', 'Type of Product', 'Product Name', 'Product Name Other', 'Supplier Name', 'Supplier Contact Phone', 'Supplier Contact Name', 'Application Need By Date', 'Urgent Request', 'Urgent Request Date', 'Status Assigned', 'Status Completed', 'Status Invoiced', 'Status Paid', 'Scheduled Date', 'Completion Date', 'Invoiced Date', 'Paid Date', 'Comments', 'Created At', 'Updated At', 'Recurrence ID', 'Service Type', 'Frequency', 'Recurrence End Date', 'Recurrence Next Date', 'Last Occurrence Date', 'Completion ID', 'Completion Unique ID', 'Completed By', 'Temperature', 'Wind', 'Restricted Exposure Hours', 'Completion Status', 'Completion Created At', 'Completion Updated At']);

// Build query
$query = "SELECT sr.service_request_id, sr.unique_id, a.business_name, p.nickname AS parcel_nickname, b.nickname AS block_nickname, au.contact_first_name AS contact_first_name, au.contact_last_name AS contact_last_name, sr.reason_for_application, sr.type_of_service, sr.type_of_product, sr.product_name, sr.product_name_other, sr.supplier_name, sr.supplier_contact_phone, sr.supplier_contact_name, sr.application_need_by_date, sr.urgent_request, sr.urgent_request_date, sr.status_assigned, sr.status_completed, sr.status_invoiced, sr.status_paid, sr.scheduled_date, sr.completion_date, sr.invoiced_date, sr.paid_date, sr.comments, sr.created_at, sr.updated_at, sr.recurrence_id, sr.service_type, sr.frequency, sr.recurrence_end_date, sr.recurrence_next_date, sr.last_occurrence_date,
          sc.completion_id, sc.unique_id AS completion_unique_id, sc.completed_by, sc.temperature, sc.wind, sc.restricted_exposure_hrs, sc.status AS completion_status, sc.created_at AS completion_created_at, sc.updated_at AS completion_updated_at
          FROM ServiceRequests sr
          JOIN Accounts a ON sr.account_id = a.account_id
          JOIN Parcels p ON sr.parcel_id = p.parcel_id
          JOIN Blocks b ON sr.block_id = b.block_id
          LEFT JOIN Accounts_Users au ON sr.contact_id = au.account_user_id
          LEFT JOIN ServiceCompletions sc ON sr.service_request_id = sc.service_request_id AND sr.completion_date IS NOT NULL
          WHERE sr.account_id = ?";

$params = [$account_id];
$types = "i";

if ($parcel_id) {
    $query .= " AND sr.parcel_id = ?";
    $params[] = $parcel_id;
    $types .= "i";
}
if ($block_id) {
    $query .= " AND sr.block_id = ?";
    $params[] = $block_id;
    $types .= "i";
}
if ($status !== '') {
    $query .= " AND sr.status_completed = ?";
    $params[] = ($status === 'Completed') ? 1 : 0;
    $types .= "i";
}

$query .= " ORDER BY sr.application_need_by_date ASC";

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
