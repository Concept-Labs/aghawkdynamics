<?php

// Get block ID, parcel ID, and account ID from the URL
if (!isset($_GET['block_id']) || !isset($_GET['parcel_id']) || !isset($_GET['account_id'])) {
    die('Block ID, Parcel ID, and Account ID are required.');
}
$block_id = intval($_GET['block_id']);
$parcel_id = intval($_GET['parcel_id']);
$account_id = intval($_GET['account_id']);

// Fetch block details
$sql_block = "SELECT * FROM Blocks WHERE block_id = ?";
$stmt_block = $conn->prepare($sql_block);
$stmt_block->bind_param("i", $block_id);
$stmt_block->execute();
$result_block = $stmt_block->get_result();

if ($result_block->num_rows == 0) {
    die('Block not found.');
}
$block = $result_block->fetch_assoc();

// Fetch parcel details
$sql_parcel = "SELECT * FROM Parcels WHERE parcel_id = ?";
$stmt_parcel = $conn->prepare($sql_parcel);
$stmt_parcel->bind_param("i", $parcel_id);
$stmt_parcel->execute();
$result_parcel = $stmt_parcel->get_result();

if ($result_parcel->num_rows == 0) {
    die('Parcel not found.');
}
$parcel = $result_parcel->fetch_assoc();
$parcelNickname = $parcel['nickname'];

// Fetch account details
$sql_account = "SELECT * FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($sql_account);
$stmt_account->bind_param("i", $account_id);
$stmt_account->execute();
$result_account = $stmt_account->get_result();

if ($result_account->num_rows == 0) {
    die('Account not found.');
}
$account = $result_account->fetch_assoc();
$business_name = $account['business_name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    update_block_details($block_id, $_POST);
}
?>

<section class="adminSection">
    <h3>Edit Block</h3>
    <form method="POST">
        <table>
            <tr>
                <td><label><strong>Customer Account:</strong></label></td>
                <td><?php echo htmlspecialchars($business_name); ?></td>
            </tr>
            <tr>
                <td><label><strong>Parcel Nickname:</strong></label></td>
                <td><?php echo htmlspecialchars($parcelNickname); ?></td>
            </tr>
            <tr>
                <td><label><strong>Block Nickname:</strong></label></td>
                <td><input type="text" name="nickname" value="<?php echo htmlspecialchars($block['nickname']); ?>" required></td>
            </tr>
            <tr>
                <td><label><strong>Acres:</strong></label></td>
                <td><input type="number" name="acres" step="0.01" value="<?php echo htmlspecialchars($block['acres']); ?>" required></td>
            </tr>
            <tr>
                <td><label><strong>Latitude:</strong></label></td>
                <td><input type="text" name="latitude" value="<?php echo htmlspecialchars($block['latitude']); ?>" required></td>
            </tr>
            <tr>
                <td><label><strong>Longitude:</strong></label></td>
                <td><input type="text" name="longitude" value="<?php echo htmlspecialchars($block['longitude']); ?>" required></td>
            </tr>
            <tr>
                <td><label><strong>Usage Type:</strong></label></td>
                <td>
                    <select name="crop_category" required>
                        <option value="">Select Usage Type</option>
                        <option value="Orchard" <?php if ($block['crop_category'] == 'Orchard') echo 'selected'; ?>>Orchard</option>
                        <option value="Vineyard" <?php if ($block['crop_category'] == 'Vineyard') echo 'selected'; ?>>Vineyard</option>
                        <option value="Row Crops" <?php if ($block['crop_category'] == 'Row Crops') echo 'selected'; ?>>Row Crops</option>
                        <option value="Pasture" <?php if ($block['crop_category'] == 'Pasture') echo 'selected'; ?>>Pasture</option>
                        <option value="Grass field" <?php if ($block['crop_category'] == 'Grass field') echo 'selected'; ?>>Grass field</option>
                        <option value="Mix" <?php if ($block['crop_category'] == 'Mix') echo 'selected'; ?>>Mix</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><strong>Notes:</strong></label></td>
                <td><textarea name="notes" rows="4"><?php echo htmlspecialchars($block['notes']); ?></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <button type="submit">Save Changes</button>
                    <a href="?view=account_view&id=<?php echo $account_id; ?>">Cancel</a>
                </td>
            </tr>
        </table>
    </form>
</section>


