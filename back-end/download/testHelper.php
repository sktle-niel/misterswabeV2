<?php
// Test if reportHelper.php can be loaded
$helperPath = __DIR__ . '/../helpers/reportHelper.php';

echo "Looking for helper at: $helperPath<br>";
echo "File exists: " . (file_exists($helperPath) ? 'YES' : 'NO') . "<br>";

if (file_exists($helperPath)) {
    require_once $helperPath;
    echo "Helper loaded successfully!<br>";
    
    if (function_exists('saveReportHistory')) {
        echo "✓ saveReportHistory function exists<br>";
    } else {
        echo "✗ saveReportHistory function NOT found<br>";
    }
    
    if (function_exists('getReportFileSize')) {
        echo "✓ getReportFileSize function exists<br>";
    } else {
        echo "✗ getReportFileSize function NOT found<br>";
    }
} else {
    echo "ERROR: Helper file not found!<br>";
    echo "Create the file at: $helperPath";
}
?>