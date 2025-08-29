<?php
// submit_survey.php - Updated with Location Fields
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "vxjtgclw_nairobi_survey";
$password = "FB=4x?80r=]wK;03";
$dbname = "vxjtgclw_nairobi_survey";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'System temporarily unavailable']);
    exit;
}

// Set charset
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'survey_responses'");
    if ($table_check->num_rows == 0) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'System not properly configured']);
        exit;
    }
    
    // Sanitize and prepare data
    $data = array();
    
    // Basic Information
    $data['gender'] = $_POST['gender'] ?? '';
    $data['age'] = $_POST['age'] ?? '';
    $data['education'] = $_POST['education'] ?? '';
    $data['occupation'] = $_POST['occupation'] ?? '';
    $data['occupation_other'] = $_POST['occupation_other'] ?? '';
    $data['income'] = $_POST['income'] ?? '';
    $data['sub_county'] = $_POST['sub_county'] ?? '';
    $data['ward'] = $_POST['ward'] ?? '';
    $data['estate'] = $_POST['estate'] ?? '';
    
    // NEW: Location fields
    $data['survey_location'] = $_POST['survey_location'] ?? '';
    $data['latitude'] = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $data['longitude'] = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $data['location_accuracy'] = !empty($_POST['location_accuracy']) ? (float)$_POST['location_accuracy'] : null;
    $data['location_method'] = $_POST['location_method'] ?? 'manual';
    
    $data['residence_privacy'] = isset($_POST['residence_privacy']) ? 1 : 0;
    $data['car_ownership'] = $_POST['car_ownership'] ?? '';
    $data['bus_usage'] = $_POST['bus_usage'] ?? '';
    $data['walking_usage'] = $_POST['walking_usage'] ?? '';
    $data['trip_origin'] = $_POST['trip_origin'] ?? '';
    $data['trip_destination'] = $_POST['trip_destination'] ?? '';
    
    // Legacy transport mode
    $data['transport_mode'] = $_POST['transport_mode'] ?? '';
    $data['transport_mode_other'] = $_POST['transport_mode_other'] ?? '';
    
    // NEW: Three new transport mode fields
    $data['transport_mode_first_mile'] = $_POST['first_mile_transport'] ?? '';
    $data['transport_mode_main_mile'] = $_POST['main_mile_transport'] ?? '';
    $data['transport_mode_last_mile'] = $_POST['last_mile_transport'] ?? '';
    
    // WOD Questions
    $data['general_safety'] = $_POST['general_safety'] ?? '';
    $data['accident_concern'] = $_POST['accident_concern'] ?? '';
    $data['driver_yield'] = $_POST['driver_yield'] ?? '';
    $data['night_safety'] = $_POST['night_safety'] ?? '';
    $data['walkway_importance'] = $_POST['walkway_importance'] ?? '';
    $data['obstacles_frequency'] = $_POST['obstacles_frequency'] ?? '';
    $data['path_connectivity'] = $_POST['path_connectivity'] ?? '';
    $data['comfort_satisfaction'] = $_POST['comfort_satisfaction'] ?? '';
    
    // Barriers (array)
    $barriers = isset($_POST['barriers']) ? implode(',', $_POST['barriers']) : '';
    $data['barriers'] = $barriers;
    $data['barriers_other'] = $_POST['barriers_other'] ?? '';
    
    // Additional comments
    $data['additional_comments'] = $_POST['additional_comments'] ?? '';
    
    // Metadata
    $data['submission_time'] = date('Y-m-d H:i:s');
    $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Insert query - Updated with all location fields
    try {
        $sql = "INSERT INTO survey_responses (
            gender, age, education, occupation, occupation_other, income, 
            sub_county, ward, estate, survey_location, latitude, longitude, 
            location_accuracy, location_method, residence_privacy, car_ownership, 
            bus_usage, walking_usage, trip_origin, trip_destination, 
            transport_mode, transport_mode_other, 
            transport_mode_first_mile, transport_mode_main_mile, transport_mode_last_mile,
            general_safety, accident_concern, driver_yield, night_safety, walkway_importance, 
            obstacles_frequency, path_connectivity, comfort_satisfaction, 
            barriers, barriers_other, additional_comments, submission_time, 
            ip_address, user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("System error occurred");
        }
        
        // Updated bind_param with correct types (d for decimal, s for string, i for integer)
        $stmt->bind_param("ssssssssssddssissssssssssssssssssssssss",
            $data['gender'], $data['age'], $data['education'], $data['occupation'], 
            $data['occupation_other'], $data['income'], $data['sub_county'], 
            $data['ward'], $data['estate'], $data['survey_location'], 
            $data['latitude'], $data['longitude'], $data['location_accuracy'], 
            $data['location_method'], $data['residence_privacy'], 
            $data['car_ownership'], $data['bus_usage'], $data['walking_usage'], 
            $data['trip_origin'], $data['trip_destination'], $data['transport_mode'], 
            $data['transport_mode_other'], 
            $data['transport_mode_first_mile'], $data['transport_mode_main_mile'], $data['transport_mode_last_mile'],
            $data['general_safety'], $data['accident_concern'], 
            $data['driver_yield'], $data['night_safety'], $data['walkway_importance'], 
            $data['obstacles_frequency'], $data['path_connectivity'], $data['comfort_satisfaction'], 
            $data['barriers'], $data['barriers_other'], $data['additional_comments'], 
            $data['submission_time'], $data['ip_address'], $data['user_agent']
        );
        
        if ($stmt->execute()) {
            $insert_id = $conn->insert_id;
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Survey submitted successfully',
                'id' => $insert_id
            ]);
        } else {
            throw new Exception('Failed to save survey data');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Unable to save survey. Please try again.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}

$conn->close();
?>