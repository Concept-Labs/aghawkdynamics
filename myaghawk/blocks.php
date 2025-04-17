<?php // BLOCKS

// Get account ID from the URL
if (!isset($_SESSION['account_id'])) {
    die('Account ID is required.');
}
$account_id = intval($_SESSION['account_id']);
$parcel_id = isset($_GET['parcel_id']) ? intval($_GET['parcel_id']) : null;

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
    $block_id = intval($_POST['block_id']);
    $deactivate_notes = isset($_POST['deactivateNotes']) ? trim($_POST['deactivateNotes']) : '';
    deactivate_block($block_id, $account_id, $deactivate_notes);
}
// ACTIVATE BLOCK
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'activateBlock' && isset($_POST['block_id'])) {
    $block_id = intval($_POST['block_id']);
    activate_block($block_id, $account_id);
}

// Fetch parcels associated with the account
$sql_parcels = "SELECT * FROM Parcels WHERE account_id = ?";
$stmt_parcels = $conn->prepare($sql_parcels);
$stmt_parcels->bind_param("i", $account_id);
$stmt_parcels->execute();
$result_parcels = $stmt_parcels->get_result();

// Fetch blocks based on parcel_id if provided, otherwise fetch all blocks
if ($parcel_id) {
    $sql_blocks = "SELECT Blocks.*, 
                          Parcels.nickname AS parcel_nickname, 
                          Parcels.status AS parcel_status 
                   FROM Blocks 
                   INNER JOIN Parcels ON Blocks.parcel_id = Parcels.parcel_id 
                   WHERE Blocks.parcel_id = ? AND Parcels.account_id = ?";
    $stmt_blocks = $conn->prepare($sql_blocks);
    $stmt_blocks->bind_param("ii", $parcel_id, $account_id);
} else {
    $sql_blocks = "SELECT Blocks.*, 
                          Parcels.nickname AS parcel_nickname, 
                          Parcels.status AS parcel_status 
                   FROM Blocks 
                   INNER JOIN Parcels ON Blocks.parcel_id = Parcels.parcel_id 
                   WHERE Parcels.account_id = ?";
    $stmt_blocks = $conn->prepare($sql_blocks);
    $stmt_blocks->bind_param("i", $account_id);
}

$stmt_blocks->execute();
$result_blocks = $stmt_blocks->get_result();

?>

<?php 
if (isset($_SESSION['displayMsg'])) { 
?>
    <div class="displayMsg"><?php echo $_SESSION['displayMsg']; ?></div>
    <script>
        $(document).ready(function() {								
            if ($('.displayMsg').length) {									
                $('.displayMsg').delay(6000).slideUp(500, function() {
                    $(this).remove();
                });
            }
        });
    </script>
<?php
    unset($_SESSION['displayMsg']);
}
?>

<h3>Blocks</h3>
<p><hr /></p>

<div id="blockActionModal" class="modal">
    <div class="modal-content">
        <h4 id="blockModalTitle">Confirm Action</h4>
        <br />
        <p id="blockModalMessage">
            Are you sure you want to <strong><span id="blockActionType"></span></strong> block "<strong><span id="blockNickname"></span></strong>"?
        </p>
        <form action="blocks" method="post">
            <input type="hidden" name="action" value="" id="modalBlockAction" />
            <input type="hidden" name="block_id" value="" id="modalBlockId" />
            <textarea name="deactivateNotes" id="blockDeactivateNotesField" placeholder="Reason for deactivation?"></textarea>
            <div class="modal-buttons">
                <button id="confirmBlockAction" class="confirm-btn"></button>
                <button id="cancelBlockAction" class="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div id="customConfirmModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h4>Are you sure?</h4>
        <br />
        <p>The cancellation of any pending service requests as a result of deactivating this block can't be reversed.</p>
        <button id="confirmYes" class="btn btn-danger">Yes, I'm sure.</button>
        <button id="confirmNo" class="btn btn-secondary">Nevermind</button>
    </div>
</div>

