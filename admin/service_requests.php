<?php

// Include database connection
require_once('_inc.php');

// Fetch filter parameters if provided
$filter_account_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : null;
$filter_status = isset($_GET['status']) ? $_GET['status'] : null;

// Prepare SQL query to fetch service requests
$sql_service_requests = "SELECT ServiceRequests.*, Accounts.business_name FROM ServiceRequests 
                         INNER JOIN Accounts ON ServiceRequests.account_id = Accounts.account_id";
$conditions = [];
$params = [];
$types = "";

// Apply filters if provided
if ($filter_account_id) {
    $conditions[] = "ServiceRequests.account_id = ?";
    $params[] = $filter_account_id;
    $types .= "i";
}
if ($filter_status) {
    if ($filter_status == 'Pending') {
        $conditions[] = "ServiceRequests.status_completed = 0";
    } elseif ($filter_status == 'Completed') {
        $conditions[] = "ServiceRequests.status_completed = 1";
    }
}

// Add conditions to the query
if (!empty($conditions)) {
    $sql_service_requests .= " WHERE " . implode(" AND ", $conditions);
}
$sql_service_requests .= " ORDER BY ServiceRequests.application_need_by_date ASC";

// Prepare and execute the query
$stmt_service_requests = $conn->prepare($sql_service_requests);
if (!empty($params)) {
    $stmt_service_requests->bind_param($types, ...$params);
}
$stmt_service_requests->execute();
$result_service_requests = $stmt_service_requests->get_result();

?>

<section class="adminSection">
    <h3>Service Requests</h3>

    <!-- Filter Form -->
    <form id="filterForm" class="filterServiceRequests" method="GET">
        <label for="account_id">Filter by Account:</label>
        <select name="account_id" id="account_id">
            <option value="">All Accounts</option>
            <?php
            $sql_accounts = "SELECT account_id, business_name FROM Accounts ORDER BY business_name ASC";
            $result_accounts = $conn->query($sql_accounts);
            while ($account = $result_accounts->fetch_assoc()) {
                $selected = ($filter_account_id == $account['account_id']) ? "selected" : "";
                echo "<option value='" . $account['account_id'] . "' $selected>" . htmlspecialchars($account['business_name']) . "</option>";
            }
            ?>
        </select>

        <label for="status">Filter by Status:</label>
        <select name="status" id="status">
            <option value="">All Statuses</option>
            <option value="Pending" <?php echo ($filter_status == 'Pending') ? "selected" : ""; ?>>Pending</option>
            <option value="Completed" <?php echo ($filter_status == 'Completed') ? "selected" : ""; ?>>Completed</option>
        </select>
    </form>
	

    <!-- Service Requests Table -->
    <table id="serviceRequestsTable">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Account Name</th>
                <th>Type of Service</th>
                <th>Application Need By Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_service_requests->num_rows > 0): ?>
                <?php while ($service = $result_service_requests->fetch_assoc()): ?>
                    <tr data-account-id="<?php echo $service['account_id']; ?>" data-status="<?php echo ($service['status_completed'] == 1) ? 'Completed' : 'Pending'; ?>">
                        <td><?php echo htmlspecialchars($service['service_request_id']); ?></td>
                        <td><?php echo htmlspecialchars($service['business_name']); ?></td>
                        <td><?php echo htmlspecialchars($service['type_of_service']); ?></td>
                        <td><?php echo htmlspecialchars($service['application_need_by_date']); ?></td>
                        <td><?php echo ($service['status_completed'] == 1) ? "Completed" : "Pending"; ?></td>
                        <td>
                            <a href="?view=service_request_edit&id=<?php echo $service['service_request_id']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No service requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<script>
    function filterServiceRequests() {
        var accountId = document.getElementById('account_id').value;
        var status = document.getElementById('status').value;
        var rows = document.querySelectorAll('#serviceRequestsTable tbody tr');

        rows.forEach(function (row) {
            var rowAccountId = row.getAttribute('data-account-id');
            var rowStatus = row.getAttribute('data-status');

            var showRow = true;

            if (accountId && rowAccountId !== accountId) {
                showRow = false;
            }

            if (status && rowStatus !== status) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    document.getElementById('account_id').addEventListener('change', filterServiceRequests);
    document.getElementById('status').addEventListener('change', filterServiceRequests);
</script>

<?php
// Close statement and connection
$stmt_service_requests->close();
$conn->close();
?>
