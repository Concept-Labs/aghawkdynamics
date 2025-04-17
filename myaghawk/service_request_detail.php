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


// Fetch service completion details
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


<section class="userSection">
    <h3>View Service Request</h3>
    
    <p><hr /></p>
    
    <table class="blockTable">
    <thead>
        <tr>
            <th align="left" colspan="2">Date Completed: <? echo htmlspecialchars($formattedDate); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="300">Parcel:</td>
            <td><? echo htmlspecialchars($parcel['nickname']); ?></td>
        </tr>
        <tr>
            <td>Block:</td>
            <td><? echo htmlspecialchars($block['nickname']); ?></td>
        </tr>
        <tr>
            <td>Reason for Application:</td>
            <td><? echo htmlspecialchars($service_request['reason_for_application']); ?></td>
        </tr>
        <tr>
            <td>Type of Service:</td>
            <td><? echo $service_request['type_of_service']; ?></td>
        </tr>
        <tr>
            <td>Type of Product:</td>
            <td><? echo $service_request['type_of_product']; ?></td>
        </tr>
        <tr>
            <td>Other Type of Product:</td>
            <td><? echo htmlspecialchars($service_request['type_of_product_other']); ?></td>
        </tr>
        <tr>
            <td>Product Name:</td>
            <td><? echo htmlspecialchars($service_request['product_name']); ?></td>
        </tr>
        <tr>
            <td>Supplier Name:</td>
            <td><? echo htmlspecialchars($service_request['supplier_name']); ?></td>
        </tr>
        <tr>
            <td>Supplier Contact:</td>
            <td><? echo htmlspecialchars($service_request['supplier_contact_phone']); ?></td>
        </tr>
        <tr>
            <td>Application Need By Date:</td>
            <td><? echo htmlspecialchars($service_request['application_need_by_date']); ?></td>
        </tr>
        <tr>
            <td>Scheduled Application Date:</td>
            <td><? echo htmlspecialchars($service_request['scheduled_date']); ?></td>
        </tr>
        <tr>
            <td>Completion Date:</td>
            <td><? echo htmlspecialchars($formattedDate); ?></td>
        </tr>
        <tr>
            <td>Completed By:</td>
            <td><? echo htmlspecialchars($service_completion['completed_by']); ?></td>
        </tr>
        <tr>
            <td>Temperature:</td>
            <td><? echo htmlspecialchars($service_completion['temperature']); ?> &deg;F</td>
        </tr>
        <tr>
            <td>Wind:</td>
            <td><? echo htmlspecialchars($service_completion['wind']); ?> MPH</td>
        </tr>
        <tr>
            <td>Restricted Exposure Hours:</td>
            <td><? echo htmlspecialchars($service_completion['restricted_exposure_hrs']); ?> HRS</td>
        </tr>
        <tr>
            <td>Comments:</td>
            <td><? echo htmlspecialchars($service_request['comments']); ?></td>
        </tr>
        </tbody>
    </table>
</section>

<p>&nbsp;</p>

<section class="userSection">
    <h4>Uploaded Attachments</h4>
	
	<div class="attachment-container" id="attachmentContainer">
		<!-- Dynamic content will be loaded here -->
	</div>
	

	<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			loadAttachments();
		});

		function loadAttachments() {
			fetch('_attachments.php?action=load&service_request_id=<? echo $service_request_id; ?>')
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


						div.appendChild(link);
						div.appendChild(commentDiv);
						container.appendChild(div);
					});
				})
				.catch(error => console.error('Error loading attachments:', error));
		}


	</script>
	
</section>

<?php
// Close statement and connection
$stmt_service_request->close();
$conn->close();
?>