<section class="adminSection">
    <h4>Upload Attachment for this Block</h4>
    
    <form id="uploadForm">
		<input type="hidden" name="type" id="type" value="block" />
		<input type="hidden" name="block_id" id="block_id" value="<? echo $block_id; ?>" />
		<input type="hidden" name="account_id" id="account_id" value="<? echo $account_id; ?>" />
		
		<p><label for="file">Select File:</label>
        <input type="file" name="file" id="file" required></p>

        <p><input type="text" name="comments" id="comments" placeholder="Attachment comments (optional)" style="width:500px;" /></p>

        <p><button type="submit" id="uploadSubmit">Upload File</button></p>
    </form>

    <div id="response"></div>
</section>



<section class="adminSection">
    <h4>Uploaded Attachments</h4>
	
	<div class="attachment-container" id="attachmentContainer">
		<!-- Dynamic content will be loaded here -->
	</div>
	
	<!-- Edit Comments Modal -->
	<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; box-shadow:0px 0px 15px rgba(0,0,0,0.5);">
		<h3>Edit Comment</h3>
		<textarea id="editComment" rows="4" cols="40"></textarea><br>
		<button onclick="saveComment()">Save</button>
		<button onclick="closeEditModal()">Cancel</button>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			loadAttachments();
		});

		function loadAttachments() {
			fetch('_attachments.php?action=load&block_id=<?php echo $block_id; ?>')
				.then(response => response.json())
				.then(attachments => {
					const container = document.getElementById('attachmentContainer');
					container.innerHTML = '';

					attachments.forEach(attachment => {
						const div = document.createElement('div');
						div.className = 'attachment';

						// Create the link element for lightbox
						const link = document.createElement('a');
						link.href = '_serve_attachment.php?path=' + encodeURIComponent(attachment.file_path);
						link.setAttribute('data-lightbox', 'attachments');
						link.setAttribute('data-title', attachment.comments);

						// Create the thumbnail image element or use a PDF icon if file type is pdf
						const thumbnail = document.createElement('img');
						if (attachment.file_path.endsWith('.pdf')) {
							thumbnail.src = 'images/pdf-icon.png'; // Replace with the actual path to a PDF icon
						} else {
							thumbnail.src = '_serve_attachment.php?path=' + encodeURIComponent(attachment.file_path);
						}
						thumbnail.className = 'thumbnail';
						thumbnail.alt = 'Attachment Thumbnail';

						// Append thumbnail to link
						link.appendChild(thumbnail);
						
						// Create comment div
						const commentDiv = document.createElement('div');
						commentDiv.className = 'comment';
						commentDiv.innerText = attachment.comments;

						const deleteButton = document.createElement('button');
						deleteButton.className = 'delete-button';
						deleteButton.innerText = 'Delete';
						deleteButton.onclick = function () {
							deleteAttachment(attachment.id, attachment.type);
						};

						const editButton = document.createElement('button');
						editButton.className = 'edit-button';
						editButton.innerText = 'Edit';
						editButton.onclick = function () {
							openEditModal(attachment.id, attachment.type, attachment.comments);
						};

						div.appendChild(link);
						div.appendChild(commentDiv);
						div.appendChild(deleteButton);
						div.appendChild(editButton);
						container.appendChild(div);
					});
				})
				.catch(error => console.error('Error loading attachments:', error));
		}

		function deleteAttachment(id, type) {
			if (confirm('Are you sure you want to delete this attachment? This action cannot be undone.')) {
				fetch('_attachments.php?action=delete&id='+id+'&type=block')
					.then(response => response.text())
					.then(result => {
						alert(result);
						loadAttachments();
					})
					.catch(error => console.error('Error deleting attachment:', error));
			}
		}

		function openEditModal(id, type, currentComment) {
			document.getElementById('editModal').style.display = 'block';
			document.getElementById('editComment').value = currentComment;
			document.getElementById('editModal').dataset.attachmentId = id;
			document.getElementById('editModal').dataset.attachmentType = type;
		}

		function closeEditModal() {
			document.getElementById('editModal').style.display = 'none';
		}

		function saveComment() {
			const id = document.getElementById('editModal').dataset.attachmentId;
			const newComment = document.getElementById('editComment').value;

			fetch('_attachments.php?action=edit&type=block', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ id: id, comment: newComment })
			})
			.then(response => {
				if (response.ok) {
					return response.text();
				} else {
					throw new Error('Failed to update comment');
				}
			})
			.then(result => {
				console.log(result); // Logging instead of alerting
				closeEditModal();
				loadAttachments();
			})
			.catch(error => {
				console.error('Error saving comment:', error);
				alert('There was an error updating the comment. Please try again.');
			});
		}

	</script>
	
</section>



<?php
$stmt_block->close();
$stmt_parcel->close();
$stmt_account->close();
$conn->close(); // Close the database connection
?>
