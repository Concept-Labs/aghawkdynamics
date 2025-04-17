<?php //MYAGHAWK USER INC
ob_start(); // Prevents output before headers
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//LOAD WORDPRESS FUNCTIONS
require_once '../wp-load.php';
require_once '../admin/_dbconn.php'; // database connection 


if (isset($_GET['logout'])) {
	session_start();
	session_unset();
	session_destroy();
	header("Location:https://my.aghawkdynamics.com/");
}

session_start();

//LOAD PHPMAILER FILES
require_once '../_PHPMailer/src/PHPMailer.php';
require_once '../_PHPMailer/src/SMTP.php';
require_once '../_PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? null;

    switch ($action) {
        case 'login':
            user_login($_POST);
            break;
		/*
        case 'newUser':
            $_SESSION['user_status'] = 'newUser';
            $_SESSION['currentScreen'] = 'newUserAccountInfo';
            break;
		/**/
        case 'continueRegistration':
            continueRegistration($_POST);
            $_SESSION['currentScreen'] = 'newUserContacts';
            break;

        case 'registerNewAccount':
            registerNewAccount($_POST);
			if($_POST['continueToPropertyDetails']=='yes') {
				header("Location:https://my.aghawkdynamics.com/parcel_add");
			} else {
				$_SESSION['currentScreen'] = 'profile';
			}            
            break;
		case 'addParcelAndBlocks':
			addParcelAndBlocks($_POST);
			break;
			
		case 'addServiceRequest':
			$account_id = $_SESSION['account_id'];
			$parcel_id = intval($_POST['parcel_id'] ?? 0);
			$block_id = intval($_POST['block_id'] ?? 0);
			$service_type = $_POST['service_type'] ?? 'one_time';
			$frequency = $_POST['frequency'] ?? null;
			$recurrence_end_date = $_POST['recurrence_end_date'] ?? null;
			add_service_request($account_id, $parcel_id, $block_id, $_POST, $service_type, $frequency, $recurrence_end_date);
			break;			
			
        case 'forgotPassword':
            forgot_myaghawk_password($_POST['email']);
            break;

        case 'resetPassword':
            reset_myaghawk_password($_POST['token'], $_POST['new_password']);
            break;

        default:
            // Optional: Handle unknown or missing actions
            error_log("Unknown action: $action");
            break;
    }
} //end $_SERVER["REQUEST_METHOD"] == "POST"

// Check for specific keywords in the URL
if (isset($_SERVER['REQUEST_URI'])) {
    $requestUri = $_SERVER['REQUEST_URI'];    
    $screenMapping = [ // Map URL keywords to screen values
        'export_service_requests' => 'export_service_requests',
        'dashboard' => 'dashboard',
        'parcels' => 'parcels',
        'parcel_detail' => 'parcel_detail',
        'parcel_add' => 'parcel_add',
        'parcel_edit' => 'parcel_edit',
        'blocks' => 'blocks',
        'block_detail' => 'block_detail',
        'block_add' => 'block_add',
        'block_edit' => 'block_edit',
        'service_requests' => 'service_requests',
        'recurring_services' => 'recurring_services',
        'service_request_add' => 'service_request_add',
        'service_request_edit' => 'service_request_edit',
        'service_request_detail' => 'service_request_detail',
        'password_reset' => 'password_reset',
        'signup' => 'newUserAccountInfo',
		'continueRegistration' => 'newUserContacts',
		'profile_edit' => 'profile_edit',
		'profile' => 'profile',
		'user_add' => 'user_add',
		'user_edit' => 'user_edit',
		'user' => 'user',
        'self_tracking_add' => 'self_tracking_add',
		'self_tracking_activity' => 'self_tracking_activity',
        // Add more mappings here
    ];
    foreach ($screenMapping as $keyword => $screen) {
        if (strpos($requestUri, $keyword) !== false) {
            $_SESSION['currentScreen'] = $screen;
			if($keyword=='signup') {
				$_SESSION['user_status'] = 'newUser';
			}
            break; // Exit the loop once a match is found
        }
    }
}



function user_login($data) {
    global $conn;	
	$email = $_POST['email'];
	$password = $_POST['password'];

	$sql = "SELECT * FROM Accounts_Users WHERE contact_email = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$account_user = $result->fetch_assoc();
		if (password_verify($password, $account_user['password'])) {
			$_SESSION['account_user_id'] = $account_user['account_user_id'];
			$_SESSION['account_id'] = $account_user['account_id'];
			$_SESSION['contact_first_name'] = $account_user['contact_first_name'];
			$_SESSION['contact_last_name'] = $account_user['contact_last_name'];
			$_SESSION['contact_email'] = $account_user['contact_email'];
			$_SESSION['phone'] = $account_user['phone'];
			$_SESSION['user_role'] = $account_user['role'];

			$_SESSION['user_status'] = 'registeredUser';
			$_SESSION['currentScreen'] = 'profile';
			session_regenerate_id(true);
		} else {
			$_SESSION['displayMsg'] = "Invalid email or password.";
		}
	} else {
		$_SESSION['displayMsg'] = "Invalid email or password.";
	}
}

function continueRegistration($data) {
    foreach ($data as $key => $value) {
        // Check if the key is "action" and handle it separately
        if ($key === 'action') {
            $_SESSION[$key] = $value ?? '';
        } else {
            $_SESSION["reg_{$key}"] = $value ?? '';
        }
    }
}



