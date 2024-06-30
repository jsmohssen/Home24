<?php
function writeLog($message) {
    $logFile = 'log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = $timestamp . " - " . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['error'])) {
    $error = $_POST['error'];
    writeLog('AJAX Error: ' . $error);
    echo 'Error logged.';
} else {
    writeLog('Invalid log request.');
    echo 'Invalid log request.';
}
?>
