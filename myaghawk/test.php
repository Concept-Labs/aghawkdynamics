
<?php 

include_once '_inc.php';

echo "Current server time is: " . date("Y-m-d H:i:s") . "<br />\n";

sendTestEmail('patrik.e8@gmail.com', 'Patrik Hertzog');

?>