<?php

// Get account ID from the URL
if (!isset($_GET['id'])) {
    die('Account ID is required.');
}
$account_id = intval($_GET['id']);

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

// Fetch users associated with this account
$sql_users = "SELECT * FROM Accounts_Users WHERE account_id = ?";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $account_id);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$users = $result_users->fetch_all(MYSQLI_ASSOC);

// Fetch parcels associated with the account
$sql_parcels = "SELECT * FROM Parcels WHERE account_id = ?";
$stmt_parcels = $conn->prepare($sql_parcels);
$stmt_parcels->bind_param("i", $account_id);
$stmt_parcels->execute();
$result_parcels = $stmt_parcels->get_result();
?>


<section class="adminSection AccountDetails">
	<h3>Account Details: <span><?php echo htmlspecialchars($account['business_name']); ?></span>
		<a class="addEditLink inline" href="?view=account_edit&id=<? echo $_GET['id']; ?>" title="Click to Edit"><i class="fa-solid fa-pen-to-square"></i></a>
	</h3>
	
	<div class="bizinfo">
		<strong><?php echo htmlspecialchars($account['business_name']); ?></strong> | 
		<a href="tel:<?php echo htmlspecialchars($account['business_phone']); ?>"><?php echo htmlspecialchars($account['business_phone']); ?></a><br />
		<?
			$gMapAddress = "https://www.google.com/maps/place/";
			$gMapAddress .= htmlspecialchars($account['street_address']).'+'.htmlspecialchars($account['state']).'+'.htmlspecialchars($account['zip']);
		?>
		<a target="_blank" href="<? echo $gMapAddress; ?>">
			<?php echo htmlspecialchars($account['street_address']); ?><br />
			<?php echo htmlspecialchars($account['city']).', '.htmlspecialchars($account['state']).'. '.htmlspecialchars($account['zip']); ?>
		</a><br />
		Acreage: <?php echo htmlspecialchars($account['acreage_size']); ?><br />
		Crop Category: <?php echo htmlspecialchars($account['crop_category']); ?>
	</div>
	
	<? /* ?>
	<table border="1">
		<tr><th>Business Name:</th><td><?php echo htmlspecialchars($account['business_name']); ?></td></tr>
		<tr><th>Business Phone:</th><td><?php echo htmlspecialchars($account['business_phone']); ?></td></tr>
		<tr><th>Street Address:</th><td><?php echo htmlspecialchars($account['street_address']); ?></td></tr>
		<tr><th>City:</th><td><?php echo htmlspecialchars($account['city']); ?></td></tr>
		<tr><th>State:</th><td><?php echo htmlspecialchars($account['state']); ?></td></tr>
		<tr><th>Zip Code:</th><td><?php echo htmlspecialchars($account['zip']); ?></td></tr>
		<tr><th>Acreage Size:</th><td><?php echo htmlspecialchars($account['acreage_size']); ?></td></tr>
		<tr><th>Crop Category:</th><td><?php echo htmlspecialchars($account['crop_category']); ?></td></tr>
	</table>
	<? /**/ ?>
	
</section>

