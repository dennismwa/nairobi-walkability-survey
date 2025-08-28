<?php
// admin/view_responses.php - Debug Version
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'survey_responses'");
    if ($table_check->num_rows == 0) {
        throw new Exception("survey_responses table does not exist");
    }
    
    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Search functionality
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $where_clause = '';
    if ($search) {
        $search_escaped = $conn->real_escape_string($search);
        $where_clause = "WHERE id LIKE '%$search_escaped%' 
                         OR gender LIKE '%$search_escaped%' 
                         OR age LIKE '%$search_escaped%' 
                         OR education LIKE '%$search_escaped%' 
                         OR occupation LIKE '%$search_escaped%'
                         OR sub_county LIKE '%$search_escaped%'
                         OR ward LIKE '%$search_escaped%'
                         OR estate LIKE '%$search_escaped%'";
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM survey_responses $where_clause";
    $count_result = $conn->query($count_query);
    
    if (!$count_result) {
        throw new Exception("Count query failed: " . $conn->error);
    }
    
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $per_page);
    
    // Get responses with only existing columns
    $query = "SELECT id, gender, age, education, occupation, sub_county, ward, estate, 
                     car_ownership, general_safety, submission_time 
              FROM survey_responses 
              $where_clause 
              ORDER BY submission_time DESC 
              LIMIT $per_page OFFSET $offset";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Main query failed: " . $conn->error);
    }
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Responses - Survey Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Debug Info -->
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 text-sm">
        <strong>Debug:</strong> Total Records: <?php echo $total_records; ?> | Current Page: <?php echo $page; ?> | Total Pages: <?php echo $total_pages; ?>
    </div>

    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="flex-shrink-0 flex items-center">
                        <i class="fas fa-arrow-left text-white text-lg mr-3"></i>
                        <i class="fas fa-chart-line text-white text-2xl mr-3"></i>
                        <span class="text-white text-xl font-bold">Survey Responses</span>
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

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Stats -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Survey Responses</h1>
                    <p class="text-gray-600 mt-2">Showing <?php echo number_format($total_records); ?> total responses</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <a href="export_excel.php?export=all" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-file-excel mr-2"></i>Export All
                    </a>
                    <button onclick="location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-sync mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Responses</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by ID, gender, age, education, location..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex space-x-2 items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="view_responses.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Responses Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">
                        <i class="fas fa-table text-blue-500 mr-2"></i>Response Details
                    </h2>
                    <div class="text-sm text-gray-600">
                        <?php if ($total_pages > 1): ?>
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Demographics</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transport</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Safety Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?php echo $row['id']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="space-y-1">
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo ucfirst(str_replace('_', ' ', $row['gender'] ?? 'N/A')); ?>
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Age: <?php echo ucfirst(str_replace('_', '-', $row['age'] ?? 'N/A')); ?>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Education: <?php echo ucfirst(str_replace('_', ' ', $row['education'] ?? 'N/A')); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="space-y-1">
                                        <?php if ($row['sub_county']): ?>
                                            <div><i class="fas fa-map-marker-alt text-xs mr-1"></i><?php echo htmlspecialchars($row['sub_county']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($row['ward']): ?>
                                            <div class="text-xs text-gray-400">Ward: <?php echo htmlspecialchars($row['ward']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($row['estate']): ?>
                                            <div class="text-xs text-gray-400">Estate: <?php echo htmlspecialchars($row['estate']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="space-y-1">
                                        <div>
                                            <?php if ($row['car_ownership'] == 'yes'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-car mr-1"></i>Has Car
                                                </span>
                                            <?php elseif ($row['car_ownership'] == 'no'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-walking mr-1"></i>No Car
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Occupation: <?php echo ucfirst(str_replace('_', ' ', $row['occupation'] ?? 'N/A')); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    $safety = $row['general_safety'] ?? '';
                                    $safety_colors = [
                                        'very_unsafe' => 'bg-red-100 text-red-800',
                                        'unsafe' => 'bg-orange-100 text-orange-800',
                                        'neutral' => 'bg-yellow-100 text-yellow-800',
                                        'safe' => 'bg-green-100 text-green-800',
                                        'very_safe' => 'bg-green-100 text-green-800'
                                    ];
                                    $safety_class = $safety_colors[$safety] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <?php if ($safety): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $safety_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $safety)); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="space-y-1">
                                        <div><?php echo date('M j, Y', strtotime($row['submission_time'])); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo date('H:i', strtotime($row['submission_time'])); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="view_response.php?id=<?php echo $row['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_response.php?id=<?php echo $row['id']; ?>" 
                                           class="text-green-600 hover:text-green-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteResponse(<?php echo $row['id']; ?>)" 
                                                class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900">No responses found</h3>
                                        <p class="text-gray-500">
                                            <?php if ($search): ?>
                                                Try adjusting your search criteria
                                            <?php else: ?>
                                                Survey responses will appear here once submitted
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium"><?php echo ($offset + 1); ?></span> to 
                                <span class="font-medium"><?php echo min($offset + $per_page, $total_records); ?></span> of 
                                <span class="font-medium"><?php echo $total_records; ?></span> results
                            </p>
                        </div>
                        
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border text-sm font-medium 
                                              <?php echo ($i == $page) ? 'border-blue-500 bg-blue-50 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" 
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
location.reload();
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