function registerNewAccount($data) {
    global $conn;

    // Sanitize and extract account data
    $business_name       = $data['reg_business_name'];
    $business_phone      = $data['reg_business_phone'];
    $street_address      = $data['reg_street_address'];
    $city                = $data['reg_city'];
    $state               = $data['reg_state'];
    $zip                 = $data['reg_zip'];
    $billing_address     = $data['reg_billing_address'];
    $billing_city        = $data['reg_billing_city'];
    $billing_state       = $data['reg_billing_state'];
    $billing_zip         = $data['reg_billing_zip'];
    $acreage_size        = $data['reg_acreage_size'];
    $crop_category       = $data['reg_crop_category'];
    $crop_mix_notes      = $data['reg_crop_mix_notes'];
    $status              = 'pending';

    // Insert new account details
    $sql_insert_account = "INSERT INTO Accounts (business_name, street_address, city, state, zip, billing_address, billing_city, billing_state, billing_zip, business_phone, acreage_size, crop_category, crop_mix_notes, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $stmt_account = $conn->prepare($sql_insert_account);

    if (!$stmt_account) {
        die("Account preparation failed: " . $conn->error);
    }

    $stmt_account->bind_param("ssssssssssssss", $business_name, $street_address, $city, $state, $zip, $billing_address, $billing_city, $billing_state, $billing_zip, $business_phone, $acreage_size, $crop_category, $crop_mix_notes, $status);

    if ($stmt_account->execute()) {
        // Get the last inserted account ID
        $account_id = $conn->insert_id;

        // Now insert all users associated with the account
        $first_names   = $data['first_name'] ?? [];
        $last_names    = $data['last_name'] ?? [];
        $emails        = $data['contact_email'] ?? [];
        $phones        = $data['phone'] ?? [];
        $passwords     = $data['password'] ?? [];
        $roles         = $data['role'] ?? [];

        if (count($first_names) === 0) {
            die("No users provided for this account.");
        }

        // Prepare the SQL statement for user insertion
        $sql_insert_user = "INSERT INTO Accounts_Users (account_id, contact_first_name, contact_last_name, contact_email, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_user = $conn->prepare($sql_insert_user);

        if (!$stmt_user) {
            die("User preparation failed: " . $conn->error);
        }

        for ($i = 0; $i < count($first_names); $i++) {
            // Sanitize user data
            $first_name   = $first_names[$i];
            $last_name    = $last_names[$i];
            $email        = $emails[$i];
            $phone        = $phones[$i];
            $password     = password_hash($passwords[$i], PASSWORD_DEFAULT); // Hash the password
            $role         = $roles[$i];

            // Bind parameters and execute the statement
            $stmt_user->bind_param("issssss", $account_id, $first_name, $last_name, $email, $phone, $password, $role);

            if ($stmt_user->execute()) {
                // Capture the first user's details for session
                if ($i === 0) {
                    $_SESSION['account_user_id'] = $conn->insert_id;
                    $_SESSION['account_id'] = $account_id;
                    $_SESSION['contact_first_name'] = $first_name;
                    $_SESSION['contact_last_name'] = $last_name;
                    $_SESSION['contact_email'] = $email;
                    $_SESSION['phone'] = $phone;
                    $_SESSION['user_role'] = $role;
                    $_SESSION['user_status'] = 'registeredUser';                    
					
					//SEND EMAILS
					sendWelcomeEmail($email, "{$first_name} {$last_name}");
                }
				
            } else {
                echo "Error adding user: " . $stmt_user->error;
                // Optionally rollback if needed
            }
        }
		$_SESSION['displayMsg'] = "<strong>Congratulations, your account has been created!</strong><br />
									You are logged in as {$first_name} {$last_name}. If this is not you, please <a href='?logout'>log out</a> and then log in again.";
    } else {
        echo "Error adding account: " . $stmt_account->error;
    }
}


function addParcelAndBlocks($data) {
    global $conn;

    // Extract and sanitize parcel data
    $account_id      = intval($_SESSION['account_id']);
    $nickname        = $data['nickname'];
    $street_address  = $data['street_address'];
    $city            = $data['city'];
    $state           = $data['state'];
    $zip             = $data['zip'];
    $acres           = floatval($data['acres']);
    $latitude        = $data['latitude'] ?? '';
    $longitude       = $data['longitude'] ?? '';
    $usage_type      = $data['usage_type'] ?? '';
    $created_at      = date('Y-m-d H:i:s');

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Insert into Parcels table
        $sql_parcel = "INSERT INTO Parcels (account_id, nickname, street_address, city, state, zip, acres, latitude, longitude, usage_type, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_parcel = $conn->prepare($sql_parcel);
        if (!$stmt_parcel) {
            throw new Exception("Failed to prepare parcel statement: " . $conn->error);
        }
        $stmt_parcel->bind_param("isssssdssss", $account_id, $nickname, $street_address, $city, $state, $zip, $acres, $latitude, $longitude, $usage_type, $created_at);
        $stmt_parcel->execute();
        $parcel_id = $conn->insert_id;

        // Generate unique_id for parcel and update
        $unique_id_parcel = "P_" . str_pad($parcel_id, 11, "0", STR_PAD_LEFT);
        $sql_update_parcel_id = "UPDATE Parcels SET unique_id = ? WHERE parcel_id = ?";
        $stmt_update_parcel_id = $conn->prepare($sql_update_parcel_id);
        $stmt_update_parcel_id->bind_param("si", $unique_id_parcel, $parcel_id);
        $stmt_update_parcel_id->execute();

        // Insert blocks for the parcel
        $block_nicknames = $data['block_nickname'];
        $crop_categories = $data['crop_category'];
        $crop_mix_notes  = $data['crop_mix_notes'] ?? [];
        $block_acres     = $data['block_acres'];
        $notes           = $data['notes'] ?? [];
        for ($i = 0; $i < count($block_nicknames); $i++) {
            $block_nickname = $block_nicknames[$i];
            $crop_category  = $crop_categories[$i];
            $crop_mix_note  = $crop_mix_notes[$i] ?? '';
            $block_acres  = $block_acres[$i];
            $block_note     = $notes[$i] ?? '';

            $sql_block = "INSERT INTO Blocks (parcel_id, account_id, nickname, crop_category, crop_mix_notes, acres, notes, created_at)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_block = $conn->prepare($sql_block);
            if (!$stmt_block) {
                throw new Exception("Failed to prepare block statement: " . $conn->error);
            }
            $stmt_block->bind_param("iisssiss", $parcel_id, $account_id, $block_nickname, $crop_category, $crop_mix_note, $block_acres, $block_note, $created_at);
            $stmt_block->execute();
            $block_id = $conn->insert_id;

            // Generate unique_id for block and update
            $unique_id_block = "B_" . str_pad($block_id, 11, "0", STR_PAD_LEFT);
            $sql_update_block_id = "UPDATE Blocks SET unique_id = ? WHERE block_id = ?";
            $stmt_update_block_id = $conn->prepare($sql_update_block_id);
            $stmt_update_block_id->bind_param("si", $unique_id_block, $block_id);
            $stmt_update_block_id->execute();
        }

        // Commit the transaction
        $conn->commit();
        $_SESSION['displayMsg'] = "Parcel and blocks added successfully!";

    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        error_log("Error in addParcelAndBlocks: " . $e->getMessage());
        $_SESSION['displayMsg'] ="An error occurred while adding the parcel and blocks. Please try again.";
    }
} // end addParcelAndBlocks()



