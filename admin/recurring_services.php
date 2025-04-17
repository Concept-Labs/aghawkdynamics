<?php

// Include database connection
require_once('_inc.php');

// Fetch all recurring services
$sql = "SELECT RecurringPatterns.*, ServiceCompletions.completion_date, ServiceCompletions.status, ServiceCompletions.notes, Accounts.business_name
        FROM RecurringPatterns
        LEFT JOIN ServiceRequests ON RecurringPatterns.service_request_id = ServiceRequests.service_request_id
        LEFT JOIN ServiceCompletions ON ServiceRequests.service_request_id = ServiceCompletions.service_request_id
        LEFT JOIN Accounts ON RecurringPatterns.account_id = Accounts.account_id
        ORDER BY RecurringPatterns.next_scheduled_date ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
?>
    <section class="adminSection">
        <h3>Recurring Services</h3>
        <table>
            <thead>
                <tr>
                    <th>Business Name</th>
                    <th>Recurrence ID</th>
                    <th>Frequency</th>
                    <th>Next Scheduled Date</th>
                    <th>Completion Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['business_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['recurrence_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['frequency']); ?></td>
                        <td><?php echo htmlspecialchars($row['next_scheduled_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['completion_date'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?></td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                        <td>
                            <a href="?view=recurrence_edit&recurrence_id=<?php echo $row['recurrence_id']; ?>">Edit Recurrence</a> |
                            <a href="?view=recurrence_end&recurrence_id=<?php echo $row['recurrence_id']; ?>">End Recurrence</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
<?php else: ?>
    <p>No recurring services found.</p>
<?php endif; ?>

<?php $conn->close(); ?>
