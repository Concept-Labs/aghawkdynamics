<?php // PARCELS V2

// Get account ID from the URL
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}
$account_id = intval($_SESSION['account_id']);

// Fetch account details from the database
$sql_account = "SELECT * FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($sql_account);
$stmt_account->bind_param("i", $account_id);
$stmt_account->execute();
$result_account = $stmt_account->get_result();

if ($result_account->num_rows == 0) {
    die('Account not found.');
}
$account = $result_account->fetch_assoc();


// DEACTIVATE PARCEL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deactivateParcel' && isset($_POST['parcel_id'])) {
    $parcel_id = intval($_POST['parcel_id']);
    $deactivate_notes = isset($_POST['deactivateNotes']) ? trim($_POST['deactivateNotes']) : '';
    deactivate_parcel($parcel_id, $account_id, $deactivate_notes);
}
// ACTIVATE PARCEL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'activateParcel' && isset($_POST['parcel_id'])) {
    $parcel_id = intval($_POST['parcel_id']);
    activate_parcel($parcel_id, $account_id);
}

// Fetch parcels associated with the account
$sql_parcels = "SELECT * FROM Parcels WHERE account_id = ?";
$stmt_parcels = $conn->prepare($sql_parcels);
$stmt_parcels->bind_param("i", $account_id);
$stmt_parcels->execute();
$result_parcels = $stmt_parcels->get_result();
?>


<?php 
    if(isset($_SESSION['displayMsg'])) { 
?>
        <div class="displayMsg"><?= $_SESSION['displayMsg']; ?></div>
        <script>
            $(document).ready(function() {								
                if ($('.displayMsg').length) {// Check if the displayMsg element exists									
                    $('.displayMsg').delay(6000).slideUp(500, function() {
                        $(this).remove();
                    });
                }
            });
        </script>
<?php
        unset($_SESSION['displayMsg']);
    } //end displayMsg
?>


<h3>Parcels</h3>

<p><hr /></p>

<div id="parcelActionModal" class="modal">
    <div class="modal-content">
        <h4 id="parcelModalTitle">Confirm Action</h4>
        <br />
        <p id="parcelModalMessage">
            Are you sure you want to <strong><span id="parcelActionType"></span></strong> parcel "<strong><span id="parcelNickname"></span></strong>"?
        </p>
        <form action="parcels" method="post">
            <input type="hidden" name="action" value="" id="modalParcelAction" />
            <input type="hidden" name="parcel_id" value="" id="modalParcelId" />
            <textarea name="deactivateNotes" id="parcelDeactivateNotesField" placeholder="Reason for deactivation?"></textarea>
            <div class="modal-buttons">
                <button id="confirmParcelAction" class="confirm-btn"></button>
                <button id="cancelParcelAction" class="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div id="customConfirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h4>Are you sure?</h4>
        <br />
        <p>The cancellation of any pending service requests as a result of deactivating this parcel can't be reversed.</p>
        <button id="confirmYes" class="btn btn-danger">Yes, I'm sure.</button>
        <button id="confirmNo" class="btn btn-secondary">Nevermind</button>
    </div>
</div>


