<?php
// admin/edit_response.php - Updated Version with New Transport Fields
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$response_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$response_id) {
    header('Location: view_responses.php');
    exit;
}

// Database connection
$servername = "localhost";
$username = "vxjtgclw_nairobi_survey";
$password = "FB=4x?80r=]wK;03";
$dbname = "vxjtgclw_nairobi_survey";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Build update query dynamically based on posted data
        $updates = [];
        $params = [];
        $types = '';
        
        // Basic fields that might exist
        $fields = [
            'gender', 'age', 'education', 'occupation', 'occupation_other', 'income',
            'sub_county', 'ward', 'estate', 'car_ownership', 'bus_usage', 'walking_usage',
            'trip_origin', 'trip_destination', 'transport_mode', 'transport_mode_other',
            'transport_mode_first_mile', 'transport_mode_main_mile', 'transport_mode_last_mile',
            'general_safety', 'accident_concern', 'driver_yield', 'night_safety',
            'walkway_importance', 'obstacles_frequency', 'path_connectivity', 'comfort_satisfaction',
            'barriers_other', 'additional_comments'
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $updates[] = "$field = ?";
                $params[] = $_POST[$field];
                $types .= 's';
            }
        }
        
        // Handle checkbox fields
        if (isset($_POST['residence_privacy'])) {
            $updates[] = "residence_privacy = ?";
            $params[] = 1;
            $types .= 'i';
        } else {
            $updates[] = "residence_privacy = ?";
            $params[] = 0;
            $types .= 'i';
        }
        
        // Handle barriers array
        if (isset($_POST['barriers']) && is_array($_POST['barriers'])) {
            $barriers = implode(',', $_POST['barriers']);
            $updates[] = "barriers = ?";
            $params[] = $barriers;
            $types .= 's';
        }
        
        if (!empty($updates)) {
            $sql = "UPDATE survey_responses SET " . implode(', ', $updates) . " WHERE id = ?";
            $params[] = $response_id;
            $types .= 'i';
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $message = "Response updated successfully!";
            } else {
                throw new Exception('Failed to update response: ' . $stmt->error);
            }
        }
        
    } catch (Exception $e) {
        $error = "Error updating response: " . $e->getMessage();
    }
}

// Get current response data
$query = "SELECT * FROM survey_responses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $response_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: view_responses.php');
    exit;
}

$response = $result->fetch_assoc();

// Helper function to check if option is selected
function isSelected($current_value, $option_value) {
    return $current_value === $option_value ? 'checked' : '';
}

