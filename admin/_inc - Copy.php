<?php //ADMIN INC

// ONLY AVAILABLE TO ME DURING DEVELOPMENT
if ($_SERVER['REMOTE_ADDR'] !== '96.46.17.70') {
    die("Access Restricted");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//LOAD WORDPRESS FUNCTIONS
require_once '../wp-load.php';


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

// ADD ACCOUNT
function add_account($data) {
    global $conn;
    
    // Sanitize and extract data
    $business_name = $data['business_name'];
    $street_address = $data['street_address'];
    $city = $data['city'];
    $state = $data['state'];
    $zip = $data['zip'];
    $business_phone = $data['business_phone'];
    $acreage_size = $data['acreage_size'];
    $crop_category = $data['crop_category'];

    // Insert new account details
    $sql_insert = "INSERT INTO Accounts (business_name, street_address, city, state, zip, business_phone, acreage_size, crop_category, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssssssss", $business_name, $street_address, $city, $state, $zip, $business_phone, $acreage_size, $crop_category);

    if ($stmt_insert->execute()) {
        // Redirect to accounts list page after successful insertion
        if (!headers_sent()) {
            header("Location: ?view=accounts");
            exit();
        } else {
            echo "<script>window.location.href='?view=accounts';</script>";
            exit();
        }
    } else {
        echo "Error adding account: " . $conn->error;
    }
}

//UPDATE ACCOUNT DETAILS
function update_account_details($account_id, $data) {
    global $conn;    
    // Sanitize and extract data
    $business_name = $data['business_name'];
    $business_phone = $data['business_phone'];
    $street_address = $data['street_address'];
    $city = $data['city'];
    $state = $data['state'];
    $zip = $data['zip'];
    $acreage_size = $data['acreage_size'];
    $crop_category = $data['crop_category'];

    // Update account details
    $sql_update = "UPDATE Accounts SET business_name = ?, business_phone = ?, street_address = ?, city = ?, state = ?, zip = ?, acreage_size = ?, crop_category = ? WHERE account_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssssssi", $business_name, $business_phone, $street_address, $city, $state, $zip, $acreage_size, $crop_category, $account_id);
    
    if ($stmt_update->execute()) {
        // Redirect to account view page after successful update
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=$account_id");
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=$account_id';</script>";
            exit();
        }
    } else {
        echo "Error updating account: " . $conn->error;
    }
}


//UPDATE USER DETAILS
function update_user_details($user_id, $data) {
    global $conn;

    // Ensure all data is set and trimmed properly
    $first_name = isset($data['first_name']) && trim($data['first_name']) !== '' ? trim($data['first_name']) : null;
    $last_name = isset($data['last_name']) && trim($data['last_name']) !== '' ? trim($data['last_name']) : null;
    $contact_email = isset($data['contact_email']) && trim($data['contact_email']) !== '' ? trim($data['contact_email']) : null;
    $phone = isset($data['phone']) && trim($data['phone']) !== '' ? trim($data['phone']) : null;
    $role = isset($data['role']) && trim($data['role']) !== '' ? trim($data['role']) : null;
    $password = isset($data['password']) ? trim($data['password']) : null;

    // If any mandatory field is missing, print an error message
    if (!$first_name || !$last_name || !$contact_email || !$phone || !$role) {
        echo "Mandatory field(s) missing.";
        exit();
    }

    // Prepare the SQL update statement
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE Accounts_Users SET contact_first_name = ?, contact_last_name = ?, contact_email = ?, phone = ?, role = ?, password = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            exit();
        }
        $stmt_update->bind_param("ssssssi", $first_name, $last_name, $contact_email, $phone, $role, $password_hash, $user_id);
    } else {
        $sql_update = "UPDATE Accounts_Users SET contact_first_name = ?, contact_last_name = ?, contact_email = ?, phone = ?, role = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            exit();
        }
        $stmt_update->bind_param("sssssi", $first_name, $last_name, $contact_email, $phone, $role, $user_id);
    }

    // Execute the update statement
    if ($stmt_update->execute()) {
        // Redirect to account view page after successful update
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . intval($_GET['account_id']));
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . intval($_GET['account_id']) . "';</script>";
            exit();
        }
    } else {
        echo "Error updating user: " . $stmt_update->error;
    }
}


