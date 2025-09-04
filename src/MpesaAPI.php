<?php
// src/MpesaAPI.php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MpesaAPI
{
    private $client;
    private $consumerKey;
    private $consumerSecret;
    private $passkey;
    private $shortcode;
    private $environment;
    private $baseUrl;

    public function __construct()
    {
        // Load environment variables
        $this->consumerKey = $_ENV['MPESA_CONSUMER_KEY'] ?? '';
        $this->consumerSecret = $_ENV['MPESA_CONSUMER_SECRET'] ?? '';
        $this->passkey = $_ENV['MPESA_PASSKEY'] ?? '';
        $this->shortcode = $_ENV['MPESA_SHORTCODE'] ?? '174379'; // Default sandbox shortcode
        $this->environment = $_ENV['MPESA_ENVIRONMENT'] ?? 'sandbox';
        
        // Set base URL based on environment
        $this->baseUrl = $this->environment === 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke' 
            : 'https://api.safaricom.co.ke';
            
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false // For development only
        ]);
    }

    /**
     * Generate OAuth access token
     */
    public function getAccessToken(): ?string
    {
        try {
            $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
            
            $response = $this->client->request('GET', $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials', [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            return $result['access_token'] ?? null;

        } catch (GuzzleException $e) {
            error_log("M-Pesa Auth Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate password for STK Push
     */
    private function generatePassword(): string
    {
        $timestamp = date('YmdHis');
        return base64_encode($this->shortcode . $this->passkey . $timestamp);
    }

    /**
     * Get current timestamp
     */
    private function getTimestamp(): string
    {
        return date('YmdHis');
    }

    /**
     * Initiate STK Push payment
     */
    public function stkPush($phoneNumber, $amount, $accountReference, $transactionDesc): array
    {
        // Validate phone number format
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        if (!$phoneNumber) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format. Use 254XXXXXXXXX'
            ];
        }

        // Get access token
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with M-Pesa API'
            ];
        }

        try {
            $timestamp = $this->getTimestamp();
            $password = $this->generatePassword();

            $requestData = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) $amount,
                'PartyA' => $phoneNumber,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => 'https://mydomain.com/callback', // Replace with your callback URL
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc
            ];

            $response = $this->client->request('POST', $this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $requestData
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'message' => 'STK Push sent successfully',
                    'data' => $result,
                    'checkout_request_id' => $result['CheckoutRequestID'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['errorMessage'] ?? 'STK Push failed',
                    'data' => $result
                ];
            }

        } catch (GuzzleException $e) {
            error_log("M-Pesa STK Push Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Network error occurred. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phoneNumber): ?string
    {
        // Remove any spaces, hyphens, or plus signs
        $phoneNumber = preg_replace('/[\s\-\+]/', '', $phoneNumber);
        
        // If starts with 0, replace with 254
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }
        
        // If starts with 7, add 254
        if (substr($phoneNumber, 0, 1) === '7') {
            $phoneNumber = '254' . $phoneNumber;
        }
        
        // Validate format (should be 254XXXXXXXXX and 12 digits)
        if (preg_match('/^254[7][0-9]{8}$/', $phoneNumber)) {
            return $phoneNumber;
        }
        
        return null;
    }

    /**
     * Query STK Push transaction status
     */
    public function queryTransactionStatus($checkoutRequestId): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with M-Pesa API'
            ];
        }

        try {
            $timestamp = $this->getTimestamp();
            $password = $this->generatePassword();

            $requestData = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId
            ];

            $response = $this->client->request('POST', $this->baseUrl . '/mpesa/stkpushquery/v1/query', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $requestData
            ]);

            $result = json_decode($response->getBody(), true);
            return [
                'success' => true,
                'data' => $result
            ];

        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'message' => 'Failed to query transaction status',
                'error' => $e->getMessage()
            ];
        }
    }
}