<?php
header('Content-Type: application/json');
$file = 'records.json';
if (file_exists($file)) {
    $records = file_get_contents($file);
    echo $records;
} else {
    echo json_encode([]);
}
?>
