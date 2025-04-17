<?php // PARCELS V2

// Get account ID from the URL
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}
$account_id = intval($_SESSION['account_id']);
$parcel_id = intval($_GET['parcel_id']);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action']=='editParcel') {
    update_parcel_details($parcel_id, $account_id, $_POST);
}


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


// DEACTIVATE BLOCK
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deactivateBlock' && isset($_POST['block_id'])) {
    deactivate_block($block_id, $_POST);
}

// Fetch parcel details
$sql_parcel = "SELECT * FROM Parcels WHERE parcel_id = ? AND  account_id = ?";
$stmt_parcel = $conn->prepare($sql_parcel);
$stmt_parcel->bind_param("ii", $parcel_id, $account_id);
$stmt_parcel->execute();
$result_parcel = $stmt_parcel->get_result();
if ($result_parcel->num_rows == 0) {
    die('Parcel not found.');
}
$parcel = $result_parcel->fetch_assoc();
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


<h3>Parcel <? echo $parcel['nickname']; ?></h3>

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
    
    <p><?= htmlspecialchars($parcel['street_address'] . ', ' . $parcel['city'] . ', ' . $parcel['state'] . ' ' . $parcel['zip']); ?></p>
    
    <div class="pageHdrActions">
        <a class="addEditLink" href="block_add?parcel_id=<?= $parcel['parcel_id']; ?>"><i class="fa-solid fa-square-plus"></i> Add Block</a>
        <a class="exportBtn" href="javascript:void(0);" title="Not Yet Working"><i class="fa-solid fa-file-export"></i> Export</a>
    </div>

    <div class="parcelBlocks" id="parcel_<?= $parcel_id; ?>" >
        <table class="blockTable">
    <thead>
        <tr>
            <th>Parcel Nickname</th>
            <th>Block Nickname</th>
            <th>Acres</th>
            <th>Usage Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

        <?php
        // Fetch blocks associated with this single parcel
        $sql_blocks = "SELECT * FROM Blocks WHERE parcel_id = ?";
        $stmt_blocks = $conn->prepare($sql_blocks);
        $stmt_blocks->bind_param("i", $parcel_id);
        $stmt_blocks->execute();
        $result_blocks = $stmt_blocks->get_result();

        if ($result_blocks->num_rows > 0) {
            while ($block = $result_blocks->fetch_assoc()) :
        ?>
                <tr>
                    <td><?= htmlspecialchars($parcel['nickname']); ?>
                    </td>
                    <td>
                        <a class="underline" href="block_detail?block_id=<?= $block['block_id']; ?>&parcel_id=<?= $parcel_id; ?>" title="View Block">
                            <?= htmlspecialchars($block['nickname']); ?> 
                        </a>
                    </td>
                    <td><?= htmlspecialchars($block['acres']); ?></td>
                    <td><?= htmlspecialchars($block['crop_category']); ?></td>
                    <td class="actions">
                        <a class="addEditLink" href="parcel_edit?parcel_id=<?= $parcel['parcel_id']; ?>" title="Edit Parcel">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>                        
                        <a class="iconDroneSVG" href="service_request_add?parcel_id=<?= $parcel['parcel_id']; ?>&block_id=<?= $block['block_id']; ?>" title="Activity">
                            <?= file_get_contents('images/iconDrone.svg'); ?>
                        </a>
                        
                        <a class="addEditLink" href="self_tracking" title="Self-Tracking (coming soon)">
                            <i class="fa-solid fa-list-check"></i>
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
        <?php
            endwhile;
        } else {
            echo '<tr><td colspan="5">No blocks found for this parcel.</td></tr>';
        }
        ?>
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

    


