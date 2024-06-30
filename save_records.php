<?php
function writeLog($message) {
    $logFile = 'log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = $timestamp . " - " . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['records'])) {
        $records = json_encode($input['records']);
        $file = 'records.json';
        
        if (file_put_contents($file, $records)) {
            writeLog('Records saved successfully.');
            echo 'Records saved.';
        } else {
            writeLog('Error saving records.');
            echo 'Error saving records.';
        }
    } else {
        writeLog('Invalid request: records not set.');
        echo 'Invalid request: records not set.';
    }
} else {
    writeLog('Invalid request method.');
    echo 'Invalid request method.';
}
?>
