<?php
require('_inc.php'); // Include common settings, session check, and database connection

if (!isset($_SESSION['account_id'])) {
    die(json_encode([]));
}

$account_id = $_SESSION['account_id'];
$parcel_id = isset($_GET['parcel_id']) ? intval($_GET['parcel_id']) : null;
$block_id = isset($_GET['block_id']) ? intval($_GET['block_id']) : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

$sql = "SELECT RecurringServices.*, Parcels.nickname AS parcel_name, Blocks.nickname AS block_name
        FROM RecurringServices
        INNER JOIN Parcels ON RecurringServices.parcel_id = Parcels.parcel_id
        INNER JOIN Blocks ON RecurringServices.block_id = Blocks.block_id
        WHERE RecurringServices.account_id = ?";

$params = [$account_id];
$types = "i";

if ($parcel_id) {
    $sql .= " AND RecurringServices.parcel_id = ?";
    $params[] = $parcel_id;
    $types .= "i";
}
if ($block_id) {
    $sql .= " AND RecurringServices.block_id = ?";
    $params[] = $block_id;
    $types .= "i";
}
if ($status) {
    if ($status == 'Active') {
        $sql .= " AND RecurringServices.status = 'active'";
    } elseif ($status == 'Inactive') {
        $sql .= " AND RecurringServices.status = 'inactive'";
    }
}

$sql .= " ORDER BY RecurringServices.next_application_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$recurring_services = [];
while ($service = $result->fetch_assoc()) {
    $recurring_services[] = $service;
}

echo json_encode($recurring_services);

$stmt->close();
$conn->close();
?>
