<?php
require('_inc.php'); // Include common settings, session check, and database connection

if (!isset($_SESSION['account_id'])) {
    die(json_encode([]));
}

$account_id = $_SESSION['account_id'];
$parcel_id = isset($_GET['parcel_id']) ? intval($_GET['parcel_id']) : null;
$block_id = isset($_GET['block_id']) ? intval($_GET['block_id']) : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

$sql = "SELECT SelfServiceRequests.*, Parcels.nickname AS parcel_name, Blocks.nickname AS block_name
        FROM SelfServiceRequests
        INNER JOIN Parcels ON SelfServiceRequests.parcel_id = Parcels.parcel_id
        INNER JOIN Blocks ON SelfServiceRequests.block_id = Blocks.block_id
        WHERE SelfServiceRequests.account_id = ?";

$params = [$account_id];
$types = "i";

if ($parcel_id) {
    $sql .= " AND SelfServiceRequests.parcel_id = ?";
    $params[] = $parcel_id;
    $types .= "i";
}
if ($block_id) {
    $sql .= " AND SelfServiceRequests.block_id = ?";
    $params[] = $block_id;
    $types .= "i";
}
if ($status) {
    if ($status == 'Pending') {
        $sql .= " AND SelfServiceRequests.status_completed = 0";
    } elseif ($status == 'Completed') {
        $sql .= " AND SelfServiceRequests.status_completed = 1";
    }
}

$sql .= " ORDER BY SelfServiceRequests.application_need_by_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$service_requests = [];
while ($service = $result->fetch_assoc()) {
    $service_requests[] = $service;
}

echo json_encode($service_requests);

$stmt->close();
$conn->close();
?>
