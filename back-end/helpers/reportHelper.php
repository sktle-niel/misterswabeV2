<?php
function saveReportHistory($conn, $reportType, $reportName, $filePath, $fileSize, $period, $pdfContent = null, $categoryName = null) {
    // Build the uniqueness check query based on report type
    if ($reportType === 'category' && $categoryName !== null) {
        // For category reports, include category name in the uniqueness check
        $checkQuery = "SELECT id FROM report_history WHERE report_type = ? AND period = ? AND report_name LIKE ?";
        $checkStmt = $conn->prepare($checkQuery);
        $categoryPattern = '%' . $categoryName . '%';
        $checkStmt->bind_param("sss", $reportType, $period, $categoryPattern);
    } else {
        // For other reports, use the original check
        $checkQuery = "SELECT id FROM report_history WHERE report_type = ? AND period = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ss", $reportType, $period);
    }
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Report already exists, don't create duplicate
        $checkStmt->close();
        return;
    }
    $checkStmt->close();
    
    // Save PDF file to storage if provided
    if ($pdfContent !== null) {
        // Use absolute path
        $storageDir = dirname(__FILE__) . '/../storage/reports/';
        
        // Create directory if it doesn't exist
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
            chmod($storageDir, 0777);
        }
        
        // Save file
        $fullPath = $storageDir . $filePath;
        $bytes = file_put_contents($fullPath, $pdfContent);
        
        // Debug: Always log
        if ($bytes === false) {
            error_log("❌ FAILED to save report: $fullPath");
        } else {
            error_log("✅ Report saved successfully: $fullPath ($bytes bytes)");
            // Also create a debug file
            file_put_contents(
                $storageDir . 'debug.txt', 
                date('Y-m-d H:i:s') . " - Saved: $filePath ($bytes bytes)\n", 
                FILE_APPEND
            );
        }
    } else {
        // Log when pdfContent is null
        error_log("⚠️ WARNING: pdfContent is NULL for $reportName");
        $storageDir = dirname(__FILE__) . '/../storage/reports/';
        file_put_contents(
            $storageDir . 'debug.txt', 
            date('Y-m-d H:i:s') . " - ERROR: pdfContent is NULL for $reportName\n", 
            FILE_APPEND
        );
    }
    
    // Insert new report
    $stmt = $conn->prepare("INSERT INTO report_history (report_type, report_name, file_path, file_size, period) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $reportType, $reportName, $filePath, $fileSize, $period);
    $stmt->execute();
    $stmt->close();
}

function getReportFileSize($content) {
    $bytes = strlen($content);
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>