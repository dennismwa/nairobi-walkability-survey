<?php
// admin/dashboard.php - Clean Production Version
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php.php.php');
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
    
    // Get statistics with error handling
    $total_responses = 0;
    $today_responses = 0;
    $this_week_responses = 0;
    $this_month_responses = 0;
    
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses");
        if ($result) {
            $total_responses = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Silent error handling
    }
    
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE DATE(submission_time) = CURDATE()");
        if ($result) {
            $today_responses = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Silent error handling
    }
    
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE WEEK(submission_time) = WEEK(NOW())");
        if ($result) {
            $this_week_responses = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Silent error handling
    }
    
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE MONTH(submission_time) = MONTH(NOW()) AND YEAR(submission_time) = YEAR(NOW())");
        if ($result) {
            $this_month_responses = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Silent error handling
    }
    
    // Recent submissions with error handling
    $recent_results = null;
    try {
        $recent_query = "SELECT id, gender, age, education, submission_time FROM survey_responses ORDER BY submission_time DESC LIMIT 10";
        $recent_results = $conn->query($recent_query);
    } catch (Exception $e) {
        // Silent error handling
    }
    
} catch (Exception $e) {
    die("System temporarily unavailable. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Dashboard - Nairobi Walkability Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-chart-line text-white text-2xl mr-3"></i>
                        <span class="text-white text-xl font-bold">Survey Dashboard</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-white">
                        <i class="fas fa-user-circle mr-2"></i>
                        Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                    </div>
                    <a href="logout.php" class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition-all duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Nairobi Walkability Survey Dashboard</h1>
            <p class="text-gray-600 mt-2">Monitor survey responses and analyze data insights</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6 hover-lift">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Responses</h3>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($total_responses); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 hover-lift">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Today</h3>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($today_responses); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 hover-lift">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-calendar-week text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">This Week</h3>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($this_week_responses); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 hover-lift">
                <div class="flex items-center">
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">This Month</h3>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($this_month_responses); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="view_responses.php" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg border border-blue-200 transition-colors duration-200 group">
                    <div class="flex items-center">
                        <i class="fas fa-eye text-blue-600 text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="ml-3 text-blue-800 font-medium">View All Responses</span>
                    </div>
                </a>
                
                <a href="export_excel.php" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg border border-green-200 transition-colors duration-200 group">
                    <div class="flex items-center">
                        <i class="fas fa-file-excel text-green-600 text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="ml-3 text-green-800 font-medium">Export to Excel</span>
                    </div>
                </a>
                
                <a href="generate_report.php" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg border border-purple-200 transition-colors duration-200 group">
                    <div class="flex items-center">
                        <i class="fas fa-chart-pie text-purple-600 text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="ml-3 text-purple-800 font-medium">Generate Report</span>
                    </div>
                </a>
                
                <button onclick="location.reload()" class="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg border border-orange-200 transition-colors duration-200 group text-left">
                    <div class="flex items-center">
                        <i class="fas fa-sync-alt text-orange-600 text-lg group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="ml-3 text-orange-800 font-medium">Refresh Data</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Recent Submissions Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">
                    <i class="fas fa-clock text-blue-500 mr-2"></i>Recent Submissions
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Education</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($recent_results && $recent_results->num_rows > 0): ?>
                            <?php while ($row = $recent_results->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?php echo $row['id']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['gender'] ?? 'N/A')); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo ucfirst(str_replace('_', '-', $row['age'] ?? 'N/A')); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo ucfirst(str_replace('_', ' ', $row['education'] ?? 'N/A')); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M j, Y H:i', strtotime($row['submission_time'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_response.php?id=<?php echo $row['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                    <a href="edit_response.php?id=<?php echo $row['id']; ?>" 
                                       class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900">No survey responses yet</h3>
                                        <p class="text-gray-500">Survey responses will appear here once submitted</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($recent_results && $recent_results->num_rows > 0): ?>
            <div class="px-6 py-4 border-t border-gray-200 text-center">
                <a href="view_responses.php" class="text-blue-600 hover:text-blue-800 font-medium">
                    View All Responses <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>

<?php if (isset($conn)) $conn->close(); ?>