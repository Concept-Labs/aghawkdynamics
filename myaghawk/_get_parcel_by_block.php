<?php
require_once('_inc.php');

if (!isset($_GET['block_id'])) {
    echo json_encode(["parcel_id" => null]);
    exit;
}

$block_id = intval($_GET['block_id']);

$sql = "SELECT parcel_id FROM Blocks WHERE block_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $block_id);
$stmt->execute();
$result = $stmt->get_result();
$parcel = $result->fetch_assoc();

echo json_encode($parcel ? $parcel : ["parcel_id" => null]);

$stmt->close();
$conn->close();
?>