//UPDATE PARCEL DETAILS
function update_parcel_details($parcel_id, $account_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $nickname = $data['nickname'];
    $street_address = $data['street_address'];
    $city = $data['city'];
    $state = $data['state'];
    $zip = $data['zip'];
    $acres = $data['acres'];
    $crop_category = $data['crop_category'];

    // Update parcel details
    $sql_update = "UPDATE Parcels SET nickname = ?, street_address = ?, city = ?, state = ?, zip = ?, acres = ? WHERE parcel_id = ? AND account_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssdiii", $nickname, $street_address, $city, $state, $zip, $acres, $parcel_id, $account_id);
    
    if ($stmt_update->execute()) {
		
		// Update the unique_id
		if ($parcel_id) {
			$unique_id = "P_" . str_pad($parcel_id, 11, "0", STR_PAD_LEFT);
			$sql_update_unique_id = "UPDATE Parcels SET unique_id = ? WHERE parcel_id = ?";
			$stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
			if ($stmt_update_unique_id === false) {
				die('Prepare failed: ' . htmlspecialchars($conn->error));
			}
			$stmt_update_unique_id->bind_param("si", $unique_id, $parcel_id);
			if (!$stmt_update_unique_id->execute()) {
				die("Error updating unique_id: " . htmlspecialchars($stmt_update_unique_id->error));
			}
		}
		
        $_SESSION['displayMsg'] = "Parcel details updated.";
        
    } else {
        $_SESSION['displayMsg'] = "Error updating parcel: " . $conn->error;
    }
}


// ADD NEW BLOCK
function add_new_block($parcel_id, $account_id, $data) {
    global $conn;
    
    // Sanitize and extract data
    $nickname = $data['nickname'];
    $acres = $data['acres'];
    $crop_category = $data['crop_category'];
    $notes = $data['notes'];

    // Insert new block details
    $sql_insert = "INSERT INTO Blocks (parcel_id, nickname, acres, crop_category, notes) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issss", $parcel_id, $nickname, $acres, $crop_category, $notes);

    if ($stmt_insert->execute()) {
				
		// Get the last inserted or updated id
		$block_id = $conn->insert_id;

		// Update the unique_id
		if ($block_id) {
			$unique_id = "B_" . str_pad($block_id, 11, "0", STR_PAD_LEFT);
			$sql_update_unique_id = "UPDATE Blocks SET unique_id = ? WHERE block_id = ?";
			$stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
			if ($stmt_update_unique_id === false) {
				die('Prepare failed: ' . htmlspecialchars($conn->error));
			}
			$stmt_update_unique_id->bind_param("si", $unique_id, $block_id);
			if (!$stmt_update_unique_id->execute()) {
				die("Error updating unique_id: " . htmlspecialchars($stmt_update_unique_id->error));
			}
		}
		
        $_SESSION['displayMsg'] = "Block added.";
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
    $crop_category = $data['crop_category'];
    $notes = $data['notes'];

    // Update block details
    $sql_update = "UPDATE Blocks SET nickname = ?, acres = ?, crop_category = ?, notes = ? WHERE block_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdssi", $nickname, $acres, $crop_category, $notes, $block_id);

    if ($stmt_update->execute()) {

		// Update the unique_id
		if ($block_id) {
			$unique_id = "B_" . str_pad($block_id, 11, "0", STR_PAD_LEFT);
			$sql_update_unique_id = "UPDATE Blocks SET unique_id = ? WHERE block_id = ?";
			$stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
			if ($stmt_update_unique_id === false) {
				die('Prepare failed: ' . htmlspecialchars($conn->error));
			}
			$stmt_update_unique_id->bind_param("si", $unique_id, $block_id);
			if (!$stmt_update_unique_id->execute()) {
				die("Error updating unique_id: " . htmlspecialchars($stmt_update_unique_id->error));
			}
		}
		
        $_SESSION['displayMsg'] = "Block details updated.";
        
    } else {
        $_SESSION['displayMsg'] = "Error updating block: " . $conn->error;
    }
}


//DEACTIVATE PARCEL
function deactivate_parcel($parcel_id, $account_id, $deactivate_notes) {
    global $conn;

    // Start a transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        // Step 1: Mark the parcel as inactive
        $sql_update_parcel = "UPDATE Parcels SET status = 'inactive', status_notes = ? WHERE parcel_id = ? AND account_id = ?";
        $stmt_update_parcel = $conn->prepare($sql_update_parcel);
        if (!$stmt_update_parcel) {
            throw new Exception("SQL Error (update parcel): " . $conn->error);
        }
        $stmt_update_parcel->bind_param("sii", $deactivate_notes, $parcel_id, $account_id);
        $stmt_update_parcel->execute();
        $stmt_update_parcel->close();

        // Step 2: Get all blocks belonging to this parcel
        $sql_get_blocks = "SELECT block_id FROM Blocks WHERE parcel_id = ? AND account_id = ? AND status != 'inactive'";
        $stmt_get_blocks = $conn->prepare($sql_get_blocks);
        if (!$stmt_get_blocks) {
            throw new Exception("SQL Error (fetch blocks): " . $conn->error);
        }
        $stmt_get_blocks->bind_param("ii", $parcel_id, $account_id);
        $stmt_get_blocks->execute();
        $result_blocks = $stmt_get_blocks->get_result();
        $stmt_get_blocks->close();

        // Step 3: Deactivate each block and track removed service requests
        $total_deleted_requests = 0;

        while ($row = $result_blocks->fetch_assoc()) {
            $block_id = $row['block_id'];
            $block_result = deactivate_block($block_id, $account_id, $deactivate_notes);

            // Extract deleted request count from session message if successful
            if ($block_result && isset($_SESSION['displayMsg'])) {
                if (preg_match('/(\d+) pending service request\(s\) have been removed/', $_SESSION['displayMsg'], $matches)) {
                    $total_deleted_requests += (int) $matches[1];
                }
            }
        }

        // Commit transaction
        $conn->commit();

        // Display success message
        $_SESSION['displayMsg'] = "Parcel deactivated successfully.<br />$total_deleted_requests pending service request(s) have been removed.";
        return true;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['displayMsg'] = "Error deactivating parcel: " . $e->getMessage();
        return false;
    }
} //end deactivate parcel

