<?php

// Get recurrence details
$recurrence_id = intval($_GET['recurrence_id'] ?? 0);
$sql = "SELECT * FROM RecurringPatterns WHERE recurrence_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recurrence_id);
$stmt->execute();
$result = $stmt->get_result();
$recurrence = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission to update recurrence
    $frequency = $_POST['frequency'];
    $recurrence_end_date = $_POST['recurrence_end_date'];

    update_recurrence_details($recurrence_id, $frequency, $recurrence_end_date);
}
?>


<section class="adminSection">
    <h3>Edit Recurrence</h3>
    <form method="POST">
        <label for="frequency">Frequency:</label>
        <select name="frequency" id="frequency">
            <option value="weekly" <?php echo ($recurrence['frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
            <option value="bimonthly" <?php echo ($recurrence['frequency'] == 'bimonthly') ? 'selected' : ''; ?>>Bimonthly</option>
            <option value="monthly" <?php echo ($recurrence['frequency'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
            <option value="quarterly" <?php echo ($recurrence['frequency'] == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
            <option value="annual" <?php echo ($recurrence['frequency'] == 'annual') ? 'selected' : ''; ?>>Annual</option>
        </select><br><br>
        
        <label for="recurrence_end_date">End Date:</label>
        <input type="date" name="recurrence_end_date" value="<?php echo htmlspecialchars($recurrence['recurrence_end_date']); ?>"><br><br>
        
        <button type="submit">Save Changes</button>
    </form>
    <a href="?view=recurring_services">Back to Recurring Services</a>
</section>

