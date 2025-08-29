<?php
// quick_url_fix.php - Run this once to fix all URLs back to .php extensions

$files_to_fix = [
    'admin/dashboard.php',
    'admin/view_responses.php', 
    'admin/view_response.php',
    'admin/edit_response.php',
    'admin/generate_report.php'
];

$url_replacements = [
    // Fix navigation links
    'href="dashboard"' => 'href="dashboard.php"',
    'href="view-responses"' => 'href="view_responses.php"',
    'href="view-response/' => 'href="view_response.php?id=',
    'href="edit-response/' => 'href="edit_response.php?id=',
    'href="generate-report"' => 'href="generate_report.php"',
    'href="export"' => 'href="export_excel.php"',
    'href="logout"' => 'href="logout.php"',
    'href="login"' => 'href="login.php"',
    
    // Fix form actions
    'action="dashboard"' => 'action="dashboard.php"',
    'action="view-responses"' => 'action="view_responses.php"',
    
    // Fix Location redirects
    "Location: dashboard" => "Location: dashboard.php",
    "Location: login" => "Location: login.php",
    "Location: view_responses" => "Location: view_responses.php",
];

echo "<!DOCTYPE html>
<html><head><title>URL Fix Tool</title></head><body style='font-family: Arial; padding: 20px;'>
<h1>Fixing URLs in Admin Files</h1>";

foreach ($files_to_fix as $file) {
    if (file_exists($file)) {
        echo "<p>Fixing: $file</p>";
        
        $content = file_get_contents($file);
        $original_content = $content;
        
        // Apply all URL replacements
        foreach ($url_replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Special case for view-response and edit-response with IDs
        $content = preg_replace('/href="view-response\/(\d+)"/', 'href="view_response.php?id=$1"', $content);
        $content = preg_replace('/href="edit-response\/(\d+)"/', 'href="edit_response.php?id=$1"', $content);
        
        if ($content !== $original_content) {
            file_put_contents($file, $content);
            echo " ✅ Updated<br>";
        } else {
            echo " ℹ️ No changes needed<br>";
        }
    } else {
        echo "<p style='color: red;'>❌ File not found: $file</p>";
    }
}

echo "<h2>Complete!</h2>
<p>✅ All URLs have been restored to use .php extensions</p>
<p>🗑️ You can delete this file now (quick_url_fix.php)</p>
<p><a href='admin/login.php'>→ Go to Admin Login</a></p>
</body></html>";
?>