//ACTIVATE PARCEL
function activate_parcel($parcel_id, $account_id) {
    global $conn;

    // Ensure parcel exists and is currently inactive
    $sql_check = "SELECT status FROM Parcels WHERE parcel_id = ? AND account_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $parcel_id, $account_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        $_SESSION['displayMsg'] = "Parcel not found or does not belong to this account.";
        return false;
    }

    $parcel = $result_check->fetch_assoc();
    if ($parcel['status'] == 'active') {
        $_SESSION['displayMsg'] = "Parcel is already active.";
        return false;
    }

    // Start a transaction for consistency
    $conn->begin_transaction();

    try {
        // Step 1: Activate the parcel
        $sql_update = "UPDATE Parcels SET status = 'active', status_notes = NULL WHERE parcel_id = ? AND account_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $parcel_id, $account_id);
        $stmt_update->execute();

        // Step 2: Check if there are any inactive blocks under this parcel
        $sql_count_blocks = "SELECT COUNT(*) AS inactive_blocks FROM Blocks WHERE parcel_id = ? AND account_id = ? AND status = 'inactive'";
        $stmt_count_blocks = $conn->prepare($sql_count_blocks);
        $stmt_count_blocks->bind_param("ii", $parcel_id, $account_id);
        $stmt_count_blocks->execute();
        $stmt_count_blocks->bind_result($inactive_blocks_count);
        $stmt_count_blocks->fetch();
        $stmt_count_blocks->close();

        // Commit transaction
        $conn->commit();

        if ($inactive_blocks_count > 0) {
            $_SESSION['displayMsg'] = "Parcel activated successfully. <br />There are $inactive_blocks_count inactive block(s) that can now be reactivated.";
        } else {
            $_SESSION['displayMsg'] = "Parcel activated successfully.";
        }

        return true;

    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $_SESSION['displayMsg'] = "Error activating parcel: " . $e->getMessage();
        return false;
    }
}


//DEACTIVATE BLOCK
function deactivate_block($block_id, $account_id, $deactivate_notes) {
    global $conn;

    // Start a transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        // Step 1: Mark the block as inactive
        $sql_update_block = "UPDATE Blocks SET status = 'inactive', status_notes = ? WHERE block_id = ? AND account_id = ?";
        $stmt_update_block = $conn->prepare($sql_update_block);
        if (!$stmt_update_block) {
            throw new Exception("SQL Error (update block): " . $conn->error);
        }
        $stmt_update_block->bind_param("sii", $deactivate_notes, $block_id, $account_id);
        $stmt_update_block->execute();
        $stmt_update_block->close();

        // Step 2: Count pending service requests before deleting them
        $sql_count_requests = "SELECT COUNT(*) FROM ServiceRequests WHERE block_id = ? AND account_id = ? AND status_completed = 0";
        $stmt_count_requests = $conn->prepare($sql_count_requests);
        if (!$stmt_count_requests) {
            throw new Exception("SQL Error (count service requests): " . $conn->error);
        }
        $stmt_count_requests->bind_param("ii", $block_id, $account_id);
        $stmt_count_requests->execute();
        $stmt_count_requests->bind_result($deleted_requests_count);
        $stmt_count_requests->fetch();
        $stmt_count_requests->close();

        // Step 3: Delete pending service requests (status_completed = 0)
        $sql_delete_requests = "DELETE FROM ServiceRequests WHERE block_id = ? AND account_id = ? AND status_completed = 0";
        $stmt_delete_requests = $conn->prepare($sql_delete_requests);
        if (!$stmt_delete_requests) {
            throw new Exception("SQL Error (delete service requests): " . $conn->error);
        }
        $stmt_delete_requests->bind_param("ii", $block_id, $account_id);
        $stmt_delete_requests->execute();
        $stmt_delete_requests->close();

        // Commit the transaction
        $conn->commit();

        // Display message with the number of deleted requests
        $_SESSION['displayMsg'] = "Block deactivated successfully.<br />$deleted_requests_count pending service request(s) have been removed.";
        return true;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['displayMsg'] = "Error deactivating block: " . $e->getMessage();
        return false;
    }
}
// ACTIVATE BLOCK 
function activate_block($block_id, $account_id) {
    global $conn;

    // Step 1: Ensure block exists and is currently inactive
    $sql_check_block = "SELECT parcel_id, status FROM Blocks WHERE block_id = ? AND account_id = ?";
    $stmt_check_block = $conn->prepare($sql_check_block);
    $stmt_check_block->bind_param("ii", $block_id, $account_id);
    $stmt_check_block->execute();
    $result_check_block = $stmt_check_block->get_result();

    if ($result_check_block->num_rows == 0) {
        $_SESSION['displayMsg'] = "Block not found or does not belong to this account.";
        return false;
    }

    $block = $result_check_block->fetch_assoc();
    if ($block['status'] == 'active') {
        $_SESSION['displayMsg'] = "Block is already active.";
        return false;
    }

    // Step 2: Check if the parent parcel is active
    $sql_check_parcel = "SELECT status FROM Parcels WHERE parcel_id = ? AND account_id = ?";
    $stmt_check_parcel = $conn->prepare($sql_check_parcel);
    $stmt_check_parcel->bind_param("ii", $block['parcel_id'], $account_id);
    $stmt_check_parcel->execute();
    $result_check_parcel = $stmt_check_parcel->get_result();

    if ($result_check_parcel->num_rows == 0) {
        $_SESSION['displayMsg'] = "Parent parcel not found.";
        return false;
    }

    $parcel = $result_check_parcel->fetch_assoc();
    if ($parcel['status'] != 'active') {
        $_SESSION['displayMsg'] = "Block cannot be activated because its parent parcel is inactive.";
        return false;
    }

    // Step 3: Update block status to active
    $sql_update_block = "UPDATE Blocks SET status = 'active', status_notes = NULL WHERE block_id = ? AND account_id = ?";
    $stmt_update_block = $conn->prepare($sql_update_block);
    $stmt_update_block->bind_param("ii", $block_id, $account_id);

    if ($stmt_update_block->execute()) {
        $_SESSION['displayMsg'] = "Block activated successfully.";
        return true;
    } else {
        $_SESSION['displayMsg'] = "Error activating block: " . $conn->error;
        return false;
    }
}