//ADD ACCOUNT USER
function add_account_user($account_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $contact_email = $data['contact_email'];
    $phone = $data['phone'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
    $role = $data['role'];

    // Insert new user details
    $sql_insert = "INSERT INTO Accounts_Users (account_id, contact_first_name, contact_last_name, contact_email, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issssss", $account_id, $first_name, $last_name, $contact_email, $phone, $password, $role);

    if ($stmt_insert->execute()) {
        // Redirect to account view page after successful insertion
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . $account_id);
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . $account_id . "';</script>";
            exit();
        }
    } else {
        echo "Error adding user: " . $conn->error;
    }
}


// ADD PARCEL
function add_parcel($account_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $nickname = $data['nickname'];
    $street_address = $data['street_address'];
    $city = $data['city'];
    $state = $data['state'];
    $zip = $data['zip'];
    $acres = $data['acres'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
    $usage_type = $data['usage_type'];

    // Insert new parcel details
    $sql_insert = "INSERT INTO Parcels (account_id, nickname, street_address, city, state, zip, acres, latitude, longitude, usage_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isssssddss", $account_id, $nickname, $street_address, $city, $state, $zip, $acres, $latitude, $longitude, $usage_type);

    if ($stmt_insert->execute()) {
        // Redirect to account view page after successful insertion
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . $account_id);
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . $account_id . "';</script>";
            exit();
        }
    } else {
        echo "Error adding parcel: " . $conn->error;
    }
}

//UPDATE PARCEL DETAILS
function update_parcel_details($parcel_id, $account_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $parcel_number = $data['parcel_number'];
    $nickname = $data['nickname'];
    $street_address = $data['street_address'];
    $city = $data['city'];
    $state = $data['state'];
    $zip = $data['zip'];
    $acres = $data['acres'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
    $usage_type = $data['usage_type'];

    // Update parcel details
    $sql_update = "UPDATE Parcels SET parcel_number = ?, nickname = ?, street_address = ?, city = ?, state = ?, zip = ?, acres = ?, latitude = ?, longitude = ?, usage_type = ? WHERE parcel_id = ? AND account_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssdsssiii", $parcel_number, $nickname, $street_address, $city, $state, $zip, $acres, $latitude, $longitude, $usage_type, $parcel_id, $account_id);
    
    if ($stmt_update->execute()) {
        // Redirect to account view page after successful update
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=$account_id");
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=$account_id';</script>";
            exit();
        }
    } else {
        echo "Error updating parcel: " . $conn->error;
    }
}


// ADD NEW BLOCK
function add_new_block($parcel_id, $account_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $nickname = $data['nickname'];
    $acres = $data['acres'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
    $usage_type = $data['usage_type'];
    $notes = $data['notes'];

    // Insert new block details
    $sql_insert = "INSERT INTO Blocks (parcel_id, nickname, acres, latitude, longitude, usage_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isddsss", $parcel_id, $nickname, $acres, $latitude, $longitude, $usage_type, $notes);

    if ($stmt_insert->execute()) {
        // Redirect to account view page after successful insertion
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . $account_id);
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . $account_id . "';</script>";
            exit();
        }
    } else {
        echo "Error adding block: " . $conn->error;
    }
}

// UPDATE BLOCK
function update_block_details($block_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $nickname = $data['nickname'];
    $acres = $data['acres'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
    $usage_type = $data['usage_type'];
    $notes = $data['notes'];

    // Update block details
    $sql_update = "UPDATE Blocks SET nickname = ?, acres = ?, latitude = ?, longitude = ?, usage_type = ?, notes = ? WHERE block_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sddsssi", $nickname, $acres, $latitude, $longitude, $usage_type, $notes, $block_id);

    if ($stmt_update->execute()) {
        // Redirect to account view page after successful update
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . intval($_GET['account_id']));
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . intval($_GET['account_id']) . "';</script>";
            exit();
        }
    } else {
        echo "Error updating block: " . $conn->error;
    }
}


