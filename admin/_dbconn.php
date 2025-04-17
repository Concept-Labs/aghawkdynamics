<?

//DATABASE CONNECTION
$servername = "localhost";  // Replace with your server name or IP address
$username = "aghawkdynamics_myaghawkuser";         // Replace with your database username
$password = "02n0!RzuwhB9R^^pihQ8";             // Replace with your database password
$database = "aghawkdynamics_myaghawk";       // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>