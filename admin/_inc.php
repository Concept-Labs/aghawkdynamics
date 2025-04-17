<?php //ADMIN INC

// ONLY AVAILABLE TO ME DURING DEVELOPMENT
if ($_SERVER['REMOTE_ADDR'] !== '96.46.17.70') {
    //die("Access Restricted");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../wp-load.php'; //LOAD WORDPRESS FUNCTIONS
require_once '_dbconn.php'; // database connection 


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
    $crop_mix_notes = $data['crop_mix_notes'];

    // Insert new account details
    $sql_insert = "INSERT INTO Accounts (business_name, street_address, city, state, zip, business_phone, acreage_size, crop_category, crop_mix_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssssssss", $business_name, $street_address, $city, $state, $zip, $business_phone, $acreage_size, $crop_category, $crop_mix_notes);

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
    $crop_mix_notes = $data['crop_mix_notes'];

    // Update account details
    $sql_update = "UPDATE Accounts SET business_name = ?, business_phone = ?, street_address = ?, city = ?, state = ?, zip = ?, acreage_size = ?, crop_category = ?, crop_mix_notes = ? WHERE account_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssssssi", $business_name, $business_phone, $street_address, $city, $state, $zip, $acreage_size, $crop_category, $crop_mix_notes, $account_id);
    
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
    $crop_category = $data['crop_category'];

    // Insert new parcel details
    $sql_insert = "INSERT INTO Parcels (account_id, nickname, street_address, city, state, zip, acres, latitude, longitude, crop_category, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isssssddss", $account_id, $nickname, $street_address, $city, $state, $zip, $acres, $latitude, $longitude, $crop_category);

    if ($stmt_insert->execute()) {
				
		// Get the last inserted or updated id
		$parcel_id = $conn->insert_id;

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
    $crop_category = $data['crop_category'];

    // Update parcel details
    $sql_update = "UPDATE Parcels SET parcel_number = ?, nickname = ?, street_address = ?, city = ?, state = ?, zip = ?, acres = ?, latitude = ?, longitude = ?, crop_category = ? WHERE parcel_id = ? AND account_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssdsssiii", $parcel_number, $nickname, $street_address, $city, $state, $zip, $acres, $latitude, $longitude, $crop_category, $parcel_id, $account_id);
    
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
    $crop_category = $data['crop_category'];
    $notes = $data['notes'];

    // Insert new block details
    $sql_insert = "INSERT INTO Blocks (parcel_id, nickname, acres, latitude, longitude, crop_category, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isddsss", $parcel_id, $nickname, $acres, $latitude, $longitude, $crop_category, $notes);

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
    $crop_category = $data['crop_category'];
    $notes = $data['notes'];

    // Update block details
    $sql_update = "UPDATE Blocks SET nickname = ?, acres = ?, latitude = ?, longitude = ?, crop_category = ?, notes = ? WHERE block_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sddsssi", $nickname, $acres, $latitude, $longitude, $crop_category, $notes, $block_id);

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
    $supplier_contact_phone = $data['supplier_contact_phone'];
    $application_need_by_date = $data['application_need_by_date'];
    $comments = $data['comments'];

    // Initialize recurrence_id to null for non-recurring requests
    $recurrence_id = null;

    // Insert into RecurringPatterns if the service is recurring
    if ($service_type === 'recurring' && $frequency) {
        $sql_recurring = "INSERT INTO RecurringPatterns (account_id, parcel_id, block_id, frequency, recurrence_end_date, created_at)
                          VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt_recurring = $conn->prepare($sql_recurring);
        
        if ($stmt_recurring === false) {
            die("Error preparing SQL statement for RecurringPatterns: " . $conn->error);
        }

        $stmt_recurring->bind_param("iiiss", $account_id, $parcel_id, $block_id, $frequency, $recurrence_end_date);
        
        if ($stmt_recurring->execute()) {
            // Get the last inserted recurrence_id
            $recurrence_id = $conn->insert_id;
        } else {
            die("Error executing SQL statement for RecurringPatterns: " . $stmt_recurring->error);
        }
    }

    // Prepare the SQL insert statement for ServiceRequests
    $sql_insert = "INSERT INTO ServiceRequests (
        account_id, parcel_id, block_id, contact_id, reason_for_application, type_of_service,
        type_of_product, product_name, supplier_name, supplier_contact_phone, application_need_by_date,
        service_type, frequency, recurrence_end_date, recurrence_id, comments, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt_insert = $conn->prepare($sql_insert);

    // Check if statement preparation failed
    if ($stmt_insert === false) {
        die("Error preparing SQL statement for ServiceRequests: " . $conn->error);
    }

    // Bind parameters
    $stmt_insert->bind_param(
        "iiissssssssssiss",
        $account_id, $parcel_id, $block_id, $contact_id,
        $reason_for_application, $type_of_service, $type_of_product,
        $product_name, $supplier_name, $supplier_contact_phone,
        $application_need_by_date, $service_type, $frequency, $recurrence_end_date,
        $recurrence_id, $comments
    );

    // Execute the statement
    if ($stmt_insert->execute()) {
		
		// Get the last inserted or updated id
		$service_request_id = $conn->insert_id;

		// Update the unique_id
		if ($service_request_id) {
			$unique_id = "SR_" . str_pad($service_request_id, 11, "0", STR_PAD_LEFT);
			$sql_update_unique_id = "UPDATE ServiceRequests SET unique_id = ? WHERE service_request_id = ?";
			$stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
			if ($stmt_update_unique_id === false) {
				die('Prepare failed: ' . htmlspecialchars($conn->error));
			}
			$stmt_update_unique_id->bind_param("si", $unique_id, $service_request_id);
			if (!$stmt_update_unique_id->execute()) {
				die("Error updating unique_id: " . htmlspecialchars($stmt_update_unique_id->error));
			}
		}
		
        // Redirect to the account view page after successful insertion
        if (!headers_sent()) {
            header("Location: ?view=account_view&id=" . intval($account_id));
            exit();
        } else {
            echo "<script>window.location.href='?view=account_view&id=" . intval($account_id) . "';</script>";
            exit();
        }
    } else {
        die("Error executing SQL statement for ServiceRequests: " . $stmt_insert->error);
    }
}
//END ADD SERVICE REQUEST


