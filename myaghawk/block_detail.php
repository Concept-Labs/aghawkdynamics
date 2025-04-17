<?php
// BLOCK DETAIL

// Get account ID from the URL
if (!isset($_SESSION['account_id']) || !isset($_GET['block_id'])) {
    die('Account ID and Block ID are required.');
}
$account_id = intval($_SESSION['account_id']);
$block_id = intval($_GET['block_id']);


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action']=='editBlock') {
    update_block_details($block_id, $_POST);
}


// DEACTIVATE BLOCK
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'deactivateBlock' && isset($_POST['block_id'])) {
    $block_id = intval($_POST['block_id']); 
    $deactivate_notes = isset($_POST['deactivateNotes']) ? trim($_POST['deactivateNotes']) : '';
    deactivate_block($block_id, $account_id, $deactivate_notes);
}


// Fetch block details, including associated parcel details
$sql_block = "SELECT Blocks.*, Parcels.nickname AS parcel_nickname, Parcels.parcel_id
              FROM Blocks 
              INNER JOIN Parcels ON Blocks.parcel_id = Parcels.parcel_id
              WHERE Blocks.block_id = ? AND Parcels.account_id = ?";
$stmt_block = $conn->prepare($sql_block);
$stmt_block->bind_param("ii", $block_id, $account_id);
$stmt_block->execute();
$result_block = $stmt_block->get_result();
if ($result_block->num_rows == 0) {
    die('Block not found.');
}
$block = $result_block->fetch_assoc();


// Fetch attachments for this block
$sql_attachments = "SELECT attachment_id, file_path, comments FROM BlockAttachments WHERE block_id = ?";
$stmt_attachments = $conn->prepare($sql_attachments);
$stmt_attachments->bind_param("i", $block_id);
$stmt_attachments->execute();
$result_attachments = $stmt_attachments->get_result();

// Store attachments in an array
$attachments = [];
while ($row = $result_attachments->fetch_assoc()) {
    $attachments[] = $row;
}

// Close statements
$stmt_block->close();
$stmt_attachments->close();
$conn->close();


// Function to determine file type
function getFileType($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    if (in_array($extension, $imageExtensions)) {
        return 'image';
    }

    $icons = [
        'pdf'  => 'fa-file-pdf',    // FontAwesome class for PDF
        'doc'  => 'fa-file-word',
        'docx' => 'fa-file-word',
        'xls'  => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'ppt'  => 'fa-file-powerpoint',
        'pptx' => 'fa-file-powerpoint',
        'txt'  => 'fa-file-alt',
        'zip'  => 'fa-file-archive',
        'rar'  => 'fa-file-archive',
        'mp4'  => 'fa-file-video',
        'avi'  => 'fa-file-video',
        'mov'  => 'fa-file-video'
    ];

    return $icons[$extension] ?? 'fa-file'; // Default file icon
}
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

<h3>Block: <?php echo htmlspecialchars($block['nickname']); ?></h3>

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
    
    <p><strong>Parent Parcel:</strong> <a href="parcel_detail?parcel_id=<?php echo $block['parcel_id']; ?>">
        <?php echo htmlspecialchars($block['parcel_nickname']); ?>
    </a></p>
    
    <!--
    <div class="pageHdrActions">
        <a class="addEditLink" href="block_edit?parcel_id=<?php echo $block['parcel_id']; ?>&block_id=<?php echo $block['block_id']; ?>">
            <i class="fa-solid fa-pen-to-square"></i> Edit Block
        </a>
        <a href="javascript:void(0);" class="deactivateBlock" data-block-id="<?php echo $block['block_id']; ?>" data-block-name="<?php echo htmlspecialchars($block['nickname']); ?>">
            <i class="fa-solid fa-toggle-off"></i> Deactivate Block
        </a>
    </div>
    <!--//-->

    <div class="parcelBlocks">
        <table class="blockTable">
            <thead>
                <tr>
                    <th>Block Nickname</th>
                    <th>Acres</th>
                    <th>Usage Type</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($block['nickname']); ?></td>
                    <td><?php echo htmlspecialchars($block['acres']); ?></td>
                    <td><?php echo htmlspecialchars($block['crop_category']); ?></td>
                    <td><?php echo htmlspecialchars($block['notes']); ?></td>
                    <td class="actions">
                        <a class="addEditLink" href="block_edit?parcel_id=<?php echo $block['parcel_id']; ?>&block_id=<?= $block['block_id']; ?>" title="Edit Block">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>                        
                        <a class="iconDroneSVG" href="service_request_add?parcel_id=<?php echo $block['parcel_id']; ?>&block_id=<?= $block['block_id']; ?>" title="Activity">
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
            </tbody>
        </table>
    </div>
</section>


<?php if (!empty($attachments)) : ?>

    <hr style="margin:60px 0;" />

    <h3>Block Attachments</h3>
    <div class="attachment-container">
        <?php foreach ($attachments as $attachment) : ?>
        
            <div class="attachment">
                <?php 
                    $attachment_url = "_serve_attachment.php?path=" . urlencode($attachment['file_path']);
                    $fileType = getFileType($attachment['file_path']);
                ?>

                <?php if ($fileType === 'image') : ?>
                    <!-- Image files open in Lightbox -->
                    <a href="<?= $attachment_url; ?>" rel="lightbox[gallery]" title="<?= htmlspecialchars($attachment['comments'] ?: 'Attachment'); ?>">
                        <img src="<?= $attachment_url; ?>" alt="Attachment" class="attachment-thumb">
                    </a>
                <?php else : ?>
                    <!-- Non-image files open in a new tab -->
                    <a href="<?= $attachment_url; ?>" target="_blank" class="file-icon">
                        <i class="fa-solid <?= $fileType; ?> fa-2x"></i>
                        <span><?= basename($attachment['file_path']); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<script>
    
//Lightbox2 Initialization
document.addEventListener("DOMContentLoaded", function() {
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true
    });
});
   
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
