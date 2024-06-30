<?php
$TOKEN = '7210847776:AAG5dq6QriUqP5iGkB-SC-h-o8aYIofzoxU'; // جایگزین کنید با توکن ربات شما
$CHAT_ID = '5791876099'; // جایگزین کنید با ID چت شما

function send_message($message) {
    global $TOKEN, $CHAT_ID;
    $url = "https://api.telegram.org/bot$TOKEN/sendMessage";
    $data = [
        'chat_id' => $CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        writeLog("Error sending message: " . error_get_last()['message']);
    } else {
        writeLog("Message sent successfully: " . $message);
    }

    return $result;
}

function writeLog($message) {
    $logFile = 'log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = $timestamp . " - " . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['record'])) {
        $record = $input['record'];
        $message = "یادآوری: واحد شماره " . $record['unitNumber'] . "/" . $record['name'] . "/" . $record['date'] . "/" . $record['description'];
        $result = send_message($message);
        if ($result !== FALSE) {
            echo 'یادآوری به تلگرام ارسال شد.';
        } else {
            echo 'خطا در ارسال یادآوری.';
        }
        writeLog('Reminder sent for record: ' . json_encode($record));
    } else {
        echo 'رکوردی برای ارسال یادآوری یافت نشد.';
        writeLog('No record found for reminder.');
    }
} else {
    echo 'درخواست نامعتبر.';
    writeLog('Invalid request method.');
}
?>