// DEACTIVATE USER
function deactivate_user($account_user_id, $data) {
    global $conn;

    $deactivateNotes = trim($data['deactivateNotes']); // Get reason for deactivation
    $sql = "UPDATE Accounts_Users SET status = 'inactive', status_notes = ? WHERE account_user_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['displayMsg'] = "SQL Error: " . $conn->error;
        return false;
    }

    $stmt->bind_param("si", $deactivateNotes, $account_user_id);

    if ($stmt->execute()) {
        $_SESSION['displayMsg'] = "User successfully deactivated.";
        return true;
    } else {
        $_SESSION['displayMsg'] = "Error deactivating user: " . $stmt->error;
        return false;
    }
}

// ACTIVATE USER
function activate_user($account_user_id, $data) {
    global $conn;

    $sql = "UPDATE Accounts_Users SET status = 'active', status_notes = NULL WHERE account_user_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['displayMsg'] = "SQL Error: " . $conn->error;
        return false;
    }

    $stmt->bind_param("i", $account_user_id);

    if ($stmt->execute()) {
        $_SESSION['displayMsg'] = "User successfully activated.";
        return true;
    } else {
        $_SESSION['displayMsg'] = "Error activating user: " . $stmt->error;
        return false;
    }
}





// UPDATE PROFILE
function update_profile($account_id, $data) {
    global $conn;

    // Sanitize and extract data
    $business_name = trim($data['business_name']);
    $business_phone = trim($data['business_phone']);
    $street_address = trim($data['street_address']);
    $city = trim($data['city']);
    $state = trim($data['state']);
    $zip = trim($data['zip']);
    $billing_address = trim($data['billing_address']);
    $billing_city = trim($data['billing_city']);
    $billing_state = trim($data['billing_state']);
    $billing_zip = trim($data['billing_zip']);
    $acreage_size = trim($data['acreage_size']);

    // Update account details
    $sql_update = "UPDATE Accounts 
                   SET business_name = ?, business_phone = ?, 
                       street_address = ?, city = ?, state = ?, zip = ?, 
                       billing_address = ?, billing_city = ?, billing_state = ?, billing_zip = ?, 
                       acreage_size = ? 
                   WHERE account_id = ?";

    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        $_SESSION['displayMsg'] = "Error preparing query: " . $conn->error;
        return false;
    }

    $stmt_update->bind_param(
        "sssssssssssi",
        $business_name, $business_phone, $street_address, $city, $state, $zip, $billing_address, $billing_city, $billing_state, $billing_zip, $acreage_size, $account_id
    );

    if ($stmt_update->execute()) {
        $_SESSION['displayMsg'] = "Profile updated successfully.";
        return true;
    } else {
        $_SESSION['displayMsg'] = "Error updating profile: " . $stmt_update->error;
        return false;
    }
}

// UPDATE USER FUNCTION
function update_user($account_user_id, $account_id, $data) {
    global $conn;

    // Sanitize and extract data
    $first_name = trim($data['contact_first_name']);
    $last_name = trim($data['contact_last_name']);
    $email = trim($data['contact_email']);
    $phone = trim($data['phone']);
    $password = trim($data['password']);

    // Start building the SQL update statement
    $sql_update = "UPDATE Accounts_Users SET 
                        contact_first_name = ?, 
                        contact_last_name = ?, 
                        contact_email = ?, 
                        phone = ?";

    $params = [$first_name, $last_name, $email, $phone];
    $types = "ssss";

    // If a new password is provided, hash it and include it in the update
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_update .= ", password = ?";
        $params[] = $hashed_password;
        $types .= "s";
    }

    // Add WHERE condition
    $sql_update .= " WHERE account_user_id = ? AND account_id = ?";
    $params[] = $account_user_id;
    $params[] = $account_id;
    $types .= "ii";

    // Prepare & execute the query
    $stmt_update = $conn->prepare($sql_update);
    
    if (!$stmt_update) {
        $_SESSION['displayMsg'] = "SQL Error: " . $conn->error;
        return false;
    }

    // Debugging: Check if params and types are correct
    if (count($params) !== strlen($types)) {
        $_SESSION['displayMsg'] = "Parameter count mismatch.";
        return false;
    }

    // Bind parameters dynamically
    $stmt_update->bind_param($types, ...$params);
    
    // Execute the query and check for success
    if ($stmt_update->execute()) {
        $_SESSION['displayMsg'] = "User details updated successfully.";
        return true;
    } else {
        $_SESSION['displayMsg'] = "Error updating user: " . $stmt_update->error;
        return false;
    }
}