<section class="userSection Parcels">
    
    <div class="pageHdrActions">
        <a class="addEditLink" href="parcel_add"><i class="fa-solid fa-square-plus"></i> Add Parcel</a>
        <a class="exportBtn" target="_blank" href="export_parcels.php" title="Not Yet Working"><i class="fa-solid fa-file-export"></i> Export</a>
    </div>
    
    <table class="blockTable">
    <thead>
        <tr>
            <th>Parcel Nickname</th>
            <th>Address</th>
            <th># Blocks</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($parcel = $result_parcels->fetch_assoc()) : ?>
            <?php
            // Fetch block counts for the given parcel_id
            $parcel_id = $parcel['parcel_id'];
            $sql_blocks = "SELECT 
                                COUNT(*) AS total_blocks, 
                                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_blocks,
                                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) AS inactive_blocks
                           FROM Blocks 
                           WHERE parcel_id = ?";

            $stmt_blocks = $conn->prepare($sql_blocks);
            $stmt_blocks->bind_param("i", $parcel_id);
            $stmt_blocks->execute();
            $result_blocks = $stmt_blocks->get_result();
            $block_counts = $result_blocks->fetch_assoc();
            ?>

            <tr>
                <td>
                    <a class="underline" href="parcel_detail?parcel_id=<?= $parcel['parcel_id']; ?>" title="View Parcel">
                        <?= htmlspecialchars($parcel['nickname']); ?>
                    </a> 
                    &nbsp; 
                </td>
                <td><?= htmlspecialchars($parcel['street_address'] . ', ' . $parcel['city'] . ', ' . $parcel['state'] . ' ' . $parcel['zip']); ?></td>
                <td>
                    <a class="underline" href="blocks?parcel_id=<?= $parcel['parcel_id']; ?>" title="View Blocks">
                        <?= $block_counts['active_blocks'] ?? 0; ?> active, <?= $block_counts['inactive_blocks'] ?? 0; ?> inactive
                    </a>
                </td>
                <td class="actions">
                    <a class="addEditLink" href="parcel_edit?parcel_id=<?= $parcel['parcel_id']; ?>" title="Edit Parcel">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    
                    <?php
                        $isActive = ($parcel['status'] == 'active');
                        $toggleStatus = $isActive ? "on" : "off";
                        $actionType = $isActive ? "deactivateParcel" : "activateParcel";
                        $titleText = $isActive ? "Deactivate" : "Activate";
                    ?>

                    <a href="javascript:void(0);" 
                       class="toggleParcelStatus"
                       data-action="<?= $actionType; ?>"
                       data-parcel-id="<?= $parcel['parcel_id']; ?>"
                       data-parcel-name="<?= $parcel['nickname']; ?>"
                       title="<?= $titleText; ?> Parcel">
                        <i class="fa-solid fa-toggle-<?= $toggleStatus; ?>"></i>
                    </a>
                </td>
            </tr>

        <?php endwhile; ?>
        </tbody>
    </table>

</section>


<script>
$(document).ready(function () {
    let formToSubmit = null; // Store form reference for final submission

    $(".toggleParcelStatus").on("click", function () {
        let parcelId = $(this).data("parcel-id");
        let parcelName = $(this).data("parcel-name");
        let actionType = $(this).data("action"); // "activateParcel" or "deactivateParcel"

        // Update modal text and input values
        $("#parcelNickname").text(parcelName);
        $("#modalParcelId").val(parcelId);
        $("#modalParcelAction").val(actionType);
        $("#parcelActionType").text(actionType === "deactivateParcel" ? "deactivate" : "activate");

        if (actionType === "deactivateParcel") {
            $("#parcelModalTitle").text("Confirm Deactivation");
            $("#parcelModalMessage").html(`Are you sure you want to <strong>deactivate</strong> parcel "<strong>${parcelName}</strong>"?`);
            $("#parcelDeactivateNotesField").show();
            $("#confirmParcelAction").text("Deactivate").removeClass("btn-success").addClass("btn-danger");

            // Show first confirmation modal
            $("#parcelActionModal").fadeIn().css("display", "flex");
        } else {
            $("#parcelModalTitle").text("Confirm Activation");
            $("#parcelModalMessage").html(`Are you sure you want to <strong>activate</strong> parcel "<strong>${parcelName}</strong>"?`);
            $("#parcelDeactivateNotesField").hide();
            $("#confirmParcelAction").text("Activate").removeClass("btn-danger").addClass("btn-success");

            // Show first confirmation modal
            $("#parcelActionModal").fadeIn().css("display", "flex");
        }
    });

    // Step 2: Show secondary confirmation modal ONLY for deactivation
    $("#confirmParcelAction").on("click", function (e) {
        e.preventDefault();
        let actionType = $("#modalParcelAction").val();

        if (actionType === "deactivateParcel") {
            $("#parcelActionModal").fadeOut();
            $("#customConfirmModal").fadeIn().css("display", "flex");
            formToSubmit = $("#parcelActionModal form"); // Store form for later
        } else {
            $("#parcelActionModal form").submit(); // Direct submit for activation
        }
    });

    // Step 3: Final confirmation for deactivation
    $("#confirmYes").on("click", function () {
        if (formToSubmit) {
            formToSubmit.submit();
        }
        $("#customConfirmModal").fadeOut();
    });

    // Cancel deactivation or activation (Close Modals)
    $("#cancelParcelAction, #confirmNo").on("click", function (e) {
        e.preventDefault();
        $("#parcelActionModal, #customConfirmModal").fadeOut();
    });

    // Close modal if user clicks outside of it
    $(".modal").on("click", function (e) {
        if ($(e.target).is(".modal")) {
            $(this).fadeOut();
        }
    });
});

</script>
    