<section class="adminSection Contacts">
	<h3>Contacts <a class="addEditLink inline" href="?view=account_user_add&account_id=<? echo $_GET['id']; ?>" title="Add New Contact"><i class="fa-solid fa-user-plus"></i></a></h3>
	<table border="1">
		<tr>
			<th>Contact Name:</th>
			<th>Email:</th>
			<th>Phone:</th>
			<th>Role:</th>
		</tr>
		<?php foreach ($users as $user): ?>
			<tr>
				<td><?php echo htmlspecialchars($user['contact_first_name'] . ' ' . $user['contact_last_name']); ?>
					<a class="addEditLink" href="?view=account_user_edit&user_id=<? echo $user['user_id']; ?>&account_id=<? echo $account_id; ?>" title="Click to Edit"><i class="fa-solid fa-pen-to-square"></i></a>
				</td>
				<td><?php echo htmlspecialchars($user['contact_email']); ?></td>
				<td><?php echo htmlspecialchars($user['phone']); ?></td>
				<td><?php echo htmlspecialchars($user['role']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</section>


<section class="adminSection Parcels">
    <h3>Parcels 
		<a class="addEditLink" href="?view=parcel_add&account_id=<?php echo $account_id; ?>" title="Add Parcel"><i class="fa-solid fa-square-plus"></i></a>
	</h3>
	
    <?php while ($parcel = $result_parcels->fetch_assoc()) : ?>
        <div class="parcels" style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd;">
				
            <div class="parcelHeader">
                Parcel Nickname: <?php echo htmlspecialchars($parcel['nickname']); ?> (<?php echo htmlspecialchars($parcel['acres']); ?> acres)
				<a class="addEditLink" href="?view=parcel_edit&parcel_id=<?php echo $parcel['parcel_id']; ?>&account_id=<?php echo $account_id; ?>" title="Edit Parcel"><i class="fa-solid fa-pen-to-square"></i></a>
				<br />
				<strong>Address:</strong> <?php echo htmlspecialchars($parcel['street_address'] . ', ' . $parcel['city'] . ', ' . $parcel['state'] . ' ' . $parcel['zip']); ?><br />
				<strong>Latitude:</strong> <?php echo htmlspecialchars($parcel['latitude']); ?>, <strong>Longitude:</strong> <?php echo htmlspecialchars($parcel['longitude']); ?>
            </div>

            <?php
            // Fetch blocks associated with the parcel
            $parcel_id = $parcel['parcel_id'];
            $sql_blocks = "SELECT * FROM Blocks WHERE parcel_id = ?";
            $stmt_blocks = $conn->prepare($sql_blocks);
            $stmt_blocks->bind_param("i", $parcel_id);
            $stmt_blocks->execute();
            $result_blocks = $stmt_blocks->get_result();
            ?>

            <?php if ($result_blocks->num_rows > 0) : ?>
                <div class="parcelBlocks">
                    <h4>Blocks
					<a class="addEditLink" href="?view=block_add&parcel_id=<?php echo $parcel['parcel_id']; ?>&account_id=<?php echo $account_id; ?>" title="Add new Block to this Parcel"><i class="fa-solid fa-square-plus"></i></a>
					</h4>
                    <?php while ($block = $result_blocks->fetch_assoc()) : 
					
							// Format latitude and longitude with directions
							$latitude = $block['latitude'];
							$longitude = $block['longitude'];

							$lat_direction = $latitude >= 0 ? 'N' : 'S';
							$lat_value = abs($latitude);

							$long_direction = $longitude >= 0 ? 'E' : 'W';
							$long_value = abs($longitude);

							// Create the Google Maps link
							$googleMap = "https://www.google.com/maps?q=$latitude,$longitude";
					?>
                        <div class="parcelBlock">
							<a class="addEditLink" href="?view=block_edit&block_id=<?php echo $block['block_id']; ?>&parcel_id=<?php echo $parcel_id; ?>&account_id=<?php echo $account_id; ?>" title="Click to Edit"><i class="fa-solid fa-pen-to-square"></i></a>
							
                            <p><strong>Nickname:</strong> <?php echo htmlspecialchars($block['nickname']); ?><br />
                            <strong>Acres:</strong> <?php echo htmlspecialchars($block['acres']); ?><br />
                            <strong>Coordinates:</strong> <a target="_blank" href="<? echo $googleMap; ?>"><?php echo "{$lat_value}° {$lat_direction}, {$long_value}° {$long_direction}"; ?></a><br />
                            <strong>Usage Type:</strong> <?php echo htmlspecialchars($block['crop_category']); ?><br />
                            <strong>Notes:</strong> <?php echo htmlspecialchars($block['notes']); ?>
							</p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p>No blocks found for this parcel.</p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</section>


<section class="adminSection ServiceRequests">
	<h3>Service Requests		
		<a class="addEditLink" href="?view=service_request_add&account_id=<?php echo $account_id; ?>" title="Add Parcel"><i class="fa-solid fa-square-plus"></i></a>
	</h3>
	
    <!-- Service Requests Table -->
    <table id="serviceRequestsTable">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Application Need By Date</th>
				<th>Parcel</th>
				<th>Block</th>
                <th>Service Type</th>
                <th>Product Type</th>
                <th>Product Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch all service requests for the given account_id
            $service_requests_sql = "SELECT * FROM ServiceRequests WHERE account_id = ? ORDER BY application_need_by_date DESC";
            $stmt_service_requests = $conn->prepare($service_requests_sql);
            $stmt_service_requests->bind_param("i", $account_id);
            $stmt_service_requests->execute();
            $service_requests_result = $stmt_service_requests->get_result();

            ?>
            <?php if ($service_requests_result->num_rows > 0): ?>
                <?php while ($service = $service_requests_result->fetch_assoc()): ?>
				<?php
					// Fetch Parcel nickname
					$parcel_sql = "SELECT nickname FROM Parcels WHERE parcel_id = ?";
					$stmt_parcel = $conn->prepare($parcel_sql);
					$stmt_parcel->bind_param("i", $service['parcel_id']);
					$stmt_parcel->execute();
					$parcel_result = $stmt_parcel->get_result();
					$parcel = $parcel_result->fetch_assoc();
					$parcel_nickname = $parcel ? $parcel['nickname'] : 'N/A';

					// Fetch Block nickname
					$block_sql = "SELECT nickname FROM Blocks WHERE block_id = ?";
					$stmt_block = $conn->prepare($block_sql);
					$stmt_block->bind_param("i", $service['block_id']);
					$stmt_block->execute();
					$block_result = $stmt_block->get_result();
					$block = $block_result->fetch_assoc();
					$block_nickname = $block ? $block['nickname'] : 'N/A';
                ?>
                    <tr data-account-id="<?php echo $service['account_id']; ?>" data-status="<?php echo ($service['status_completed'] == 1) ? 'Completed' : 'Pending'; ?>">
                        <td><?php echo htmlspecialchars($service['service_request_id']); ?></td>
                        <td><?php echo htmlspecialchars($service['application_need_by_date']); ?></td>
						<td><?php echo htmlspecialchars($parcel_nickname); ?></td>
                    	<td><?php echo htmlspecialchars($block_nickname); ?></td>
                        <td><?php echo htmlspecialchars($service['type_of_service']); ?></td>
                        <td><?php echo htmlspecialchars($service['type_of_product']); ?></td>
                        <td><?php echo htmlspecialchars($service['product_name']); ?></td>
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

<?php
$conn->close(); // Close the database connection
?>
