<?php // MY.AGHAWK

// Get service request ID from the URL
$service_request_id = isset($_GET['service_request_id']) ? intval($_GET['service_request_id']) : null;

if (!$service_request_id) {
    die("Error: Service request ID is missing.");
}

// Fetch service request details
$sql_service_request = "SELECT * FROM ServiceRequests WHERE service_request_id = ?";
$stmt_service_request = $conn->prepare($sql_service_request);
$stmt_service_request->bind_param("i", $service_request_id);
$stmt_service_request->execute();
$result_service_request = $stmt_service_request->get_result();

if ($result_service_request->num_rows == 0) {
    die("Error: Service request not found.");
}

$service_request = $result_service_request->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Call function to update service request
    update_service_request($service_request_id, $_POST);
}


// Fetch account details
$account_sql = "SELECT business_name FROM Accounts WHERE account_id = ?";
$stmt_account = $conn->prepare($account_sql);
$stmt_account->bind_param("i", $service_request['account_id']);
$stmt_account->execute();
$account_result = $stmt_account->get_result();
$account = $account_result->fetch_assoc();

// Fetch parcel details
$parcel_sql = "SELECT nickname FROM Parcels WHERE parcel_id = ?";
$stmt_parcel = $conn->prepare($parcel_sql);
$stmt_parcel->bind_param("i", $service_request['parcel_id']);
$stmt_parcel->execute();
$parcel_result = $stmt_parcel->get_result();
$parcel = $parcel_result->fetch_assoc();

// Fetch block details
$block_sql = "SELECT nickname FROM Blocks WHERE block_id = ?";
$stmt_block = $conn->prepare($block_sql);
$stmt_block->bind_param("i", $service_request['block_id']);
$stmt_block->execute();
$block_result = $stmt_block->get_result();
$block = $block_result->fetch_assoc();

?>


