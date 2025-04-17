<?php

// Fetch all accounts and their associated users from the database
$sql = "SELECT A.account_id, A.business_name, A.business_phone, GROUP_CONCAT(CONCAT(U.contact_first_name, ' ', U.contact_last_name) SEPARATOR '<br />') AS contact_names, GROUP_CONCAT(CONCAT(U.contact_email, ' (', U.phone, ')') SEPARATOR '<br />') AS contact_info
        FROM Accounts A
        LEFT JOIN Accounts_Users U ON A.account_id = U.account_id
        GROUP BY A.account_id";
$result = $conn->query($sql);
?>

<h3>Customer Accounts</h3>


<section class="adminSection">
<table border="1" class="accountsList">
	<thead>
		<tr>
			<th>Account ID</th>
			<th>Business Name</th>
			<th>Contact Name</th>
			<th>Contact Info</th>
			<th>Pending Services</th>
			<th>Completed Services</th>
		</tr>
	</thead>
	<tbody>
		<?php if ($result->num_rows > 0): ?>
			<?php while ($account = $result->fetch_assoc()): ?>
				<tr>
					<td valign="top"><?php echo htmlspecialchars($account['account_id']); ?></td>
					<td valign="top"><a class="acctName" href="?view=account_view&id=<?php echo $account['account_id']; ?>" title="View Account"><strong><?php echo htmlspecialchars($account['business_name']); ?></strong> &nbsp;<i class="fa-solid fa-arrow-up-right-from-square"></i></a></td>
                    <td valign="top"><?php echo $account['contact_names']; ?>
					<td valign="top"><?php echo $account['contact_info']; ?></td>
					<td valign="top">
						<?php
							$sql_pending_services = "SELECT * FROM ServiceRequests WHERE account_id = ? AND status_completed = 0 ORDER BY application_need_by_date ASC";
							$stmt_pending = $conn->prepare($sql_pending_services);
							$stmt_pending->bind_param("i", $account['account_id']);
							$stmt_pending->execute();
							$result_pending = $stmt_pending->get_result();
							if ($result_pending->num_rows > 0):
						?>
								<a class="toggle-button" data-target="pendingList<? echo $account['account_id']; ?>" href="javascript:void(0);">
									Show <? echo $result_pending->num_rows; ?> Pending
								</a>
								<ul id="pendingList<? echo $account['account_id']; ?>" style="display:none;">
									
						<?		while ($service = $result_pending->fetch_assoc()):
						?>
									<li><a href="?view=service_request_edit&id=<? echo $service['service_request_id']; ?>">
										<? echo htmlspecialchars($service['reason_for_application']) . " (" . htmlspecialchars($service['type_of_service']) . ") - Need by: " . htmlspecialchars($service['application_need_by_date']); ?></a>
									</li>
									
						<?		endwhile;
								echo "</ul>\n";
							else:
								echo "None pending.<br>";
							endif;
							$stmt_pending->close();
						?>
					</td>
					<td valign="top">
						<?php
							$sql_completed_services = "SELECT ServiceRequests.reason_for_application, ServiceRequests.type_of_service, ServiceRequests.comments, 
													  ServiceCompletions.service_request_id, ServiceCompletions.completion_date 
													  FROM ServiceCompletions
													  INNER JOIN ServiceRequests ON ServiceCompletions.service_request_id = ServiceRequests.service_request_id
													  WHERE ServiceRequests.account_id = ?
													  ORDER BY ServiceCompletions.completion_date DESC";
							$stmt_completed = $conn->prepare($sql_completed_services);
							$stmt_completed->bind_param("i", $account['account_id']);
							$stmt_completed->execute();
							$result_completed = $stmt_completed->get_result();
							if ($result_completed->num_rows > 0):
						?>
								<a class="toggle-button" data-target="completedList<? echo $account['account_id']; ?>" href="javascript:void(0);">
									Show <? echo $result_completed->num_rows; ?> Completed
								</a>
								<ul id="completedList<? echo $account['account_id']; ?>" style="display:none;">
									
						<?		while ($service = $result_completed->fetch_assoc()):
						?>
									<li><a href="?view=service_request_edit&id=<? echo $service['service_request_id']; ?>">
										<? echo date('Y-m-d', strtotime($service['completion_date'])) .": " . htmlspecialchars($service['reason_for_application']) . " (" . htmlspecialchars($service['type_of_service']) . ")"; ?></a>
										<br />
										Notes: <? echo htmlspecialchars($service['comments']); ?>
									</li>
									
						<?		endwhile;
								echo "</ul>\n";
							else:
								echo "None completed.<br>";
							endif;
							$stmt_completed->close();
						?>
					</td>
				</tr>
			<?php endwhile; ?>
		<?php else: ?>
			<tr>
				<td colspan="6">No accounts found.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
	
	
<script>
    $(document).ready(function() {
        $('.toggle-button').on('click', function() {
            var targetId = $(this).data('target');
            var $targetList = $('#' + targetId);

            // Toggle visibility of the list
            $targetList.slideToggle();

            // Update button text based on visibility after animation completes
            $targetList.promise().done(() => {
                var isVisible = $targetList.is(':visible');
                var requestType = targetId.includes('pending') ? 'Pending' : 'Completed';
                var requestCount = $targetList.find('li').length;
                $(this).text((isVisible ? 'Hide' : 'Show') + ' ' + requestCount + ' ' + requestType);
            });
        });
    });
</script>
	
</section>