function isCheckedInArray($array_string, $value) {
    if (empty($array_string)) return '';
    $array = explode(',', $array_string);
    return in_array(trim($value), array_map('trim', $array)) ? 'checked' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Response #<?php echo $response_id; ?> - Survey Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="view_response.php?id=<?php echo $response_id; ?>" class="flex-shrink-0 flex items-center text-white hover:text-gray-200">
                        <i class="fas fa-arrow-left text-lg mr-3"></i>
                        <i class="fas fa-edit text-2xl mr-3"></i>
                        <span class="text-xl font-bold">Edit Response #<?php echo $response_id; ?></span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-white">
                        <i class="fas fa-user-circle mr-2"></i>
                        <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                    </div>
                    <a href="logout.php" class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition-all duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Status Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Survey Response</h1>
            <p class="text-gray-600 mt-2">
                Originally submitted on <?php echo date('F j, Y \a\t g:i A', strtotime($response['submission_time'])); ?>
            </p>
        </div>

        <!-- Edit Form -->
        <form method="POST" class="space-y-8">
            
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-3"></i>
                    Basic Information
                </h2>
                
                <!-- Gender -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Gender:</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="gender" value="male" class="mr-2" <?php echo isSelected($response['gender'], 'male'); ?>>
                            <span>Male</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="gender" value="female" class="mr-2" <?php echo isSelected($response['gender'], 'female'); ?>>
                            <span>Female</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="gender" value="other" class="mr-2" <?php echo isSelected($response['gender'], 'other'); ?>>
                            <span>Other</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="gender" value="prefer_not_say" class="mr-2" <?php echo isSelected($response['gender'], 'prefer_not_say'); ?>>
                            <span>Prefer not to say</span>
                        </label>
                    </div>
                </div>
                
                <!-- Age -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Age Group:</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="age" value="below_18" class="mr-2" <?php echo isSelected($response['age'], 'below_18'); ?>>
                            <span>Below 18 years</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="age" value="18_25" class="mr-2" <?php echo isSelected($response['age'], '18_25'); ?>>
                            <span>18–25 years</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="age" value="26_35" class="mr-2" <?php echo isSelected($response['age'], '26_35'); ?>>
                            <span>26–35 years</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="age" value="36_45" class="mr-2" <?php echo isSelected($response['age'], '36_45'); ?>>
                            <span>36–45 years</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="age" value="46_60" class="mr-2" <?php echo isSelected($response['age'], '46_60'); ?>>
                            <span>46–60 years</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="age" value="over_60" class="mr-2" <?php echo isSelected($response['age'], 'over_60'); ?>>
                            <span>Over 60 years</span>
                        </label>
                    </div>
                </div>
                
                <!-- Education -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Education Level:</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="education" value="no_formal" class="mr-2" <?php echo isSelected($response['education'], 'no_formal'); ?>>
                            <span>No formal education</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="education" value="primary" class="mr-2" <?php echo isSelected($response['education'], 'primary'); ?>>
                            <span>Primary School</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="education" value="secondary" class="mr-2" <?php echo isSelected($response['education'], 'secondary'); ?>>
                            <span>Secondary School / High School</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="education" value="technical" class="mr-2" <?php echo isSelected($response['education'], 'technical'); ?>>
                            <span>Technical/Vocational College</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="education" value="university" class="mr-2" <?php echo isSelected($response['education'], 'university'); ?>>
                            <span>University (Bachelor's degree)</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="education" value="postgraduate" class="mr-2" <?php echo isSelected($response['education'], 'postgraduate'); ?>>
                            <span>Postgraduate (Master's/PhD)</span>
                        </label>
                    </div>
                </div>
                
                <!-- Text Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sub-County</label>
                        <input type="text" name="sub_county" value="<?php echo htmlspecialchars($response['sub_county'] ?? ''); ?>" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ward</label>
                        <input type="text" name="ward" value="<?php echo htmlspecialchars($response['ward'] ?? ''); ?>" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Car Ownership -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Car Ownership:</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="car_ownership" value="yes" class="mr-2" <?php echo isSelected($response['car_ownership'], 'yes'); ?>>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="car_ownership" value="no" class="mr-2" <?php echo isSelected($response['car_ownership'], 'no'); ?>>
                            <span>No</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Transport Modes Section -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-route text-purple-600 mr-3"></i>
                    Transport Modes
                </h2>
                
                <!-- First Mile Transport -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">First Mile Transport Mode:</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_first_mile" value="walking" class="mr-2" <?php echo isSelected($response['transport_mode_first_mile'], 'walking'); ?>>
                            <span>Walking</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_first_mile" value="bicycle" class="mr-2" <?php echo isSelected($response['transport_mode_first_mile'], 'bicycle'); ?>>
                            <span>Bicycle</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_first_mile" value="matatu_bus" class="mr-2" <?php echo isSelected($response['transport_mode_first_mile'], 'matatu_bus'); ?>>
                            <span>Matatu/Bus</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_first_mile" value="personal_car" class="mr-2" <?php echo isSelected($response['transport_mode_first_mile'], 'personal_car'); ?>>
                            <span>Personal Car</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_first_mile" value="motorcycle" class="mr-2" <?php echo isSelected($response['transport_mode_first_mile'], 'motorcycle'); ?>>
                            <span>Motorcycle/Boda-boda</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_first_mile" value="taxi" class="mr-2" <?php echo isSelected($response['transport_mode_first_mile'], 'taxi'); ?>>
                            <span>Taxi/Ride-sharing</span>
                        </label>
                    </div>
                </div>
                
                <!-- Main Mile Transport -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Main Mile Transport Mode:</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_main_mile" value="walking" class="mr-2" <?php echo isSelected($response['transport_mode_main_mile'], 'walking'); ?>>
                            <span>Walking</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_main_mile" value="bicycle" class="mr-2" <?php echo isSelected($response['transport_mode_main_mile'], 'bicycle'); ?>>
                            <span>Bicycle</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_main_mile" value="matatu_bus" class="mr-2" <?php echo isSelected($response['transport_mode_main_mile'], 'matatu_bus'); ?>>
                            <span>Matatu/Bus</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_main_mile" value="personal_car" class="mr-2" <?php echo isSelected($response['transport_mode_main_mile'], 'personal_car'); ?>>
                            <span>Personal Car</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_main_mile" value="motorcycle" class="mr-2" <?php echo isSelected($response['transport_mode_main_mile'], 'motorcycle'); ?>>
                            <span>Motorcycle/Boda-boda</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_main_mile" value="taxi" class="mr-2" <?php echo isSelected($response['transport_mode_main_mile'], 'taxi'); ?>>
                            <span>Taxi/Ride-sharing</span>
                        </label>
                    </div>
                </div>
                
                <!-- Last Mile Transport -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Last Mile Transport Mode:</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_last_mile" value="walking" class="mr-2" <?php echo isSelected($response['transport_mode_last_mile'], 'walking'); ?>>
                            <span>Walking</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_last_mile" value="bicycle" class="mr-2" <?php echo isSelected($response['transport_mode_last_mile'], 'bicycle'); ?>>
                            <span>Bicycle</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_last_mile" value="matatu_bus" class="mr-2" <?php echo isSelected($response['transport_mode_last_mile'], 'matatu_bus'); ?>>
                            <span>Matatu/Bus</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_last_mile" value="personal_car" class="mr-2" <?php echo isSelected($response['transport_mode_last_mile'], 'personal_car'); ?>>
                            <span>Personal Car</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_last_mile" value="motorcycle" class="mr-2" <?php echo isSelected($response['transport_mode_last_mile'], 'motorcycle'); ?>>
                            <span>Motorcycle/Boda-boda</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="transport_mode_last_mile" value="taxi" class="mr-2" <?php echo isSelected($response['transport_mode_last_mile'], 'taxi'); ?>>
                            <span>Taxi/Ride-sharing</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Safety Questions -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-shield-alt text-green-600 mr-3"></i>
                    Safety Perception
                </h2>
                
                <!-- General Safety -->
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">How safe do you feel walking in Nairobi?</label>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <label class="flex items-center p-3 bg-red-50 rounded-lg cursor-pointer hover:bg-red-100">
                            <input type="radio" name="general_safety" value="very_unsafe" class="mr-2" <?php echo isSelected($response['general_safety'], 'very_unsafe'); ?>>
                            <span>Very unsafe</span>
                        </label>
                        <label class="flex items-center p-3 bg-orange-50 rounded-lg cursor-pointer hover:bg-orange-100">
                            <input type="radio" name="general_safety" value="unsafe" class="mr-2" <?php echo isSelected($response['general_safety'], 'unsafe'); ?>>
                            <span>Unsafe</span>
                        </label>
                        <label class="flex items-center p-3 bg-yellow-50 rounded-lg cursor-pointer hover:bg-yellow-100">
                            <input type="radio" name="general_safety" value="neutral" class="mr-2" <?php echo isSelected($response['general_safety'], 'neutral'); ?>>
                            <span>Neutral</span>
                        </label>
                        <label class="flex items-center p-3 bg-green-50 rounded-lg cursor-pointer hover:bg-green-100">
                            <input type="radio" name="general_safety" value="safe" class="mr-2" <?php echo isSelected($response['general_safety'], 'safe'); ?>>
                            <span>Safe</span>
                        </label>
                        <label class="flex items-center p-3 bg-green-50 rounded-lg cursor-pointer hover:bg-green-100">
                            <input type="radio" name="general_safety" value="very_safe" class="mr-2" <?php echo isSelected($response['general_safety'], 'very_safe'); ?>>
                            <span>Very safe</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Walking Barriers -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                    Walking Barriers
                </h2>
                
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Main barriers to walking (select all that apply):</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="barriers[]" value="poor_sidewalk" class="mr-2" <?php echo isCheckedInArray($response['barriers'], 'poor_sidewalk'); ?>>
                            <span>Poor sidewalk condition</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="barriers[]" value="lack_shade" class="mr-2" <?php echo isCheckedInArray($response['barriers'], 'lack_shade'); ?>>
                            <span>Lack of shade or shelter</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="barriers[]" value="unsafe_crossings" class="mr-2" <?php echo isCheckedInArray($response['barriers'], 'unsafe_crossings'); ?>>
                            <span>Unsafe road crossings</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="barriers[]" value="crime_concerns" class="mr-2" <?php echo isCheckedInArray($response['barriers'], 'crime_concerns'); ?>>
                            <span>Crime or harassment concerns</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="barriers[]" value="poor_lighting" class="mr-2" <?php echo isCheckedInArray($response['barriers'], 'poor_lighting'); ?>>
                            <span>Poor street lighting</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="checkbox" name="barriers[]" value="vehicle_speeds" class="mr-2" <?php echo isCheckedInArray($response['barriers'], 'vehicle_speeds'); ?>>
                            <span>High vehicle speeds</span>
                        </label>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Other Barriers</label>
                    <input type="text" name="barriers_other" value="<?php echo htmlspecialchars($response['barriers_other'] ?? ''); ?>" 
                           placeholder="Other barriers (please specify)" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Additional Comments -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-comments text-blue-600 mr-3"></i>
                    Additional Comments
                </h2>
                
                <div class="mb-6">
                    <label class="block text-lg font-semibold text-gray-800 mb-3">
                        Additional comments or suggestions:
                    </label>
                    <textarea name="additional_comments" rows="6" 
                              placeholder="Your insights are valuable..." 
                              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"><?php echo htmlspecialchars($response['additional_comments'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center bg-white rounded-xl shadow-lg p-6">
                <a href="view_response.php?id=<?php echo $response_id; ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['gender', 'age', 'education'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.querySelector(`input[name="${field}"]:checked`);
                if (!input) {
                    isValid = false;
                    alert(`Please select a ${field.replace('_', ' ')}.`);
                    return false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>