<section class="userSection">
    <h3>Edit Service Request</h3>
        
	<div class="serviceRequestEditInfo">
		<em>Parcel:</em> <strong><? echo htmlspecialchars($parcel['nickname']); ?></strong> &nbsp; | &nbsp; 
		<em>Block:</em> <strong><? echo htmlspecialchars($block['nickname']); ?></strong>
	</div>

    <form method="POST">
        <div class="labelAndInputWrap">
            <label><strong>Reason for Application:</strong></label>
            <input type="text" name="reason_for_application" value="<?php echo htmlspecialchars($service_request['reason_for_application']); ?>" required>
        </div>

        <div class="labelAndInputWrap">
            <label><strong>Type of Service:</strong></label>
            <select name="type_of_service" required>
                <option value="">Select Service Type</option>
                <option value="Spray" <?php echo ($service_request['type_of_service'] == 'Spray') ? "selected" : ""; ?>>Spray</option>
                <option value="Spread" <?php echo ($service_request['type_of_service'] == 'Spread') ? "selected" : ""; ?>>Spread</option>
                <option value="Analyze" <?php echo ($service_request['type_of_service'] == 'Analyze') ? "selected" : ""; ?>>Analyze</option>
                <option value="Drying" <?php echo ($service_request['type_of_service'] == 'Drying') ? "selected" : ""; ?>>Drying</option>
            </select>                
        </div>

        <div class="labelAndInputWrap">
            <label><strong>Type of Product:</strong></label>
            <select name="type_of_product" id="type_of_product" required>
                <option value="">Select Product Type</option>
                <option value="Pesticide" <?php echo ($service_request['type_of_product'] == 'Pesticide') ? "selected" : ""; ?>>Pesticide</option>
                <option value="Herbicide" <?php echo ($service_request['type_of_product'] == 'Herbicide') ? "selected" : ""; ?>>Herbicide</option>
                <option value="Fungicide" <?php echo ($service_request['type_of_product'] == 'Fungicide') ? "selected" : ""; ?>>Fungicide</option>
                <option value="Chemical Thinner" <?php echo ($service_request['type_of_product'] == 'Chemical Thinner') ? "selected" : ""; ?>>Chemical Thinner</option>
                <option value="Nutrient" <?php echo ($service_request['type_of_product'] == 'Nutrient') ? "selected" : ""; ?>>Nutrient</option>
                <option value="Seed" <?php echo ($service_request['type_of_product'] == 'Seed') ? "selected" : ""; ?>>Seed</option>
                <option value="Fertilizer" <?php echo ($service_request['type_of_product'] == 'Fertilizer') ? "selected" : ""; ?>>Fertilizer</option>
                <option value="Rodent Control" <?php echo ($service_request['type_of_product'] == 'Rodent Control') ? "selected" : ""; ?>>Rodent Control</option>
                <option value="Other" <?php echo ($service_request['type_of_product'] == 'Other') ? "selected" : ""; ?>>Other</option>
            </select>                
        </div>

        <div class="labelAndInputWrap" id="type_of_product_other_row" style="display:none;">
            <label><strong>Other Type of Product:</strong></label>
            <input type="text" name="type_of_product_other" id="type_of_product_other" value="<?php echo htmlspecialchars($service_request['type_of_product_other']); ?>">
        </div>

        <div class="labelAndInputWrap">
            <label><strong>Product Name:</strong></label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($service_request['product_name']); ?>">
        </div>
        
        <div class="labelAndInputWrap">
            <label><strong>Supplier Name:</strong></label>
            <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($service_request['supplier_name']); ?>">
        </div>

        <div class="labelAndInputWrap">
            <label><strong>Supplier Contact:</strong></label>
            <input type="text" name="supplier_contact_phone" value="<?php echo htmlspecialchars($service_request['supplier_contact_phone']); ?>">
        </div>

        <div class="labelAndInputWrap">
            <label><strong>Application Need By Date:</strong></label>
            <input type="date" name="application_need_by_date" value="<?php echo htmlspecialchars($service_request['application_need_by_date']); ?>" required>
        </div>

        <div class="labelAndInputWrap">
            <label><strong>Scheduled Application Date:</strong></label>
            <input type="date" name="scheduled_date" value="<?php echo htmlspecialchars($service_request['scheduled_date']); ?>" required>
        </div>

        <br class="Clear" />

        <div class="labelAndInputWrap">
            <label><strong>Service Completed?</strong></label>
            <select name="status_completed" id="status_completed">
                <option value="">Not yet completed</option>
                <option value="1" <?php echo ($service_request['status_completed'] == '1') ? "selected" : ""; ?>>Completed</option>
            </select>

                <?	// Fetch service completion details
                    $sql_service_completion = "SELECT * FROM ServiceCompletions WHERE service_request_id = ?";
                    $stmt_service_completion = $conn->prepare($sql_service_completion);
                    $stmt_service_completion->bind_param("i", $service_request_id);
                    $stmt_service_completion->execute();
                    $result_service_completion = $stmt_service_completion->get_result();

                    if ($result_service_completion->num_rows > 0) {
                        $service_completion = $result_service_completion->fetch_assoc();
                        //echo "<pre>".print_r($service_completion)."</pre>\n";
                        $formattedDate = (new DateTime($service_completion['completion_date']))->format('Y-m-d'); //strip the time(hours) out
                    }					
                ?>

            <div id="serviceCompletionFields" style="display:none;">
                <label for="completion_date">Date Completed:</label>
                <input type="date" name="completion_date" id="completion_date" value="<?php echo htmlspecialchars($formattedDate); ?>" >
                <br />
                <label for="completed_by">Completed By:</label>
                <input type="text" name="completed_by" id="completed_by" value="<?php echo htmlspecialchars($service_completion['completed_by']); ?>" >
                <br />
                <label for="temperature">Temperature:</label>
                <input type="number" name="temperature" id="temperature" value="<?php echo htmlspecialchars($service_completion['temperature']); ?>" maxlength="3" > &deg;F
                <br />
                <label for="wind">Wind:</label>
                <input type="number" name="wind" id="wind" value="<?php echo htmlspecialchars($service_completion['wind']); ?>" maxlength="3" > MPH
                <br />
                <label for="restricted_exposure_hrs">Restricted Exposure Hours:</label>
                <input type="number" name="restricted_exposure_hrs" id="restricted_exposure_hrs" value="<?php echo htmlspecialchars($service_completion['restricted_exposure_hrs']); ?>" maxlength="3" > (per label)
                
                <br />
                
                <label for="volume">Volume:</label>
                <input type="text" name="volume" value="<?php echo htmlspecialchars($service_completion['volume']); ?>">
                <br />

                <label for="uom">Volume OUM:</label>
                <select name="uom" id="uom" required>
                    <option value="">Select Volume OUM</option>
                    <option value="Ounces" <?php echo ($service_completion['oum'] == 'Ounces') ? "selected" : ""; ?>>Ounces</option>
                    <option value="Pounds" <?php echo ($service_completion['oum'] == 'Pounds') ? "selected" : ""; ?>>Pounds</option>
                    <option value="Liters" <?php echo ($service_completion['oum'] == 'Liters') ? "selected" : ""; ?>>Liters</option>
                    <option value="Gallons" <?php echo ($service_completion['oum'] == 'Gallons') ? "selected" : ""; ?>>Gallons</option>
                    <option value="Other" <?php echo ($service_completion['oum'] == 'Other') ? "selected" : ""; ?>>Other</option>
                </select>
                <br />
                <label for="other_uom">Other UOM:</label>
                <input type="text" name="other_uom" value="<?php echo htmlspecialchars($service_completion['other_uom']); ?>">

            </div><!--//#serviceCompletionFields-->				
        </div>

        <br class="Clear" />

        <div class="labelAndInputWrap">
            <label><strong>Comments:</strong></label>
            <textarea name="comments"><?php echo htmlspecialchars($service_request['comments']); ?></textarea>
        </div>

        <br class="Clear" />

        <button type="submit">Update Service Request</button> &nbsp; 
        <a href="service_requests">Cancel</a>
            
    </form>
