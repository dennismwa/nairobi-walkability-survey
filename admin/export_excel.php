<?php
// admin/export_excel.php
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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine export type and build query
$where_clause = "";
$filename_suffix = "";

if (isset($_POST['selected_ids'])) {
    // Export selected responses
    $selected_ids = json_decode($_POST['selected_ids'], true);
    if (!empty($selected_ids)) {
        $ids_string = implode(',', array_map('intval', $selected_ids));
        $where_clause = "WHERE id IN ($ids_string)";
        $filename_suffix = "_selected";
    }
} elseif (isset($_GET['export']) && $_GET['export'] === 'current') {
    // Export current view with search
    $search = isset($_GET['search']) ? $_GET['search'] : '';
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
        $filename_suffix = "_filtered";
    }
}

// Get all survey responses
$query = "SELECT * FROM survey_responses $where_clause ORDER BY submission_time DESC";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Create CSV content
$csv_data = [];

// Find this section in export_excel.php and replace the headers array:

$headers = [
    'Response ID',
    'Submission Time',
    'IP Address',
    
    // Basic Information
    'Gender',
    'Age Group',
    'Education Level',
    'Occupation',
    'Occupation Other',
    'Monthly Income',
    'Sub County',
    'Ward',
    'Estate',
    'Residence Privacy',
    'Car Ownership',
    'Bus Usage Frequency',
    'Walking Usage Frequency',
    'Recent Trip Origin',
    'Recent Trip Destination',
    
    // Updated Transport Mode Fields
    'Transport Mode First Mile',
    'Transport Mode Main Mile',  
    'Transport Mode Last Mile',
    'Transport Mode Other (Legacy)', // Keep for backward compatibility
    
    // Section A: Safety Perception
    'General Safety Feeling',
    'Accident Concern Level',
    'Driver Yield Frequency',
    'Night Safety Feeling',
    
    // Section B: Walkability
    'Walkway Maintenance Importance',
    'Obstacles Frequency',
    'Path Connectivity Agreement',
    'Comfort Satisfaction',
    
    // Section C: Infrastructure Quality
    'Walkway Obstruction',
    'Street Lighting Adequacy',
    'Road Surface Safety',
    'Traffic Calming Effectiveness',
    
    // Section D: Socioeconomic Context
    'Income Transportation Limitation',
    'Affordability Walking Influence',
    'Transport Cost Effect',
    
    // Section E: Mobility Patterns
    'Walking Frequency to Facilities',
    'Safety Concerns Route Choice',
    'Leisure Walking Frequency',
    
    // Section F: Traffic Safety Risks
    'Vehicle Speed Risk Frequency',
    'Accident Witness Frequency',
    'Road Crossing Danger',
    
    // Section G: Last Mile Accessibility
    'Bus Stop Convenience',
    'Path to Transport Friendliness',
    'Job Accessibility',
    
    // Section H: Vulnerable Groups
    'Vulnerable Group Accommodation',
    'Children School Safety',
    'Wheelchair Accessibility',
    'Equal Access Effectiveness',
    
    // Additional
    'Walking Barriers',
    'Other Barriers',
    'Additional Comments'
];

// And replace the data row mapping section:
while ($row = $result->fetch_assoc()) {
    $data_row = [
        $row['id'],
        $row['submission_time'],
        $row['ip_address'],
        
        // Basic Information
        ucfirst(str_replace('_', ' ', $row['gender'] ?? '')),
        str_replace('_', '-', $row['age'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['education'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['occupation'] ?? '')),
        $row['occupation_other'] ?? '',
        $row['income'] ?? '',
        $row['sub_county'] ?? '',
        $row['ward'] ?? '',
        $row['estate'] ?? '',
        $row['residence_privacy'] ? 'Yes' : 'No',
        ucfirst($row['car_ownership'] ?? ''),
        ucfirst($row['bus_usage'] ?? ''),
        ucfirst($row['walking_usage'] ?? ''),
        $row['trip_origin'] ?? '',
        $row['trip_destination'] ?? '',
        
        // Updated Transport Mode Fields
        ucfirst(str_replace('_', '/', $row['transport_mode_first_mile'] ?? '')),
        ucfirst(str_replace('_', '/', $row['transport_mode_main_mile'] ?? '')),
        ucfirst(str_replace('_', '/', $row['transport_mode_last_mile'] ?? '')),
        str_replace('_', '/', ucfirst($row['transport_mode'] ?? '')), // Legacy field
        
        // Safety and walkability responses (keep existing mappings)
        ucfirst(str_replace('_', ' ', $row['general_safety'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['accident_concern'] ?? '')),
        ucfirst($row['driver_yield'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['night_safety'] ?? '')),
        
        ucfirst(str_replace('_', ' ', $row['walkway_importance'] ?? '')),
        ucfirst($row['obstacles_frequency'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['path_connectivity'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['comfort_satisfaction'] ?? '')),
        
        ucfirst($row['walkway_obstruction'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['street_lighting'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['road_surface_safety'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['traffic_calming'] ?? '')),
        
        ucfirst($row['income_limitation'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['affordability_influence'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['cost_effect'] ?? '')),
        
        ucfirst($row['walk_frequency'] ?? ''),
        ucfirst($row['safety_route_choice'] ?? ''),
        ucfirst($row['leisure_walk'] ?? ''),
        
        ucfirst($row['vehicle_speed_risk'] ?? ''),
        ucfirst($row['witness_accidents'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['crossing_danger'] ?? '')),
        
        ucfirst(str_replace('_', ' ', $row['bus_stop_convenience'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['path_friendliness'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['job_accessibility'] ?? '')),
        
        ucfirst($row['vulnerable_accommodation'] ?? ''),
        ucfirst(str_replace('_', ' ', $row['children_school_safety'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['wheelchair_accessibility'] ?? '')),
        ucfirst(str_replace('_', ' ', $row['equal_access_effectiveness'] ?? '')),
        
        str_replace(',', '; ', $row['barriers'] ?? ''),
        $row['barriers_other'] ?? '',
        $row['additional_comments'] ?? ''
    ];
    
    $csv_data[] = $data_row;
}
// Generate filename
$timestamp = date('Y-m-d_H-i-s');
$filename = "nairobi_survey_responses{$filename_suffix}_{$timestamp}.csv";

// Set headers for file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output CSV
$output = fopen('php://output', 'w');

// Add BOM for proper UTF-8 handling in Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

foreach ($csv_data as $row) {
    fputcsv($output, $row);
}

fclose($output);
$conn->close();
exit;
?>