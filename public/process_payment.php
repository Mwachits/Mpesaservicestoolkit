<?php
// public/process_payment.php

require_once '../vendor/autoload.php';

use App\MpesaAPI;

// Load environment variables
if (file_exists('../.env')) {
    $lines = file('../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Load services configuration
$services = include '../config/services.php';

// Set response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get form data
    $serviceType = $_POST['service_type'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $amount = $_POST['amount'] ?? 0;

    // Validate input
    if (empty($serviceType) || empty($phoneNumber) || empty($customerName) || empty($amount)) {
        throw new Exception('All fields are required');
    }

    // Validate service type
    if (!isset($services[$serviceType])) {
        throw new Exception('Invalid service type selected');
    }

    $selectedService = $services[$serviceType];

    // Validate amount matches service cost
    if ((int) $amount !== (int) $selectedService['amount']) {
        throw new Exception('Amount does not match service cost');
    }

    // Validate phone number format (basic validation)
    $phoneNumber = preg_replace('/[\s\-\+]/', '', $phoneNumber);
    if (!preg_match('/^(0|254)?[7][0-9]{8}$/', $phoneNumber)) {
        throw new Exception('Invalid phone number format. Use 07XXXXXXXX or 2547XXXXXXXX');
    }

    // Initialize M-Pesa API
    $mpesa = new MpesaAPI();

    // Generate unique account reference
    $accountReference = $selectedService['code'] . '_' . date('YmdHis') . '_' . substr($phoneNumber, -4);
    
    // Generate transaction description
    $transactionDesc = $selectedService['name'] . ' - ' . $customerName;

    // Log the payment attempt
    error_log("Payment attempt: Service={$serviceType}, Phone={$phoneNumber}, Amount={$amount}, Customer={$customerName}");

    // Initiate STK Push
    $result = $mpesa->stkPush(
        $phoneNumber,
        $amount,
        $accountReference,
        $transactionDesc
    );

    if ($result['success']) {
        // Log successful STK push
        error_log("STK Push successful: CheckoutRequestID=" . ($result['checkout_request_id'] ?? 'N/A'));
        
        // In a real application, you would save this to database
        // For now, we'll just return success response
        echo json_encode([
            'success' => true,
            'message' => 'Payment request sent successfully. Please check your phone.',
            'data' => [
                'service_name' => $selectedService['name'],
                'amount' => $amount,
                'phone_number' => $phoneNumber,
                'account_reference' => $accountReference,
                'checkout_request_id' => $result['checkout_request_id'] ?? null
            ]
        ]);
    } else {
        // Log failed STK push
        error_log("STK Push failed: " . $result['message']);
        
        echo json_encode([
            'success' => false,
            'message' => $result['message'] ?? 'Payment request failed',
            'error_details' => $result['data'] ?? null
        ]);
    }

} catch (Exception $e) {
    // Log the error
    error_log("Payment processing error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Log system errors
    error_log("System error: " . $e->getMessage());
    
    // Return generic error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A system error occurred. Please try again later.'
    ]);
}
?>