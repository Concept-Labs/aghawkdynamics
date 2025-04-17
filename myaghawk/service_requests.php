<?php
// Include database connection
require_once('_inc.php');

//this needs to go directly after _inc.php
if (isset($_GET['export']) && $_GET['export'] == "csv") {
    exportServiceRequests($conn); // Call the function to export
}

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
?>

<section class="userSection">
    <h3>All Activities</h3>

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
        

        <label for="activity_type" style="display:none;">by Activity Type:</label>
        <select name="activity_type" id="activity_type">
            <option value="">All Activity Type</option>
            <option value="">Service Requests</option>
            <option value="">Self Tracking</option>
        </select>
        

        <label for="status" style="display:none;">by Status:</label>
        <select name="status" id="status">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Completed">Completed</option>
        </select>
        
        <button type="button" id="resetFilters">Clear Filters</button>
    </form>
        
    
    <div class="pageHdrActions serviceRequests">
        <br />
        <a class="addEditLink" href="service_request_add" title="Request Service"><i class="fa-solid fa-square-plus"></i> Request Service</a>
        <a class="addEditLink" href="self_tracking" title="Request Service"><i class="fa-solid fa-list-check"></i> Track</a>
        <a target="_blank" class="exportBtn" href="export_service_requests.php" title="Export Records"><i class="fa-solid fa-file-export"></i> Export</a>
    </div>

    <!-- Service Requests Table -->
    <table id="serviceRequestsTable" class="blockTable">
        <thead>
            <tr>
                <th>Parcel Name</th>
                <th>Block Name</th>
                <th>Activity Type</th>
                <th>Type of Service</th>
                <th>Application Need By Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="serviceRequestsBody">
            <!-- Data will be loaded dynamically via AJAX -->
        </tbody>
    </table>
</section>

<script>
$(document).ready(function () {
    function getURLParam(param) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    function fetchServiceRequests() {
        let parcelId = $("#parcel_id").val();
        let blockId = $("#block_id").val();
        let status = $("#status").val();

        $.ajax({
            url: "_fetch_service_requests.php",
            type: "GET",
            data: { parcel_id: parcelId, block_id: blockId, status: status },
            dataType: "json",
            success: function (response) {
                let tbody = $("#serviceRequestsBody");
                tbody.empty();

                if (response.length > 0) {
                    $.each(response, function (index, service) {
                        let statusText = service.status_completed == 1 ? "Completed" : "Pending";
                        let actionLink =
                            service.status_completed == 1
                                ? `<a class="addEditLink" href="service_request_detail?service_request_id=${service.service_request_id}" title="View"><i class="fa-solid fa-eye"></i></a>`
                                : `<a class="addEditLink" href="service_request_edit?service_request_id=${service.service_request_id}" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                        
                                    <!--<a class="addEditLink" href="self_tracking" title="Self-Tracking (coming soon)">
                                        <i class="fa-solid fa-list-check"></i>
                                    </a>-->

                                    <a class="addEditLink" href="service_request_cancel?service_request_id=${service.service_request_id}" title="Cancel Request"><i class="fa-solid fa-rotate-left"></i></a>`;

                        let row = `<tr>
                            <td><a class="underline" href="parcel_detail?parcel_id=${service.parcel_id}">${service.parcel_name}</a></td>
                            <td><a class="underline" href="block_detail?block_id=${service.block_id}">${service.block_name}</a></td>
                            <td>Service Request</td>
                            <td>${service.type_of_service}</td>
                            <td>${service.application_need_by_date}</td>
                            <td>${statusText}</td>
                            <td class="actions" align="center">${actionLink}</td>
                        </tr>`;
                        tbody.append(row);
                    });
                } else {
                    tbody.append(
                        `<tr>
                            <td colspan="6" class="no-results">No services found. <a href="service_request_add">Create a Service Request</a>.</td>
                        </tr>`
                    );
                }
            },
            error: function () {
                console.error("Failed to fetch service requests.");
            },
        });

        updateExportLink(); // Ensure the Export button updates after fetching data
    }

    function updateBlocksDropdown(parcelId, preSelectedBlockId = null) {
        let blockDropdown = $("#block_id");
        blockDropdown.empty().append('<option value="">All Blocks</option>').prop("disabled", true);

        if (parcelId) {
            $.get("_get_blocks.php", { parcel_id: parcelId }, function (data) {
                blockDropdown.prop("disabled", false);
                let addedBlockIds = new Set();

                $.each(data, function (index, block) {
                    if (!addedBlockIds.has(block.block_id)) {
                        addedBlockIds.add(block.block_id);
                        let selected = preSelectedBlockId == block.block_id ? "selected" : "";
                        blockDropdown.append(`<option value="${block.block_id}" ${selected}>${block.nickname}</option>`);
                    }
                });

                // Ensure the block ID is updated in the export URL
                updateExportLink();
            }, "json");
        } else {
            updateExportLink();
        }
    }

    function updateExportLink() {
        let parcelId = $("#parcel_id").val() || getURLParam("parcel_id");
        let blockId = $("#block_id").val() || getURLParam("block_id");
        let status = $("#status").val() || getURLParam("status");

        let params = new URLSearchParams();
        if (parcelId && parcelId !== "") params.append("parcel_id", parcelId);
        if (blockId && blockId !== "") params.append("block_id", blockId);
        if (status && status !== "") params.append("status", status);

        let exportUrl = "export_service_requests.php";
        if (params.toString()) {
            exportUrl += "?" + params.toString();
        }

        $(".exportBtn").attr("href", exportUrl);
    }

    // Detect URL parameters on page load
    let urlParcelId = getURLParam("parcel_id");
    let urlBlockId = getURLParam("block_id");

    if (urlBlockId) {
        $.get("_get_parcel_by_block.php", { block_id: urlBlockId }, function (response) {
            if (response.parcel_id) {
                $("#parcel_id").val(response.parcel_id);
                updateBlocksDropdown(response.parcel_id, urlBlockId);
                fetchServiceRequests();
            }
        }, "json");
    } else if (urlParcelId) {
        $("#parcel_id").val(urlParcelId);
        updateBlocksDropdown(urlParcelId, urlBlockId);
    }

    fetchServiceRequests(); // Auto-fetch service requests on page load

    // Event listeners
    $("#parcel_id, #block_id, #status").change(fetchServiceRequests);
    $("#parcel_id").change(function () {
        let selectedParcel = $(this).val();
        if (!selectedParcel) {
            $("#block_id").empty().append('<option value="">All Blocks</option>').prop("disabled", true);
        } else {
            updateBlocksDropdown(selectedParcel);
        }
        fetchServiceRequests();
    });

    $("#resetFilters").click(function () {
        $("#parcel_id, #block_id, #status").val(""); // Reset filters
        $("#block_id").prop("disabled", true).html('<option value="">All Blocks</option>'); // Reset Block dropdown
        fetchServiceRequests();
    });

    // Ensure Export button updates when filters change
    $("#parcel_id, #block_id, #status, #resetFilters").on("change click", updateExportLink);

    updateExportLink(); // Initialize Export button URL on page load
});

</script>