</section>

<script>	
$(document).ready(function () {
	
	$('#type_of_product').on('change', function () {
		if ($(this).val() === 'Other') {
			$('#type_of_product_other_row').show();
		} else {
			$('#type_of_product_other_row').hide();
			$('#type_of_product_other').val('');
		}
	});
    
    // Toggle #type_of_product_other visibility
    $('#type_of_product').on('change', function () {
        if ($(this).val() === 'Other') {
            $('#type_of_product_other').show().prop('required', true);
        } else {
            $('#type_of_product_other').hide().val('').prop('required', false);
        }
    });
    
	if($('#status_completed').val()==='1') {
	   $('#serviceCompletionFields').show();
	}
	
	$('#status_completed').on('change', function () {
		if ($(this).val() === '1') {
			$('#serviceCompletionFields').show();
		} else {
			$('#serviceCompletionFields').hide();
			$('#completed_by, #temperature, #wind, #restricted_exposure_hrs').val('');
		}
	});
	
});
</script>

    
    <hr style="margin:40px 0;" />


<section class="userSection">
    <h4>Upload Attachment for this Service Request</h4>
    
    <form id="uploadForm">
		<input type="hidden" name="type" id="type" value="service_request" />
		<input type="hidden" name="service_request_id" id="service_request_id" value="<? echo $service_request_id; ?>" />
		<input type="hidden" name="account_id" id="account_id" value="<? echo $service_request['account_id']; ?>" />
		
		<p><label for="file">Select File:</label>
        <input type="file" name="file" id="file" required></p>

        <p><input type="text" name="comments" id="comments" placeholder="Attachment comments (optional)" style="width:500px;" /></p>

        <p><button type="submit" id="uploadSubmit">Upload File</button></p>
    </form>

    <div id="response"></div>
</section>

<section class="userSection">
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
			fetch('_attachments.php?action=load&service_request_id=<?php echo $service_request_id; ?>')
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
				fetch('_attachments.php?action=delete&id='+id+'&type=service_request')
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
		}

		function closeEditModal() {
			document.getElementById('editModal').style.display = 'none';
		}

		function saveComment() {
			const id = document.getElementById('editModal').dataset.attachmentId;
			const newComment = document.getElementById('editComment').value;

			fetch('_attachments.php?action=edit&type=service_request', {
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
// Close statement and connection
$stmt_service_request->close();
$conn->close();
?>
