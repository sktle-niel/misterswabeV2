<?php
// Test if we can find the storage directory
$storageDir = dirname(__FILE__) . '/../storage/reports/';

echo "Download folder: " . dirname(__FILE__) . "<br>";
echo "Storage path: $storageDir<br>";
echo "Storage exists: " . (is_dir($storageDir) ? 'YES' : 'NO') . "<br>";
echo "Absolute path: " . realpath($storageDir) . "<br>";

if (is_dir($storageDir)) {
    echo "<br>Files:<br>";
    $files = scandir($storageDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file<br>";
        }
    }
}
?>