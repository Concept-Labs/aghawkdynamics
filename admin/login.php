<?php
require('_inc.php');


//$password = "SQXC46D^CJT!y0LjdtOv";
//$password = "uXQ7gX14dL&4!gz";
//$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
//echo $hashedPassword;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['admin_user_id'] = $user['user_id'];
            $_SESSION['admin_user_role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
			
            /**
             @todo: log admin entry to a file instead of sending emails
             */
            // Email details
            // $to = "patrik.e8@gmail.com";
            // $subject = "My Aghawk admin login";
            // $message = $_SESSION['first_name'] . " has logged in:\n";
            // $message .="IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
            // $message .="Hostname: " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "\n";
            // $message .="User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
            // $headers = "From: no-reply@aghawkdynamics.com";
            // mail($to, $subject, $message, $headers);
			
			
            header("Location: index.php");
            exit();
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "Invalid email or password.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - AgHawk Dynamics</title>
    <!-- Add any CSS or JS here if needed -->
	
	
	<link rel="stylesheet" type="text/css" href="styles_admin.css?t=<?php echo time(); ?>" />
	
</head>
<body class="loginPage">
        <form method="POST">
        	<p><img src="images/admin-logo.png" /></p>
            <input type="email" name="email" required placeholder="Email:">
            <input type="password" name="password" required placeholder="Password:">
            <button type="submit">Login</button>
        </form>
<?php
    // Display all session data at the bottom of the page
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
?>
</body>
</html>