function sendWelcomeEmail($recipientEmail, $recipientName) {
    $subject = "Welcome to AgHawk Dynamics!";
	$recipient = $recipientEmail;
    $headers = "From: AgHawk Dynamics <aghawkdynamics@aghawkdynamics.com>\r\n";
    $headers .= "Reply-To: chris@aghawkdynamics.com\r\n";
    $headers .= "BCC: mlisenko5@gmail.com\r\n";
    $headers .= "BCC: patrik@nwwebdev.com\r\n";
    $headers .= "Reply-To: chris@aghawkdynamics.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message = "
            <html>
                <head>
					<link href='https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&amp;family=Recursive:wght@300..1000&amp;display=swap' rel='stylesheet'>
                    <style>
                        body {
							font-size:18px;
							color: #212222;
						}
                        .header {
							font-family: 'Orbitron', sans-serif;
							text-align: center;
						}
                        .content {
							font-family: 'Recursive', sans-serif;
							line-height: 1.6em;
							padding: 20px;
						}
						.header span,
						.content span {
							color:#5e6670;
						}
                    </style>
                </head>
                <body>
                    <div class='header'>
						<img src='https://my.aghawkdynamics.com/images/admin-logo.png' /><br />
                        <h2>Welcome to AG<span>HAWK</span> DYNAMICS</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$recipientName},</p>
                        <p>Thank you for registering with <strong>AG<span>HAWK</span> DYNAMICS</strong>.</p>
                        <p>Your MyAgHawk Customer Portal account has been successfully created. We are excited to have you onboard and look forward to working with you.</p>
						<p>Your Customer Portal is available 24/7 here: <a href='https://my.aghawkdynamics.com/'>https://my.aghawkdynamics.com</a></p>
                        <p>If you need any assistance, feel free to <a href='mailto:chris@aghawkdynamics.com'>email Chris</a> or call us at <a href='tel:509-449-8989'>509-449-8989</a>.</p>
                        <p>Best regards,</p>
                        <p><strong>The Aghawk Dynamics Team</strong></p>
                    </div>
                </body>
            </html>
    ";

    if (mail($recipient, $subject, $message, $headers)) {
        //echo "Email sent successfully to {$recipientEmail}";
    } else {
        echo "Failed to send email.";
    }
}
/**/


