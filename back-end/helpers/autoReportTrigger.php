<?php
// Auto-generate reports trigger
// This runs once per day when any page is loaded

// Make sure we have database connection
if (!isset($conn)) {
    require_once __DIR__ . '../../../config/connection.php';
}

// Check if report_history table exists, if not skip
$tableCheck = $conn->query("SHOW TABLES LIKE 'report_history'");
if ($tableCheck->num_rows == 0) {
    // Table doesn't exist yet, skip auto-generation
    return;
}

// Check if we need to generate reports
$lastRunQuery = $conn->query("SELECT MAX(generated_at) as last_run FROM report_history");
if (!$lastRunQuery) {
    // Query failed, skip
    return;
}

$lastRunResult = $lastRunQuery->fetch_assoc();
$lastRun = $lastRunResult['last_run'] ?? null;
$lastRunDate = $lastRun ? date('Y-m-d', strtotime($lastRun)) : '1970-01-01';
$today = date('Y-m-d');

// Only run once per day
if ($lastRunDate < $today) {
    // Generate yesterday's report
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $checkDaily = $conn->query("SELECT id FROM report_history WHERE report_type = 'daily_sales' AND period = '$yesterday'");
    
    if ($checkDaily && $checkDaily->num_rows == 0) {
        // Trigger report generation in background
        $phpPath = 'php'; // For Laragon, php is in PATH
        $scriptPath = __DIR__ . '/../cron/autoGenerateReports.php';
        
        // Use exec for better background execution (non-blocking)
        if (function_exists('exec') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows command
            pclose(popen("start /B php \"$scriptPath\"", "r"));
        } elseif (function_exists('exec')) {
            // Linux/Unix command
            exec("php \"$scriptPath\" > /dev/null 2>&1 &");
        }
    }
}
?>