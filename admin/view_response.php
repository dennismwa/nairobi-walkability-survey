<?php
// admin/view_response.php - Individual Response Detail View
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get response ID
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

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get the specific response
    $query = "SELECT * FROM survey_responses WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $response_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        throw new Exception("Response not found");
    }
    
    $response = $result->fetch_assoc();
    
} catch (Exception $e) {
    die("Error loading response: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Response #<?php echo $response['id']; ?> - Survey Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .gradient-bg { background: #667eea !important; }
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="view_responses.php" class="flex-shrink-0 flex items-center text-white hover:text-gray-200">
                        <i class="fas fa-arrow-left text-lg mr-3"></i>
                        <i class="fas fa-eye text-2xl mr-3"></i>
                        <span class="text-xl font-bold">Response #<?php echo $response['id']; ?></span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button onclick="window.print()" class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition-all duration-200">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
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

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Response Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Response Details</h1>
                    <p class="text-gray-600 mt-2">
                        Response ID: #<?php echo $response['id']; ?> | 
                        Submitted: <?php echo date('F j, Y \a\t g:i A', strtotime($response['submission_time'])); ?>
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="edit_response.php?id=<?php echo $response['id']; ?>" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <button onclick="deleteResponse(<?php echo $response['id']; ?>)" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-user text-blue-600 mr-3"></i>
                Personal Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo ucfirst(str_replace('_', ' ', $response['gender'] ?? 'Not specified')); ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Age Group</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo ucfirst(str_replace('_', '-', $response['age'] ?? 'Not specified')); ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo ucfirst(str_replace('_', ' ', $response['education'] ?? 'Not specified')); ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo ucfirst(str_replace('_', ' ', $response['occupation'] ?? 'Not specified')); ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Income</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php 
                        $income = $response['monthly_income'] ?? '';
                        if ($income) {
                            echo ucfirst(str_replace('_', ' ', $income));
                        } else {
                            echo 'Not specified';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Household Size</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo $response['household_size'] ?? 'Not specified'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-map-marker-alt text-green-600 mr-3"></i>
                Location Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sub County</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($response['sub_county'] ?? 'Not specified'); ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ward</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($response['ward'] ?? 'Not specified'); ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estate/Area</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo htmlspecialchars($response['estate'] ?? 'Not specified'); ?>
                    </div>
                </div>
            </div>
        </div>


        <!-- Transportation & Walking Habits -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-walking text-purple-600 mr-3"></i>
                Transportation & Walking Habits
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Car Ownership</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php 
                        if ($response['car_ownership'] == 'yes') {
                            echo '<span class="text-green-600"><i class="fas fa-car mr-2"></i>Owns a car</span>';
                        } elseif ($response['car_ownership'] == 'no') {
                            echo '<span class="text-red-600"><i class="fas fa-walking mr-2"></i>No car</span>';
                        } else {
                            echo 'Not specified';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Walking Frequency</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo ucfirst(str_replace('_', ' ', $response['walking_usage'] ?? 'Not specified')); ?>
                    </div>
                </div>
            </div>
            
            <!-- Transport Mode Details -->
            <?php if (!empty($response['transport_mode_first_mile']) || !empty($response['transport_mode_main_mile']) || !empty($response['transport_mode_last_mile'])): ?>
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800 mb-4">Transport Mode Breakdown</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="bg-white p-4 rounded-lg">
                            <i class="fas fa-play text-blue-600 text-2xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-700">First Mile</div>
                            <div class="text-lg font-semibold text-gray-900">
                                <?php echo ucfirst(str_replace('_', '/', $response['transport_mode_first_mile'] ?? 'Not specified')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="bg-white p-4 rounded-lg">
                            <i class="fas fa-arrows-alt-h text-green-600 text-2xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-700">Main Mile</div>
                            <div class="text-lg font-semibold text-gray-900">
                                <?php echo ucfirst(str_replace('_', '/', $response['transport_mode_main_mile'] ?? 'Not specified')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="bg-white p-4 rounded-lg">
                            <i class="fas fa-stop text-red-600 text-2xl mb-2"></i>
                            <div class="text-sm font-medium text-gray-700">Last Mile</div>
                            <div class="text-lg font-semibold text-gray-900">
                                <?php echo ucfirst(str_replace('_', '/', $response['transport_mode_last_mile'] ?? 'Not specified')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Legacy Transport Mode (if exists) -->
            <?php if (!empty($response['transport_mode'])): ?>
            <div class="bg-gray-50 p-4 rounded-lg mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Legacy Transport Mode</label>
                <div class="text-lg font-semibold text-gray-900">
                    <?php echo ucfirst(str_replace('_', '/', $response['transport_mode'])); ?>
                    <?php if (!empty($response['transport_mode_other'])): ?>
                        <span class="text-sm text-gray-600"> (<?php echo htmlspecialchars($response['transport_mode_other']); ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        

        <!-- Safety Perceptions -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-shield-alt text-red-600 mr-3"></i>
                Safety Perceptions
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">General Safety Rating</label>
                    <div class="text-lg font-semibold">
                        <?php 
                        $safety = $response['general_safety'] ?? '';
                        $safety_colors = [
                            'very_unsafe' => 'text-red-600',
                            'unsafe' => 'text-orange-600',
                            'neutral' => 'text-yellow-600',
                            'safe' => 'text-green-600',
                            'very_safe' => 'text-green-700'
                        ];
                        $safety_class = $safety_colors[$safety] ?? 'text-gray-900';
                        
                        if ($safety) {
                            echo '<span class="' . $safety_class . '">' . ucfirst(str_replace('_', ' ', $safety)) . '</span>';
                        } else {
                            echo 'Not specified';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Night Safety Rating</label>
                    <div class="text-lg font-semibold">
                        <?php 
                        $night_safety = $response['night_safety'] ?? '';
                        $night_safety_class = $safety_colors[$night_safety] ?? 'text-gray-900';
                        
                        if ($night_safety) {
                            echo '<span class="' . $night_safety_class . '">' . ucfirst(str_replace('_', ' ', $night_safety)) . '</span>';
                        } else {
                            echo 'Not specified';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Walking Barriers -->
        <?php if (!empty($response['barriers'])): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                Walking Barriers
            </h2>
            
            <?php 
            $barriers = explode(',', $response['barriers']);
            $barrier_labels = [
                'poor_sidewalk' => 'Poor sidewalk condition',
                'lack_shade' => 'Lack of shade or shelter',
                'unsafe_crossings' => 'Unsafe road crossings',
                'long_distance' => 'Long distance to bus stops',
                'crime_concerns' => 'Crime or harassment concerns',
                'poor_lighting' => 'Poor street lighting',
                'vehicle_speeds' => 'High vehicle speeds and aggressive driving',
                'lack_signals' => 'Lack of pedestrian signals',
                'no_amenities' => 'Absence of pedestrian amenities',
                'narrow_sidewalks' => 'Narrow/crowded sidewalks'
            ];
            ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($barriers as $barrier): ?>
                    <?php $barrier = trim($barrier); ?>
                    <?php if (!empty($barrier)): ?>
                        <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                            <i class="fas fa-times-circle text-red-500 mr-2"></i>
                            <span class="text-red-700 font-medium">
                                <?php echo $barrier_labels[$barrier] ?? ucfirst(str_replace('_', ' ', $barrier)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Additional Comments -->
        <?php if (!empty($response['additional_comments'])): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-comment text-blue-600 mr-3"></i>
                Additional Comments
            </h2>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-gray-800 leading-relaxed">
                    "<?php echo nl2br(htmlspecialchars($response['additional_comments'])); ?>"
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Response Metadata -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-info-circle text-gray-600 mr-3"></i>
                Response Metadata
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Response ID</label>
                    <div class="text-lg font-semibold text-gray-900">
                        #<?php echo $response['id']; ?>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Submission Time</label>
                    <div class="text-lg font-semibold text-gray-900">
                        <?php echo date('F j, Y \a\t g:i A', strtotime($response['submission_time'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteResponse(id) {
            if (confirm('Are you sure you want to delete this response? This action cannot be undone.')) {
                fetch('delete_response.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({id: id})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'view_responses.php';
                    } else {
                        alert('Error deleting response: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting response');
                });
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>