// ADD SERVICE REQUEST
function add_service_request($account_id, $parcel_id, $block_id, $data, $service_type, $frequency, $recurrence_end_date) {
    global $conn;

    // Sanitize and extract data
    $contact_id = $data['contact_id'];
    $reason_for_application = $data['reason_for_application'];
    $type_of_service = $data['type_of_service'];
    $type_of_product = $data['type_of_product'];
    $product_name = $data['product_name'];
    $supplier_name = $data['supplier_name'];
    $supplier_contact = $data['supplier_contact'];
    $application_need_by_date = $data['application_need_by_date'];
    $comments = $data['comments'];

    // Initialize recurrence_id to NULL
    $recurrence_id = null;
    $next_scheduled_date = null;

    // If the service is recurring, insert data into the RecurringPatterns table first
    if ($service_type === 'recurring') {
        // Calculate the next scheduled date based on frequency
        $next_scheduled_date = calculate_next_scheduled_date($frequency, $application_need_by_date);

        $sql_recurrence = "INSERT INTO RecurringPatterns (
            account_id, parcel_id, block_id, frequency, recurrence_end_date, next_scheduled_date, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt_recurrence = $conn->prepare($sql_recurrence);
        $stmt_recurrence->bind_param("iiisss", $account_id, $parcel_id, $block_id, $frequency, $recurrence_end_date, $next_scheduled_date);

        if ($stmt_recurrence->execute()) {
            // Get the inserted recurrence_id
            $recurrence_id = $stmt_recurrence->insert_id;
        } else {
            echo "Error adding recurring pattern: " . $conn->error;
            return;
        }
    }

    // Insert new service request details
    $sql_insert = "INSERT INTO ServiceRequests (
        account_id, parcel_id, block_id, contact_id, reason_for_application, type_of_service,
        type_of_product, product_name, supplier_name, supplier_contact, application_need_by_date,
        service_type, frequency, recurrence_end_date, recurrence_id, comments, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiisssssssssssis", $account_id, $parcel_id, $block_id, $contact_id,
        $reason_for_application, $type_of_service, $type_of_product, $product_name, $supplier_name,
        $supplier_contact, $application_need_by_date, $service_type, $frequency, $recurrence_end_date, $recurrence_id, $comments);

    if ($stmt_insert->execute()) {
        // Redirect to account view page after successful insertion
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . $account_id);
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . $account_id . "';</script>";
            exit();
        }
    } else {
        echo "Error adding service request: " . $conn->error;
    }
}
 //END ADD SERVICE REQUEST

//UPDATE RECURRENCE
function update_recurrence_details($recurrence_id, $frequency, $recurrence_end_date) {
    global $conn;
    
    $sql_update = "UPDATE RecurringPatterns SET frequency = ?, recurrence_end_date = ? WHERE recurrence_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $frequency, $recurrence_end_date, $recurrence_id);

    if ($stmt_update->execute()) {
        // Redirect to recurring services management page after successful update
        if (!headers_sent()) {
            header("Location: ?view=recurring_services");
            exit();
        } else {
            echo "<script>window.location.href='?view=recurring_services';</script>";
            exit();
        }
    } else {
        echo "Error updating recurrence: " . $conn->error;
    }
}



function calculate_next_scheduled_date($frequency, $base_date) {
    $date = new DateTime($base_date);

    switch ($frequency) {
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'biweekly':
            $date->modify('+2 weeks');
            break;
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'quarterly':
            $date->modify('+3 months');
            break;
        case 'annual':
            $date->modify('+1 year');
            break;
        default:
            // No change for one-time services or unknown frequency
            return null;
    }

    return $date->format('Y-m-d');
}



?>