//UPDATE SERVICE REQUEST
function update_service_request($service_request_id, $data) {
    global $conn;

    // Sanitize and extract data
    $reason_for_application = $data['reason_for_application'];
    $type_of_service = $data['type_of_service'];
    $type_of_product = $data['type_of_product'];
    $product_name = $data['product_name'];
    $supplier_name = $data['supplier_name'];
    $supplier_contact_phone = $data['supplier_contact_phone'];
    $application_need_by_date = $data['application_need_by_date'];
    $scheduled_date = $data['scheduled_date'];
    $status_completed = isset($data['status_completed']) ? 1 : 0;
    $completion_date = date('Y-m-d', strtotime($data['completion_date']));
    $completed_by = $data['completed_by'];
    $wind = $data['wind'];
    $temperature = $data['temperature'];
    $restricted_exposure_hrs = $data['restricted_exposure_hrs'];
    $comments = $data['comments'];

    // Prepare the SQL update statement
    $sql_update = "UPDATE ServiceRequests SET reason_for_application = ?, type_of_service = ?, type_of_product = ?, product_name = ?, supplier_name = ?, supplier_contact_phone = ?, application_need_by_date = ?, scheduled_date = ?, status_completed = ?, completion_date = ?, comments = ? WHERE service_request_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssssssssi", $reason_for_application, $type_of_service, $type_of_product, $product_name, $supplier_name, $supplier_contact_phone, $application_need_by_date, $scheduled_date, $status_completed, $completion_date, $comments, $service_request_id);

    // Execute the update statement
    if ($stmt_update->execute()) {
        // If status_completed is "1", update or insert into the ServiceCompletions table
        if ($status_completed == 1) {
            $sql_completion = "INSERT INTO ServiceCompletions (
                                   service_request_id, 
                                   completion_date, 
                                   completed_by, 
                                   wind, 
                                   temperature, 
                                   restricted_exposure_hrs, 
                                   status, 
                                   created_at, 
                                   updated_at
                               ) 
                               VALUES (?, ?, ?, ?, ?, ?, 'Completed', NOW(), NOW()) 
                               ON DUPLICATE KEY UPDATE 
                                   completion_date = VALUES(completion_date), 
                                   completed_by = VALUES(completed_by), 
                                   wind = VALUES(wind), 
                                   temperature = VALUES(temperature), 
                                   restricted_exposure_hrs = VALUES(restricted_exposure_hrs), 
                                   status = 'Completed', 
                                   updated_at = NOW()";

            $stmt_completion = $conn->prepare($sql_completion);
            if ($stmt_completion === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }

            // Bind parameters to the statement
            $stmt_completion->bind_param("isssss", $service_request_id, $completion_date, $completed_by, $wind, $temperature, $restricted_exposure_hrs);

            if ($stmt_completion->execute()) {
                // Get the last inserted or updated completion_id
                $completion_id = $conn->insert_id;

                // Update the unique_id
                if ($completion_id) {
                    $unique_id = "SRC_" . str_pad($completion_id, 11, "0", STR_PAD_LEFT);
                    $sql_update_unique_id = "UPDATE ServiceCompletions SET unique_id = ? WHERE completion_id = ?";
                    $stmt_update_unique_id = $conn->prepare($sql_update_unique_id);
                    if ($stmt_update_unique_id === false) {
                        die('Prepare failed: ' . htmlspecialchars($conn->error));
                    }
                    $stmt_update_unique_id->bind_param("si", $unique_id, $completion_id);
                    if (!$stmt_update_unique_id->execute()) {
                        die("Error updating unique_id: " . htmlspecialchars($stmt_update_unique_id->error));
                    }
                }
            } else {
                die("Error inserting into ServiceCompletions: " . htmlspecialchars($stmt_completion->error));
            }
        }

        // Redirect to service requests page after successful update
        if (!headers_sent()) {
            header("Location: ?view=service_requests");
            exit();
        } else {
            echo "<script>window.location.href='?view=service_requests';</script>";
            exit();
        }
    } else {
        echo "Error updating service request: " . $conn->error;
    }
}
//END UPDATE SERVICE REQUEST


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



?>