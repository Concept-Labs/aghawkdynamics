<?php

//path: php /home/aghawkdynamics/domains/aghawkdynamics.com/public_html/myaghawk/_cron.php

//echo "Aghawk cron ran \n";
//mail('patrik.e8@gmail.com','AgHawk cron debug','line 3.');

/**/
//DATABASE CONNECTION
$servername = "localhost";
$username = "aghawkdynamics_myaghawkuser";
$password = "02n0!RzuwhB9R^^pihQ8";
$database = "aghawkdynamics_myaghawk";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 @todo: log errors to a file instead of sending emails
 */
// Connect to the database (assuming $conn is defined in _inc.php)
if (!$conn) {
	//mail('patrik.e8@gmail.com','AgHawk cron error',"Database connection error: " . mysqli_connect_error());
    die("Database connection error: " . mysqli_connect_error());
}

// Query to delete expired tokens
$sql = "DELETE FROM password_reset_tokens WHERE expires_at < NOW()";

if ($conn->query($sql) === TRUE) {
	//mail('patrik.e8@gmail.com','AgHawk cron ran','Expired tokens cleaned up successfully.');
} else {
	//mail('patrik.e8@gmail.com','AgHawk cron error',"Error cleaning up expired tokens: " . $conn->error);
}

// Close the database connection
$conn->close();

/**/
?>