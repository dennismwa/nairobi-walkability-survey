<?php
// admin/generate_report.php - Enhanced Version with Graphs
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
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

    // Get basic statistics with error handling
    $stats = [
        'total_responses' => 0,
        'this_month' => 0,
        'male_count' => 0,
        'female_count' => 0,
        'no_car' => 0,
        'daily_walkers' => 0,
        'feel_safe' => 0
    ];

    // Total responses
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses");
        if ($result) {
            $stats['total_responses'] = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // This month responses
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE MONTH(submission_time) = MONTH(NOW()) AND YEAR(submission_time) = YEAR(NOW())");
        if ($result) {
            $stats['this_month'] = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // Gender distribution
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE gender = 'male'");
        if ($result) {
            $stats['male_count'] = $result->fetch_assoc()['count'];
        }
        
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE gender = 'female'");
        if ($result) {
            $stats['female_count'] = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // Car ownership
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE car_ownership = 'no'");
        if ($result) {
            $stats['no_car'] = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // Daily walkers
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE walking_usage = 'daily'");
        if ($result) {
            $stats['daily_walkers'] = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // Safety perception
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM survey_responses WHERE general_safety IN ('safe', 'very_safe')");
        if ($result) {
            $stats['feel_safe'] = $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // Get detailed data for graphs
    $age_data = [];
    try {
        $result = $conn->query("SELECT age, COUNT(*) as count FROM survey_responses WHERE age IS NOT NULL GROUP BY age ORDER BY FIELD(age, '18-24', '25-34', '35-44', '45-54', '55-64', '65+')");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $age_data[$row['age']] = $row['count'];
            }
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get safety perception distribution
    $safety_data = [];
    try {
        $result = $conn->query("SELECT general_safety, COUNT(*) as count FROM survey_responses WHERE general_safety IS NOT NULL GROUP BY general_safety");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $safety_data[$row['general_safety']] = $row['count'];
            }
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get walking frequency data
    $walking_frequency_data = [];
    try {
        $result = $conn->query("SELECT walking_usage, COUNT(*) as count FROM survey_responses WHERE walking_usage IS NOT NULL GROUP BY walking_usage");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $walking_frequency_data[$row['walking_usage']] = $row['count'];
            }
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get monthly trends
    $monthly_trends = [];
    try {
        $result = $conn->query("SELECT DATE_FORMAT(submission_time, '%Y-%m') as month, COUNT(*) as count FROM survey_responses WHERE submission_time >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(submission_time, '%Y-%m') ORDER BY month");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $monthly_trends[$row['month']] = $row['count'];
            }
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get some detailed data
    $recent_responses = [];
    try {
        $result = $conn->query("SELECT id, gender, age, general_safety, submission_time FROM survey_responses ORDER BY submission_time DESC LIMIT 10");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recent_responses[] = $row;
            }
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get barriers data
    $barriers_data = [];
    try {
        $result = $conn->query("SELECT barriers FROM survey_responses WHERE barriers IS NOT NULL AND barriers != ''");
        if ($result) {
            $barrier_counts = [];
            while ($row = $result->fetch_assoc()) {
                $barriers = explode(',', $row['barriers']);
                foreach ($barriers as $barrier) {
                    $barrier = trim($barrier);
                    if (!empty($barrier)) {
                        $barrier_counts[$barrier] = ($barrier_counts[$barrier] ?? 0) + 1;
                    }
                }
            }
            arsort($barrier_counts);
            $barriers_data = array_slice($barrier_counts, 0, 8, true); // Top 8 barriers for better visualization
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

} catch (Exception $e) {
    die("System Error: Unable to generate report. Please contact administrator.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Analysis Report - Nairobi Walkability Study</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .gradient-bg { background: #667eea !important; }
            .chart-container { break-inside: avoid; }
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }

        .chart-small {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard" class="flex-shrink-0 flex items-center text-white hover:text-gray-200">
                        <i class="fas fa-arrow-left text-lg mr-3"></i>
                        <i class="fas fa-chart-pie text-2xl mr-3"></i>
                        <span class="text-xl font-bold">Analysis Report</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button onclick="window.print()" class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition-all duration-200">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                    <div class="text-white">
                        <i class="fas fa-user-circle mr-2"></i>
                        <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                    </div>
                    <a href="logout" class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition-all duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Report Header -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Nairobi Walkability Survey
            </h1>
            <h2 class="text-2xl text-gray-600 mb-6">
                Analysis Report
            </h2>
            <div class="flex justify-center items-center space-x-8 text-sm text-gray-500">
                <div>
                    <i class="fas fa-calendar mr-2"></i>
                    Generated: <?php echo date('F j, Y'); ?>
                </div>
                <div>
                    <i class="fas fa-database mr-2"></i>
                    Total Responses: <?php echo number_format($stats['total_responses']); ?>
                </div>
                <div>
                    <i class="fas fa-chart-line mr-2"></i>
                    This Month: <?php echo number_format($stats['this_month']); ?>
                </div>
            </div>
        </div>

        <!-- Executive Summary -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-clipboard-list text-blue-600 mr-3"></i>
                Executive Summary
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <div class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['total_responses']); ?></div>
                    <div class="text-blue-800 font-medium">Total Responses</div>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                    <div class="text-3xl font-bold text-green-600">
                        <?php 
                        echo $stats['total_responses'] > 0 ? 
                            round(($stats['feel_safe'] / $stats['total_responses']) * 100, 1) . '%' : '0%';
                        ?>
                    </div>
                    <div class="text-green-800 font-medium">Feel Safe Walking</div>
                </div>
                
                <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                    <div class="text-3xl font-bold text-red-600">
                        <?php 
                        echo $stats['total_responses'] > 0 ? 
                            round(($stats['no_car'] / $stats['total_responses']) * 100, 1) . '%' : '0%';
                        ?>
                    </div>
                    <div class="text-red-800 font-medium">Don't Own Cars</div>
                </div>
                
                <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
                    <div class="text-3xl font-bold text-yellow-600">
                        <?php 
                        echo $stats['total_responses'] > 0 ? 
                            round(($stats['daily_walkers'] / $stats['total_responses']) * 100, 1) . '%' : '0%';
                        ?>
                    </div>
                    <div class="text-yellow-800 font-medium">Walk Daily</div>
                </div>
            </div>
            
            <div class="prose max-w-none text-gray-700">
                <p class="mb-4">
                    This analysis of <?php echo number_format($stats['total_responses']); ?> survey responses reveals critical insights into pedestrian safety and walkability challenges in Nairobi. The study focuses on residents' experiences with walking infrastructure, safety perceptions, and barriers to walkable urban development around BRT Line 3.
                </p>
                
                <?php if ($stats['total_responses'] > 0): ?>
                <p class="mb-4">
                    <strong>Key Findings:</strong> A significant portion of respondents (<?php echo round(($stats['no_car'] / $stats['total_responses']) * 100, 1); ?>%) do not own private vehicles, making walking and public transport essential for daily mobility. 
                    <?php if ($stats['feel_safe'] > 0): ?>
                    However, safety perceptions vary, with <?php echo round(($stats['feel_safe'] / $stats['total_responses']) * 100, 1); ?>% of respondents feeling safe while walking in Nairobi.
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Demographics Analysis -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-users text-purple-600 mr-3"></i>
                Demographic Overview
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Gender Distribution</h3>
                    
                    <?php if ($stats['total_responses'] > 0): ?>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Male</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: <?php echo round(($stats['male_count'] / $stats['total_responses']) * 100, 1); ?>%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">
                                    <?php echo round(($stats['male_count'] / $stats['total_responses']) * 100, 1); ?>%
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Female</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-pink-600 h-2 rounded-full" 
                                         style="width: <?php echo round(($stats['female_count'] / $stats['total_responses']) * 100, 1); ?>%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">
                                    <?php echo round(($stats['female_count'] / $stats['total_responses']) * 100, 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-xs text-gray-500">
                        Total responses: <?php echo number_format($stats['male_count'] + $stats['female_count']); ?>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500">No data available yet.</p>
                    <?php endif; ?>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Transport Patterns</h3>
                    
                    <?php if ($stats['total_responses'] > 0): ?>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Own Car</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" 
                                         style="width: <?php echo round((($stats['total_responses'] - $stats['no_car']) / $stats['total_responses']) * 100, 1); ?>%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">
                                    <?php echo round((($stats['total_responses'] - $stats['no_car']) / $stats['total_responses']) * 100, 1); ?>%
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">No Car</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full" 
                                         style="width: <?php echo round(($stats['no_car'] / $stats['total_responses']) * 100, 1); %>%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">
                                    <?php echo round(($stats['no_car'] / $stats['total_responses']) * 100, 1); ?>%
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Daily Walkers</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" 
                                         style="width: <?php echo round(($stats['daily_walkers'] / $stats['total_responses']) * 100, 1); ?>%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-12 text-right">
                                    <?php echo round(($stats['daily_walkers'] / $stats['total_responses']) * 100, 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500">No data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Data Visualization Section -->
        <?php if ($stats['total_responses'] > 0): ?>
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 flex items-center">
                <i class="fas fa-chart-bar text-indigo-600 mr-3"></i>
                Survey Data Visualization
            </h2>

            <!-- Gender and Age Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Gender Distribution</h3>
                    <div class="chart-small">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
                
                <?php if (!empty($age_data)): ?>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Age Distribution</h3>
                    <div class="chart-small">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Safety Perception -->
            <?php if (!empty($safety_data)): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Safety Perception Distribution</h3>
                <div class="chart-container">
                    <canvas id="safetyChart"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <!-- Walking Frequency -->
            <?php if (!empty($walking_frequency_data)): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Walking Frequency</h3>
                <div class="chart-container">
                    <canvas id="walkingFrequencyChart"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <!-- Monthly Response Trends -->
            <?php if (!empty($monthly_trends)): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Response Trends Over Time</h3>
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Barriers Analysis -->
        <?php if (!empty($barriers_data)): ?>
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                Walking Barriers Analysis
            </h2>
            
            <!-- Barriers Chart -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Most Common Walking Barriers</h3>
                <div class="chart-container">
                    <canvas id="barriersChart"></canvas>
                </div>
            </div>
            
            <div class="bg-red-50 p-6 rounded-lg border border-red-200">
                <h3 class="text-lg font-semibold text-red-800 mb-4">Detailed Barrier Breakdown</h3>
                
                <?php 
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
                
                $total_barrier_responses = array_sum($barriers_data);
                ?>
                
                <div class="space-y-4">
                    <?php foreach ($barriers_data as $barrier => $count): ?>
                        <?php $percentage = $total_barrier_responses > 0 ? round(($count / $total_barrier_responses) * 100, 1) : 0; ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-red-700 flex-1">
                                <?php echo $barrier_labels[$barrier] ?? ucfirst(str_replace('_', ' ', $barrier)); ?>
                            </span>
                            <div class="flex items-center space-x-3 ml-4">
                                <div class="w-32 bg-red-200 rounded-full h-3">
                                    <div class="bg-red-600 h-3 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="text-sm font-bold text-red-800 w-16 text-right">
                                    <?php echo $count; ?> (<?php echo $percentage; ?>%)
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Activity -->
        <?php if (!empty($recent_responses)): ?>
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-clock text-blue-600 mr-3"></i>
                Recent Survey Activity
            </h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Response ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Safety Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recent_responses as $response): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?php echo $response['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo ucfirst($response['gender'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo ucfirst(str_replace('_', '-', $response['age'] ?? 'N/A')); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                $safety = $response['general_safety'] ?? '';
                                if ($safety) {
                                    $safety_colors = [
                                        'very_unsafe' => 'bg-red-100 text-red-800',
                                        'unsafe' => 'bg-orange-100 text-orange-800',
                                        'neutral' => 'bg-yellow-100 text-yellow-800',
                                        'safe' => 'bg-green-100 text-green-800',
                                        'very_safe' => 'bg-green-100 text-green-800'
                                    ];
                                    $safety_class = $safety_colors[$safety] ?? 'bg-gray-100 text-gray-800';
                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $safety_class . '">';
                                    echo ucfirst(str_replace('_', ' ', $safety));
                                    echo '</span>';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($response['submission_time'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recommendations -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-lightbulb text-yellow-600 mr-3"></i>
                Key Recommendations
            </h2>
            
            <div class="space-y-6">
                <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                    <h3 class="text-lg font-semibold text-green-800 mb-3">
                        <i class="fas fa-road mr-2"></i>Infrastructure Improvements
                    </h3>
                    <ul class="text-green-700 space-y-2 text-sm">
                        <li>• Priority rehabilitation of sidewalks and pedestrian walkways</li>
                        <li>• Installation of adequate street lighting in poorly lit areas</li>
                        <li>• Creation of covered waiting areas and shade structures</li>
                        <li>• Implementation of proper drainage systems</li>
                    </ul>
                </div>
                
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-3">
                        <i class="fas fa-shield-alt mr-2"></i>Safety Enhancements
                    </h3>
                    <ul class="text-blue-700 space-y-2 text-sm">
                        <li>• Increased enforcement of traffic regulations and speed limits</li>
                        <li>• Installation of pedestrian crossing signals and zebra crossings</li>
                        <li>• Community policing programs in high-risk areas</li>
                        <li>• Public awareness campaigns on pedestrian rights</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Report Footer -->
        <div class="bg-gray-50 rounded-xl shadow-lg p-8 text-center">
            <div class="text-sm text-gray-600 space-y-2">
                <p>This report was generated from survey data collected through the Nairobi Walkability Study.</p>
                <p><strong>Research Conducted by:</strong> Alex Ngamau, Waseda University</p>
                <p><strong>Report Generated:</strong> <?php echo date('F j, Y \a\t g:i A'); ?></p>
                <?php if ($stats['total_responses'] > 0): ?>
                <p><strong>Data Summary:</strong> 
                    <?php echo number_format($stats['total_responses']); ?> total responses, 
                    <?php echo number_format($stats['this_month']); ?> this month
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chart.js Initialization Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gender Distribution Pie Chart
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                new Chart(genderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female'],
                        datasets: [{
                            data: [<?php echo $stats['male_count']; ?>, <?php echo $stats['female_count']; ?>],
                            backgroundColor: ['#3B82F6', '#EC4899'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = <?php echo $stats['male_count'] + $stats['female_count']; ?>;
                                        const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Age Distribution Bar Chart
            <?php if (!empty($age_data)): ?>
            const ageCtx = document.getElementById('ageChart');
            if (ageCtx) {
                new Chart(ageCtx, {
                    type: 'bar',
                    data: {
                        labels: [<?php echo '"' . implode('", "', array_keys($age_data)) . '"'; ?>],
                        datasets: [{
                            label: 'Number of Respondents',
                            data: [<?php echo implode(', ', array_values($age_data)); ?>],
                            backgroundColor: '#8B5CF6',
                            borderColor: '#7C3AED',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>

            // Safety Perception Chart
            <?php if (!empty($safety_data)): ?>
            const safetyCtx = document.getElementById('safetyChart');
            if (safetyCtx) {
                new Chart(safetyCtx, {
                    type: 'bar',
                    data: {
                        labels: [<?php 
                            $safety_labels = [];
                            foreach (array_keys($safety_data) as $key) {
                                $safety_labels[] = '"' . ucfirst(str_replace('_', ' ', $key)) . '"';
                            }
                            echo implode(', ', $safety_labels);
                        ?>],
                        datasets: [{
                            label: 'Number of Responses',
                            data: [<?php echo implode(', ', array_values($safety_data)); ?>],
                            backgroundColor: [
                                '#EF4444', // very_unsafe - red
                                '#F97316', // unsafe - orange  
                                '#EAB308', // neutral - yellow
                                '#22C55E', // safe - green
                                '#16A34A'  // very_safe - dark green
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'How safe do you feel walking in Nairobi?'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>

            // Walking Frequency Chart
            <?php if (!empty($walking_frequency_data)): ?>
            const walkingCtx = document.getElementById('walkingFrequencyChart');
            if (walkingCtx) {
                new Chart(walkingCtx, {
                    type: 'pie',
                    data: {
                        labels: [<?php 
                            $walking_labels = [];
                            foreach (array_keys($walking_frequency_data) as $key) {
                                $walking_labels[] = '"' . ucfirst(str_replace('_', ' ', $key)) . '"';
                            }
                            echo implode(', ', $walking_labels);
                        ?>],
                        datasets: [{
                            data: [<?php echo implode(', ', array_values($walking_frequency_data)); ?>],
                            backgroundColor: [
                                '#8B5CF6', // purple
                                '#06B6D4', // cyan
                                '#10B981', // emerald
                                '#F59E0B', // amber
                                '#EF4444'  // red
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            },
                            title: {
                                display: true,
                                text: 'How often do you walk for transportation?'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = <?php echo array_sum($walking_frequency_data); ?>;
                                        const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>

            // Monthly Trends Line Chart
            <?php if (!empty($monthly_trends)): ?>
            const trendsCtx = document.getElementById('trendsChart');
            if (trendsCtx) {
                new Chart(trendsCtx, {
                    type: 'line',
                    data: {
                        labels: [<?php 
                            $trend_labels = [];
                            foreach (array_keys($monthly_trends) as $month) {
                                $trend_labels[] = '"' . date('M Y', strtotime($month . '-01')) . '"';
                            }
                            echo implode(', ', $trend_labels);
                        ?>],
                        datasets: [{
                            label: 'Survey Responses',
                            data: [<?php echo implode(', ', array_values($monthly_trends)); ?>],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3B82F6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Survey Response Trends (Last 12 Months)'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>

            // Barriers Horizontal Bar Chart
            <?php if (!empty($barriers_data)): ?>
            const barriersCtx = document.getElementById('barriersChart');
            if (barriersCtx) {
                const barrier_labels = {
                    'poor_sidewalk': 'Poor sidewalk condition',
                    'lack_shade': 'Lack of shade or shelter',
                    'unsafe_crossings': 'Unsafe road crossings',
                    'long_distance': 'Long distance to bus stops',
                    'crime_concerns': 'Crime or harassment concerns',
                    'poor_lighting': 'Poor street lighting',
                    'vehicle_speeds': 'High vehicle speeds',
                    'lack_signals': 'Lack of pedestrian signals',
                    'no_amenities': 'No pedestrian amenities',
                    'narrow_sidewalks': 'Narrow/crowded sidewalks'
                };

                new Chart(barriersCtx, {
                    type: 'bar',
                    data: {
                        labels: [<?php 
                            $barrier_chart_labels = [];
                            foreach (array_keys($barriers_data) as $key) {
                                $barrier_chart_labels[] = '"' . (isset($barrier_labels[$key]) ? $barrier_labels[$key] : ucfirst(str_replace('_', ' ', $key))) . '"';
                            }
                            echo implode(', ', $barrier_chart_labels);
                        ?>],
                        datasets: [{
                            label: 'Number of Reports',
                            data: [<?php echo implode(', ', array_values($barriers_data)); ?>],
                            backgroundColor: '#EF4444',
                            borderColor: '#DC2626',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Most Reported Walking Barriers'
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>

<?php if (isset($conn)) $conn->close(); ?>