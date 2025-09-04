<?php
// public/callback.php
require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

$url_key = $_GET['key'] ?? '';
if ($url_key !== $_ENV['MPESA_CALLBACK_SECRET']) {
    http_response_code(403);
    error_log("Unauthorized callback attempt with key: $url_key");
    exit('Forbidden: Invalid secret key');
}

use App\MpesaAPI;

$payload = file_get_contents('php://input');
error_log("Callback received: $payload");

$data = json_decode($payload, true);
$callback = $data['Body']['stkCallback'] ?? null;
if (!$callback) {
    http_response_code(400);
    exit;
}

$resultCode = $callback['ResultCode'];
$resultDesc = $callback['ResultDesc'];
$metadata = $callback['CallbackMetadata']['Item'] ?? [];

$record = [];
foreach ($metadata as $item) {
    $record[$item['Name']] = $item['Value'] ?? null;
}

// DB logging
try {
    $db = new PDO($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $stmt = $db->prepare("
        INSERT INTO transactions 
        (checkout_request_id, result_code, result_desc, amount, receipt_no, transaction_date, phone) 
        VALUES (:checkout, :code, :desc, :amount, :receipt, :date, :phone)
    ");
    $stmt->execute([
        ':checkout' => $callback['CheckoutRequestID'],
        ':code' => $resultCode,
        ':desc' => $resultDesc,
        ':amount' => $record['Amount'] ?? 0,
        ':receipt' => $record['MpesaReceiptNumber'] ?? '',
        ':date' => $record['TransactionDate'] ?? '',
        ':phone' => $record['PhoneNumber'] ?? ''
    ]);
} catch (PDOException $e) {
    error_log("DB Error on callback: " . $e->getMessage());
}

echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