function sendTestEmail($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com'; // Replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'help@aghawkdynamics.com'; // Replace with your email
        $mail->Password   = 'W*572563179571ot';    // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Debugging and optional SSL bypass
        $mail->SMTPDebug = 3; // Verbose debug output
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
		
        // Recipients
        $mail->setFrom('help@aghawkdynamics.com', 'AgHawk Dynamics'); // Replace with your "from" address
        $mail->addAddress($recipientEmail, $recipientName); // Recipient email and name
		$mail->addBCC('patrik.e8@gmail.com');
		$mail->addReplyTo('chris@aghawkdynamics.com', 'AgHawk Dynamics');

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to AgHawk Dynamics!';
        $mailBody = "
            <html>
                <head>
					<link href='https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&amp;family=Recursive:wght@300..1000&amp;display=swap' rel='stylesheet'>
                    <style>
                        body {
							font-size:18px;
							color: #212222;
						}
                        .header {
							font-family: 'Orbitron', sans-serif;
							text-align: center;
						}
                        .content {
							font-family: 'Recursive', sans-serif;
							line-height: 1.6em;
							padding: 20px;
						}
						.header span,
						.content span {
							color:#5e6670;
						}
                    </style>
                </head>
                <body>
                    <div class='header'>
						<img src='https://my.aghawkdynamics.com/images/admin-logo.png' /><br />
                        <h2>Welcome to AG<span>HAWK</span> DYNAMICS</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$recipientName},</p>
                        <p>Thank you for registering with <strong>AG<span>HAWK</span> DYNAMICS</strong>.</p>
                        <p>Your MyAgHawk Customer Portal account has been successfully created. We are excited to have you onboard and look forward to working with you.</p>
						<p>Your Customer Portal is available 24/7 here: <a href='https://my.aghawkdynamics.com/'>https://my.aghawkdynamics.com</a></p>
                        <p>If you need any assistance, feel free to <a href='mailto:chris@aghawkdynamics.com'>email Chris</a> or call us at <a href='tel:509-449-8989'>509-449-8989</a>.</p>
                        <p>Best regards,</p>
                        <p><strong>The Aghawk Dynamics Team</strong></p>
                    </div>
                </body>
            </html>
        ";

        $mail->Body = $mailBody;
        $mail->send();
		echo "mail sent.";
    } catch (Exception $e) {
        // Log the error
        //error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
		mail('patrik.e8@gmail.com','aghawk email error', "Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
} //end sendWelcomeEmail()

/* UNTIL SMPT INFO IS WORKING, USE A MORE SIMPLE FUNCTION, ABOVE
function sendWelcomeEmail($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.aghawkdynamics.com'; // Replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'help@aghawkdynamics.com'; // Replace with your email
        $mail->Password   = 'Myportal2024$';    // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        // Recipients
        $mail->setFrom('help@aghawkdynamics.com', 'AgHawk Dynamics'); // Replace with your "from" address
        $mail->addAddress($recipientEmail, $recipientName); // Recipient email and name
		$mail->addBCC('patrik.e8@gmail.com');
		$mail->addReplyTo('chris@aghawkdynamics.com', 'AgHawk Dynamics');

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to AgHawk Dynamics!';
        $mailBody = "
            <html>
                <head>
					<link href='https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&amp;family=Recursive:wght@300..1000&amp;display=swap' rel='stylesheet'>
                    <style>
                        body {
							font-size:18px;
							color: #212222;
						}
                        .header {
							font-family: 'Orbitron', sans-serif;
							text-align: center;
						}
                        .content {
							font-family: 'Recursive', sans-serif;
							line-height: 1.6em;
							padding: 20px;
						}
						.header span,
						.content span {
							color:#5e6670;
						}
                    </style>
                </head>
                <body>
                    <div class='header'>
						<img src='https://my.aghawkdynamics.com/images/admin-logo.png' /><br />
                        <h2>Welcome to AG<span>HAWK</span> DYNAMICS</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$recipientName},</p>
                        <p>Thank you for registering with <strong>AG<span>HAWK</span> DYNAMICS</strong>.</p>
                        <p>Your MyAgHawk Customer Portal account has been successfully created. We are excited to have you onboard and look forward to working with you.</p>
						<p>Your Customer Portal is available 24/7 here: <a href='https://my.aghawkdynamics.com/'>https://my.aghawkdynamics.com</a></p>
                        <p>If you need any assistance, feel free to <a href='mailto:chris@aghawkdynamics.com'>email Chris</a> or call us at <a href='tel:509-449-8989'>509-449-8989</a>.</p>
                        <p>Best regards,</p>
                        <p><strong>The Aghawk Dynamics Team</strong></p>
                    </div>
                </body>
            </html>
        ";

        $mail->Body = $mailBody;
        $mail->send();
    } catch (Exception $e) {
        // Log the error
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
		mail('patrik.e8@gmail.com','aghawk email error', "Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
} //end sendWelcomeEmail()
/**/



//ADD SERVICE REQUEST
function add_service_request($account_id, $parcel_id, $block_id, $data, $service_type, $frequency = null, $recurrence_end_date = null) {
    global $conn;

    // Sanitize and extract data
    $contact_id = isset($data['contact_id']) ? intval($data['contact_id']) : null;
    $reason_for_application = $data['reason_for_application'] ?? '';
    $type_of_service = $data['type_of_service'] ?? '';
    $type_of_product = $data['type_of_product'] ?? null;
    $product_name = $data['product_name'] ?? null;
    $supplier_name = $data['supplier_name'] ?? null;
    $supplier_contact_phone = $data['supplier_contact_phone'] ?? null;
    $application_need_by_date = $data['application_need_by_date'] ?? '';
    $urgent_request = $data['urgent_request'] ?? null;
    $urgent_request_date = $data['urgent_request_date'] ?? null;
    $comments = $data['comments'] ?? '';

    // Step 1: Insert into ServiceRequests (initially without recurrence_id)
    $sql_insert = "INSERT INTO ServiceRequests (
        account_id, parcel_id, block_id, contact_id, reason_for_application, type_of_service,
        type_of_product, product_name, supplier_name, supplier_contact_phone, application_need_by_date, urgent_request, urgent_request_date,
        service_type, frequency, comments, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($sql_insert);
    if ($stmt_insert === false) {
        error_log("Error preparing SQL for ServiceRequests: " . $conn->error);
        die("Internal Server Error");
    }

    // Bind parameters (without recurrence_end_date and recurrence_id initially)
    $stmt_insert->bind_param(
        "iiisssssssssssss",
        $account_id, $parcel_id, $block_id, $contact_id,
        $reason_for_application, $type_of_service, $type_of_product,
        $product_name, $supplier_name, $supplier_contact_phone,
        $application_need_by_date, $urgent_request, $urgent_request_date,
        $service_type, $frequency, $comments
    );

    if ($stmt_insert->execute()) {
        // Step 2: Get the newly inserted service_request_id
        $service_request_id = $conn->insert_id;

        // Step 3: Generate and update unique_id
        $unique_id = "SR_" . str_pad($service_request_id, 11, "0", STR_PAD_LEFT);
        $sql_update_unique_id = "UPDATE ServiceRequests SET unique_id = ? WHERE service_request_id = ?";
        $stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
        $stmt_update_unique_id->bind_param("si", $unique_id, $service_request_id);
        $stmt_update_unique_id->execute();

        // Step 4: Insert into RecurringPatterns if it's a recurring service
        $recurrence_id = null;
        if ($service_type === 'recurring' && $frequency) {
            $sql_recurring = "INSERT INTO RecurringPatterns (account_id, parcel_id, block_id, service_request_id, frequency, recurrence_end_date, created_at)
                              VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt_recurring = $conn->prepare($sql_recurring);

            if ($stmt_recurring === false) {
                error_log("Error preparing SQL for RecurringPatterns: " . $conn->error);
                die("Internal Server Error");
            }

            $stmt_recurring->bind_param("iiiiss", $account_id, $parcel_id, $block_id, $service_request_id, $frequency, $recurrence_end_date);
            if ($stmt_recurring->execute()) {
                // Step 5: Get the recurrence_id
                $recurrence_id = $conn->insert_id;
            }
        }

        // Step 6: Update the ServiceRequests table with recurrence_id and recurrence_end_date
        if ($recurrence_id !== null) {
            $sql_update_recurrence = "UPDATE ServiceRequests SET recurrence_id = ?, recurrence_end_date = ? WHERE service_request_id = ?";
            $stmt_update_recurrence = $conn->prepare($sql_update_recurrence);
            if ($stmt_update_recurrence === false) {
                error_log("Error preparing SQL for updating recurrence_id: " . $conn->error);
                die("Internal Server Error");
            }
            $stmt_update_recurrence->bind_param("isi", $recurrence_id, $recurrence_end_date, $service_request_id);
            $stmt_update_recurrence->execute();
        }

        $_SESSION['displayMsg'] = "New Service Request created! We will contact you with scheduling shortly.";
    } else {
        error_log("Error executing SQL for ServiceRequests: " . $stmt_insert->error);
        die("Internal Server Error");
    }
}


//ADD SELF SERVICE REQUEST
function add_self_service_request($account_id, $parcel_id, $block_id, $data, $service_type, $frequency = null, $recurrence_end_date = null) {
    global $conn;

    // Sanitize and extract data
    $contact_id = isset($data['contact_id']) ? intval($data['contact_id']) : null;
    $reason_for_application = $data['reason_for_application'] ?? '';
    $type_of_service = $data['type_of_service'] ?? '';
    $type_of_product = $data['type_of_product'] ?? null;
    $product_name = $data['product_name'] ?? null;
    $supplier_name = $data['supplier_name'] ?? null;
    $supplier_contact_phone = $data['supplier_contact_phone'] ?? null;
    $application_need_by_date = $data['application_need_by_date'] ?? '';
    $comments = $data['comments'] ?? '';

    // Step 1: Insert into ServiceRequests (initially without recurrence_id)
    $sql_insert = "INSERT INTO SelfServiceRequests (
        account_id, parcel_id, block_id, contact_id, reason_for_application, type_of_service,
        type_of_product, product_name, supplier_name, supplier_contact_phone, application_need_by_date, 
        service_type, frequency, comments, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($sql_insert);
    if ($stmt_insert === false) {
        error_log("Error preparing SQL for ServiceRequests: " . $conn->error);
        die("Internal Server Error line 1194");
    }

    // Bind parameters (without recurrence_end_date and recurrence_id initially)
    $stmt_insert->bind_param(
        "iiisssssssssss",
        $account_id, $parcel_id, $block_id, $contact_id,
        $reason_for_application, $type_of_service, $type_of_product,
        $product_name, $supplier_name, $supplier_contact_phone,
        $application_need_by_date, 
        $service_type, $frequency, $comments
    );

    if ($stmt_insert->execute()) {
        // Step 2: Get the newly inserted service_request_id
        $self_service_request_id = $conn->insert_id;

        // Step 3: Generate and update unique_id
        $unique_id = "SR_" . str_pad($service_request_id, 11, "0", STR_PAD_LEFT);
        $sql_update_unique_id = "UPDATE SelfServiceRequests SET unique_id = ? WHERE service_request_id = ?";
        $stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
        $stmt_update_unique_id->bind_param("si", $unique_id, $service_request_id);
        $stmt_update_unique_id->execute();

        // Step 4: Insert into RecurringPatterns if it's a recurring service
        $recurrence_id = null;
        if ($service_type === 'recurring' && $frequency) {
            $sql_recurring = "INSERT INTO SelfRecurringPatterns (account_id, parcel_id, block_id, service_request_id, frequency, recurrence_end_date, created_at)
                              VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt_recurring = $conn->prepare($sql_recurring);

            if ($stmt_recurring === false) {
                error_log("Error preparing SQL for SelfRecurringPatterns: " . $conn->error);
                die("Internal Server Error line 1227");
            }

            $stmt_recurring->bind_param("iiiiss", $account_id, $parcel_id, $block_id, $service_request_id, $frequency, $recurrence_end_date);
            if ($stmt_recurring->execute()) {
                // Step 5: Get the recurrence_id
                $recurrence_id = $conn->insert_id;
            }
        }

        // Step 6: Update the ServiceRequests table with recurrence_id and recurrence_end_date
        if ($recurrence_id !== null) {
            $sql_update_recurrence = "UPDATE SelfServiceRequests SET recurrence_id = ?, recurrence_end_date = ? WHERE service_request_id = ?";
            $stmt_update_recurrence = $conn->prepare($sql_update_recurrence);
            if ($stmt_update_recurrence === false) {
                error_log("Error preparing SQL for updating recurrence_id: " . $conn->error);
                die("Internal Server Error line 1243");
            }
            $stmt_update_recurrence->bind_param("isi", $recurrence_id, $recurrence_end_date, $service_request_id);
            $stmt_update_recurrence->execute();
        }

        $_SESSION['displayMsg'] = "New Self Tracking Activity created!";
    } else {
        error_log("Error executing SQL for ServiceRequests: " . $stmt_insert->error);
        die("Internal Server Error line 1252");
    }
}


function calculate_next_scheduled_date($frequency, $base_date) {
    $date = new DateTime($base_date);

    switch ($frequency) {
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'bimonthly':
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


// PASSWORD RESET
function forgot_myaghawk_password($email) {
    global $conn;

    // Default message to display regardless of outcome
    $_SESSION['displayMsg'] = "Password reset email has been sent.";

    // Check if the user exists
    $sql = "SELECT user_id, contact_first_name FROM Accounts_Users WHERE contact_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Email not found; return without revealing this fact
        return;
    }

    $user = $result->fetch_assoc();
    $account_user_id = $user['account_user_id'];

    // Generate a secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Insert the token into the database
    $sql_token = "INSERT INTO password_reset_tokens (account_user_id, token, expires_at) VALUES (?, ?, ?)";
    $stmt_token = $conn->prepare($sql_token);
    $stmt_token->bind_param("iss", $account_user_id, $token, $expires_at);
    $stmt_token->execute();

    // Send the reset email
    $reset_link = "https://my.aghawkdynamics.com/password_reset?token={$token}";
    $subject = "Password Reset Request";
    $message = "
        <html>
            <body>
                <p>Hi {$user['contact_first_name']},</p>
                <p>We received a request to reset your password. Click the link below to reset it:</p>
                <p><a href='{$reset_link}'>Reset Password</a> &nbsp; (This link will expire in one hour)</p>
                <p>If you did not request this, please ignore this email.</p>
				<p>Best Regards,<br /><strong>The Aghawk Dynamics team</strong></p>
            </body>
        </html>
    ";
    $headers = "Content-Type: text/html; charset=UTF-8";

    if (!mail($email, $subject, $message, $headers)) {
        $_SESSION['displayMsg'] = "Failed to send email.";
    }
}


function reset_myaghawk_password($token, $new_password) {
    global $conn;

    // Validate the token
    $sql = "SELECT account_user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Invalid or expired token.");
    }

    $row = $result->fetch_assoc();
    $account_user_id = $row['account_user_id'];

    // Update the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql_update = "UPDATE Accounts_Users SET password = ? WHERE account_user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $hashed_password, $account_user_id);

    if ($stmt_update->execute()) {
        // Delete the used token
        $sql_delete_token = "DELETE FROM password_reset_tokens WHERE account_user_id = ?";
        $stmt_delete_token = $conn->prepare($sql_delete_token);
        $stmt_delete_token->bind_param("i", $account_user_id);
        $stmt_delete_token->execute();

        $_SESSION['displayMsg'] = "Password has been reset successfully. Please <a href='index.php'>LOG IN</a> with your new password.";
    } else {
        $_SESSION['displayMsg'] = "Failed to reset password.";
    }
}


ob_end_flush(); // Ensures buffered output is sent properly


?>