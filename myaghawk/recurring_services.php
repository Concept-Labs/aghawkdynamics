<?php

// Include database connection
require_once('_inc.php');

// Ensure user is logged in
if (!isset($_SESSION['account_id'])) {
    die('Unauthorized access. Please log in.');
}
$account_id = intval($_SESSION['account_id']);

// Fetch Parcels for dropdown
$sql_parcels = "SELECT parcel_id, nickname FROM Parcels WHERE account_id = ? ORDER BY nickname ASC";
$stmt_parcels = $conn->prepare($sql_parcels);
$stmt_parcels->bind_param("i", $account_id);
$stmt_parcels->execute();
$result_parcels = $stmt_parcels->get_result();

// Fetch Recurring Services with filtering options
$sql_recurring = "SELECT rp.*, p.nickname AS parcel_name, b.nickname AS block_name, sr.type_of_service
                  FROM RecurringPatterns rp
                  LEFT JOIN Parcels p ON rp.parcel_id = p.parcel_id
                  LEFT JOIN Blocks b ON rp.block_id = b.block_id
                  LEFT JOIN ServiceRequests sr ON rp.service_request_id = sr.service_request_id
                  WHERE rp.account_id = ? 
                  ORDER BY rp.next_scheduled_date ASC";

$stmt_recurring = $conn->prepare($sql_recurring);
$stmt_recurring->bind_param("i", $account_id);
$stmt_recurring->execute();
$result_recurring = $stmt_recurring->get_result();

?>

<section class="userSection">
    <h3>Recurring Services (in development)</h3>

    <p>&nbsp;</p>

    <!-- Filter Form (no submission needed) -->
    <form id="filterForm" class="filterServiceRequests">
        Filter Results:<br />
        <label for="parcel_id" style="display:none;">by Parcel:</label>
        <select name="parcel_id" id="parcel_id">
            <option value="">All Parcels</option>
            <?php while ($parcel = $result_parcels->fetch_assoc()): ?>
                <option value="<?php echo $parcel['parcel_id']; ?>">
                    <?php echo htmlspecialchars($parcel['nickname']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="block_id" style="display:none;">by Block:</label>
        <select name="block_id" id="block_id" disabled>
            <option value="">All Blocks</option>
        </select>
        
        <button type="button" id="resetFilters">Clear Filters</button>
    </form>
    

    <!-- Recurring Services Table -->
    <table id="recurringServicesTable" class="blockTable">
        <thead>
            <tr>
                <th>Parcel</th>
                <th>Block</th>
                <th>Service Type</th>
                <th>Next Scheduled Date</th>
                <th>Frequency</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="recurringServicesBody">
            <?php if ($result_recurring->num_rows > 0): ?>
                <?php while ($service = $result_recurring->fetch_assoc()): ?>
                    <tr data-parcel-id="<?= $service['parcel_id']; ?>" data-status="<?= $service['status']; ?>">
                        <td><?= htmlspecialchars($service['parcel_name']); ?></td>
                        <td><?= htmlspecialchars($service['block_name']); ?></td>
                        <td><?= htmlspecialchars($service['type_of_service']); ?></td>
                        <td><?= htmlspecialchars($service['next_scheduled_date']); ?></td>
                        <td><?= htmlspecialchars($service['frequency']); ?></td>
                        <td><?= htmlspecialchars($service['status']); ?></td>
                        <td align="center">
                            <a href="?view=recurring_service_edit&id=<?= $service['recurring_id']; ?>" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No recurring services found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<script>
$(document).ready(function () {
    function fetchRecurringServices() {
        let parcelId = $("#parcel_id").val();
        let status = $("#status").val();

        $.ajax({
            url: "_fetch_recurring_services.php",
            type: "GET",
            data: { parcel_id: parcelId, status: status },
            dataType: "json",
            success: function(response) {
                let tbody = $("#recurringServicesBody");
                tbody.empty();

                if (response.length > 0) {
                    $.each(response, function(index, service) {
                        let row = `<tr>
                            <td>${service.parcel_name}</td>
                            <td>${service.block_name}</td>
                            <td>${service.service_type}</td>
                            <td>${service.next_scheduled_date}</td>
                            <td>${service.frequency}</td>
                            <td>${service.status}</td>
                            <td><a href="?view=recurring_service_edit&id=${service.recurring_id}">Edit</a></td>
                        </tr>`;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="7">No recurring services found.</td></tr>');
                }
            },
            error: function() {
                console.error("Failed to fetch recurring services.");
            }
        });
    }

    // Trigger AJAX filter update
    $("#parcel_id, #status").change(fetchRecurringServices);

    // Reset Filters
    $("#resetFilters").click(function() {
        $("#parcel_id").val("");
        $("#status").val("");
        fetchRecurringServices();
    });

    // Auto-fetch on page load
    fetchRecurringServices();
});
</script>

<?php
// Close statements
$stmt_parcels->close();
$stmt_recurring->close();
$conn->close();
?>