<section class="userSection Blocks">
    
    <div class="pageHdrActions">
        <a class="addEditLink" href="block_add"><i class="fa-solid fa-square-plus"></i> Add Block</a>
        <a class="exportBtn" target="_blank" href="export_blocks.php"><i class="fa-solid fa-file-export"></i> Export</a>
    </div>
    
    <div class="parcelBlocks">
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
                <?php if ($result_blocks->num_rows > 0): ?>
                    <?php while ($block = $result_blocks->fetch_assoc()) : ?>
                <tr>
                    <td>
                        <a class="underline" href="parcel_detail?parcel_id=<?= $block['parcel_id']; ?>">
                            <?= htmlspecialchars($block['parcel_nickname']); ?>
                        </a>
                    </td>
                    <td>
                        <a class="underline" href="block_detail?block_id=<?= $block['block_id']; ?>">
                            <?= htmlspecialchars($block['nickname']); ?>
                        </a>
                    </td>
                    <td>
                        <?= (!empty($block['acres']) && $block['acres'] != 0) ? htmlspecialchars($block['acres']) : 'TBD'; ?>
                    </td>

                    
                    <td><?= htmlspecialchars($block['crop_category']); ?></td>
                    <td class="actions">
                        <a class="addEditLink" href="block_edit?block_id=<?= $block['block_id']; ?>" title="Edit Block">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="iconDroneSVG" href="service_request_add?parcel_id=<?= $block['parcel_id']; ?>&block_id=<?= $block['block_id']; ?>" title="Activity">
                            <?= file_get_contents('images/iconDrone.svg'); ?>
                        </a>
                        
                        <a class="addEditLink" href="self_tracking?parcel_id=<?= $block['parcel_id']; ?>&block_id=<?= $block['block_id']; ?>" title="Self-Tracking (coming soon)">
                            <i class="fa-solid fa-list-check"></i>
                        </a>
                        <?php
                            $isBlockActive = ($block['status'] == 'active');
                            $isParcelActive = ($block['parcel_status'] == 'active'); // Ensure this is fetched in the query

                            $toggleStatus = $isBlockActive ? "on" : "off";
                            $actionType = $isBlockActive ? "deactivateBlock" : "activateBlock";

                            // Default tooltip text
                            $titleText = $isBlockActive ? "Deactivate" : "Activate";

                            // If the parcel is inactive and the block is inactive, prevent activation & show warning message
                            if (!$isParcelActive && !$isBlockActive) {
                                $titleText = "Block cannot be activated because its parent parcel is inactive.";
                                $disabledClass = "no-click disabled-block";  // New class to prevent interaction
                                $disabledStyle = 'opacity: 0.5;';
                            } else {
                                $disabledClass = "";
                                $disabledStyle = "";
                            }
                        ?>

                        <a href="javascript:void(0);" 
                           class="toggleBlockStatus <?= $disabledClass; ?>"
                           data-action="<?= $actionType; ?>"
                           data-block-id="<?= $block['block_id']; ?>"
                           data-block-name="<?= $block['nickname']; ?>"
                           title="<?= $titleText; ?>"
                           style="<?= $disabledStyle; ?>">
                            <i class="fa-solid fa-toggle-<?= $toggleStatus; ?>"></i>
                        </a>
                    </td>
                </tr>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No blocks found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
    
$(document).ready(function () {
    let formToSubmit = null; // Store form reference for final submission

    // Block clicks on disabled toggles (no modal should open)
    $(".no-click").on("click", function (e) {
        e.preventDefault();  // Prevent default link behavior
        return false;        // Ensure no further event execution
    });

    $(".toggleBlockStatus").on("click", function () {
        // If this element has the "no-click" class, do nothing
        if ($(this).hasClass("no-click")) {
            return false; // Stops any further execution
        }

        let blockId = $(this).data("block-id");
        let blockName = $(this).data("block-name");
        let actionType = $(this).data("action"); // "activateBlock" or "deactivateBlock"

        // Update modal text and input values
        $("#blockNickname").text(blockName);
        $("#modalBlockId").val(blockId);
        $("#modalBlockAction").val(actionType);

        if (actionType === "deactivateBlock") {
            $("#blockModalTitle").text("Confirm Deactivation");
            $("#blockModalMessage").html(`Are you sure you want to <strong>deactivate</strong> block "<strong>${blockName}</strong>"?`);
            $("#blockDeactivateNotesField").show();
            $("#confirmBlockAction").text("Deactivate").removeClass("btn-success").addClass("btn-danger");
        } else {
            $("#blockModalTitle").text("Confirm Activation");
            $("#blockModalMessage").html(`Are you sure you want to <strong>activate</strong> block "<strong>${blockName}</strong>"?`);
            $("#blockDeactivateNotesField").hide();
            $("#confirmBlockAction").text("Activate").removeClass("btn-danger").addClass("btn-success");
        }

        // Show primary action modal first
        $("#blockActionModal").fadeIn().css("display", "flex");
    });

    // Step 2: Show secondary confirmation modal ONLY for deactivation
    $("#confirmBlockAction").on("click", function (e) {
        e.preventDefault();
        let actionType = $("#modalBlockAction").val();

        if (actionType === "deactivateBlock") {
            $("#customConfirmModal").fadeIn().css("display", "flex");
            formToSubmit = $("#blockActionModal form"); // Store form for later
        } else {
            $("#blockActionModal form").submit(); // Direct submit for activation
        }
    });

    // Step 3: Final confirmation for deactivation
    $("#confirmYes").on("click", function () {
        if (formToSubmit) {
            formToSubmit.submit();
        }
        $("#customConfirmModal").fadeOut();
    });

    // Cancel any action (Close Modal)
    $("#cancelBlockAction, #confirmNo").on("click", function (e) {
        e.preventDefault();
        $("#blockActionModal, #customConfirmModal").fadeOut();
    });

    // Close modal if user clicks outside of it
    $(".modal").on("click", function (e) {
        if ($(e.target).is(".modal")) {
            $(this).fadeOut();
        }
    });
});

</script>
