# Getting Started with M-Pesa Daraja API in PHP - Kenya Government Services Integration

## 1. Title & Objective

**Technology Chosen:** M-Pesa Daraja API with PHP  
**Project Goal:** Build a payment integration system for Kenya government services including Business Permits, Certificates of Good Conduct, Driving License Renewals, and Marriage Certificate applications.  

**Why This Technology?**
- M-Pesa is the backbone of digital payments in Kenya, processing over 50% of the country's GDP
- Essential for government digital transformation initiatives like eCitizen
- High demand skill for developers working on fintech and government solutions in Kenya
- Provides practical experience with REST APIs, webhooks, and payment processing

**End Goal:** Create a functional web application that can process real government service payments through M-Pesa STK Push, demonstrating the complete payment flow from service selection to payment confirmation.

## 2. Quick Summary of the Technology

**What is M-Pesa Daraja API?**
M-Pesa Daraja API is Safaricom's platform that allows developers to integrate M-Pesa payment functionality into their applications. "Daraja" means "bridge" in Swahili, symbolizing the connection between businesses and M-Pesa's payment ecosystem.

**Key Features:**
- **STK Push (Lipa na M-Pesa Online):** Send payment prompts directly to customer phones
- **C2B Payments:** Customer-to-business transactions
- **B2C Payments:** Business-to-customer transfers  
- **Transaction Status Queries:** Check payment status in real-time
- **Account Balance:** Query account balances

**Where It's Used:**
- E-commerce platforms (Jumia, Kilimall)
- Utility bill payments (KPLC, Nairobi Water)
- Government services (eCitizen, KRA iTax)
- Banking applications
- Insurance premium payments
- School fee payments

**Real-World Example:** 
The Kenya Government's eCitizen platform uses M-Pesa Daraja API to process over 300,000 government service payments monthly, including passport applications, business registrations, and court fine payments. Citizens can pay for services like good conduct certificates (KES 1,050) and business permits (KES 2,000+) directly from their phones.

## 3. System Requirements

**Operating System:**
- Linux (Ubuntu 20.04+ recommended)
- macOS (10.15+)
- Windows (with WSL2)

**Software Requirements:**
- **PHP 8.0 or higher** - Core programming language
- **Composer** - PHP dependency manager  
- **Web Server** - Apache, Nginx, or PHP built-in server
- **Git** - Version control

**Development Tools:**
- **VS Code** - Code editor with PHP extensions
- **Postman** (optional) - API testing
- **Terminal/Command Line** access

**External Services:**
- **Safaricom Developer Account** - Free registration required
- **M-Pesa Sandbox Access** - For testing payments
- **Internet Connection** - For API communications

**PHP Extensions Required:**
- curl - For HTTP requests
- json - JSON processing
- mbstring - String manipulation
- xml - XML processing (if needed)

## 4. Installation & Setup Instructions

### Step 1: Environment Setup

**For Ubuntu/WSL:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install php php-cli php-curl php-json php-mbstring php-xml -y

# Verify PHP installation
php -v
# Expected output: PHP 8.3.6 (cli) or higher
```

**Install Composer:**
```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer -V
# Expected output: Composer version 2.x.x
```

**Install VS Code Extensions:**
1. Open VS Code
2. Go to Extensions (Ctrl+Shift+X)
3. Install:
   - PHP Intelephense
   - PHP Debug
   - Bracket Pair Colorizer

### Step 2: Safaricom Developer Account Setup

1. **Visit:** https://developer.safaricom.co.ke/
2. **Click:** "Get Started" or "Sign Up"
3. **Fill in details:**
   - Email address
   - Phone number
   - Password
4. **Verify email** from Safaricom
5. **Login** to developer portal
6. **Create new app:**
   - App Name: "Government Services Payment"
   - Select APIs: "Lipa na M-Pesa Online"
   - Environment: Sandbox
7. **Copy credentials:**
   - Consumer Key: `[YOUR_CONSUMER_KEY]`
   - Consumer Secret: `[YOUR_CONSUMER_SECRET]`  
   - Passkey: `bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919`

### Step 3: Project Setup

```bash
# Create project directory
mkdir mpesa-government-services
cd mpesa-government-services

# Initialize Git repository
git init
git remote add origin https://github.com/Mwachits/Mpesaservicestoolkit.git

# Initialize Composer
composer init --no-interaction

# Install required dependencies
composer require guzzlehttp/guzzle vlucas/phpdotenv

# Create project structure
mkdir -p src public config docs logs
chmod 755 logs

# Create essential files
touch .env README.md
touch src/MpesaAPI.php
touch public/index.html public/process_payment.php public/callback.php
touch config/services.php
touch docs/TOOLKIT.md
```

### Step 4: Environment Configuration

Create `.env` file:
```bash
# M-Pesa API Configuration
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_SHORTCODE=174379
MPESA_ENVIRONMENT=sandbox

# Application Configuration
APP_NAME="Government Services Payment"
APP_URL=http://localhost:8000
```

## 5. Minimal Working Example

### Core Components Explanation

**1. MpesaAPI.php** - The main integration class that handles:
- Authentication with M-Pesa API using OAuth
- STK Push payment initiation
- Phone number formatting and validation
- Error handling and response processing

**2. index.html** - Frontend interface featuring:
- Service selection cards for government services
- Payment form with phone number and customer details
- Real-time payment status updates
- Responsive design for mobile and desktop

**3. process_payment.php** - Backend endpoint that:
- Validates form input and service selection
- Initiates M-Pesa STK Push payments
- Returns JSON responses to frontend
- Logs payment attempts for debugging

**4. services.php** - Configuration file containing:
- Government service definitions with accurate pricing
- Service codes and descriptions
- Processing time information

### Key Code Snippets

**Authentication Example:**
```php
public function getAccessToken(): ?string
{
    $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
    
    $response = $this->client->request('GET', 
        $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials', [
        'headers' => [
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/json'
        ]
    ]);
    
    $result = json_decode($response->getBody(), true);
    return $result['access_token'] ?? null;
}
```

**STK Push Implementation:**
```php
public function stkPush($phoneNumber, $amount, $accountReference, $transactionDesc): array
{
    $timestamp = date('YmdHis');
    $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
    
    $requestData = [
        'BusinessShortCode' => $this->shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => (int) $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $this->shortcode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => 'https://mydomain.com/callback',
        'AccountReference' => $accountReference,
        'TransactionDesc' => $transactionDesc
    ];
    
    // Send request to M-Pesa API...
}
```

### Expected Output

**Successful Payment:**
1. User selects "Business Permit Application" (KES 2,000)
2. Enters phone number "0712345678" 
3. System sends STK Push to phone
4. User receives M-Pesa prompt: "Pay KES 2000 to Government Services"
5. User enters M-Pesa PIN
6. Payment confirmed with receipt number
7. System displays success message

**Console Output:**
```
Payment attempt: Service=business_permit, Phone=254712345678, Amount=2000
STK Push successful: CheckoutRequestID=ws_CO_191220191020363925
Payment confirmed: Receipt=NLJ7RT61SV, Amount=2000
```

### Testing Instructions

1. **Start local server:**
   ```bash
   php -S localhost:8000 -t public/
   ```

2. **Open browser:** http://localhost:8000

3. **Test payment flow:**
   - Select "Certificate of Good Conduct"
   - Enter test phone: 254708374149
   - Enter name: "John Doe"
   - Click "Pay with M-Pesa"
   - Check phone for STK Push prompt

4. **Verify logs:**
   ```bash
   tail -f logs/payments.log
   ```

## 6. AI Prompt Journal

### Prompt 1: Technology Overview
**Prompt Used:** 
> "I'm building a payment system for Kenya government services (Business Permits, Good Conduct certificates, DL Renewal, Marriage Applications) using M-Pesa Daraja API and PHP. Explain the M-Pesa integration basics and what endpoints I need for STK Push payments."

**Link:** https://ai.moringaschool.com/chat/[session_id]

**AI Response Summary:**
The AI explained that M-Pesa Daraja API is Safaricom's developer platform offering several payment endpoints. For government services, STK Push (Lipa na M-Pesa Online) is the most suitable as it sends payment prompts directly to users' phones. Key endpoints needed:
- `/oauth/v1/generate` - Authentication
- `/mpesa/stkpush/v1/processrequest` - Initiate payments
- `/mpesa/stkpushquery/v1/query` - Check payment status

**Evaluation:** ⭐⭐⭐⭐⭐ Extremely helpful. Provided clear technical overview and specific API endpoints needed. This saved me hours of documentation reading and gave me the right starting point.

### Prompt 2: PHP Project Structure
**Prompt Used:**
> "Show me how to structure a PHP project for M-Pesa Daraja API integration. Include authentication, STK Push, and handling different government service types with different amounts."

**AI Response Summary:**
The AI suggested a clean project structure separating concerns:
- `src/MpesaAPI.php` - Core API integration class
- `config/services.php` - Government services configuration
- `public/` - Web-accessible files
- `.env` - Environment variables for credentials

It also recommended using Composer for dependency management and Guzzle HTTP client for API requests.

**Evaluation:** ⭐⭐⭐⭐⭐ Perfect guidance on project architecture. The suggested structure made the codebase organized and maintainable. The recommendation to separate services configuration was particularly valuable.

### Prompt 3: Authentication Implementation
**Prompt Used:**
> "Create a PHP class method to authenticate with M-Pesa Daraja API using OAuth. Include error handling and explain the authentication flow."

**AI Response Summary:**
The AI provided a complete authentication method using base64 encoding of consumer key and secret. It explained the OAuth flow:
1. Combine consumer key and secret with colon separator
2. Base64 encode the combination  
3. Send GET request with Authorization header
4. Extract access token from response

**Evaluation:** ⭐⭐⭐⭐ Very helpful for understanding OAuth implementation. The code was production-ready with proper error handling.

### Prompt 4: STK Push Implementation  
**Prompt Used:**
> "Implement M-Pesa STK Push in PHP with proper error handling. Show me how to format phone numbers and generate the required password for authentication."

**AI Response Summary:**
The AI provided comprehensive STK Push implementation including:
- Password generation using timestamp and passkey
- Phone number formatting (handling 07xx and 254xx formats)
- Complete request payload structure
- Error handling for network failures and API errors

**Evaluation:** ⭐⭐⭐⭐⭐ Exceptional detail and accuracy. The phone number formatting logic was particularly valuable as it handles common user input variations.

### Prompt 5: Frontend Integration
**Prompt Used:**
> "Create a modern HTML form for government service payments with JavaScript to handle M-Pesa integration. Include service selection and real-time payment status updates."ruit."

**AI Response Summary:**
The AI generated a complete responsive frontend with:
- Service selection cards with pricing
- Payment form with validation
- AJAX integration for seamless payments
- Loading states and error handling
- Mobile-first responsive design

**Evaluation:** ⭐⭐⭐⭐ Good frontend implementation with modern design principles. The JavaScript integration made the user experience smooth.

### Prompt 6: Error Handling and Debugging
**Prompt Used:**
> "What are common errors when integrating M-Pesa Daraja API and how to fix them in PHP? Include authentication issues, network problems, and callback handling."

**AI Response Summary:**
The AI identified common issues:
- Authentication failures due to incorrect credentials
- Network timeouts and connection issues  
- Phone number format validation errors
- Callback URL accessibility problems
- SSL certificate verification issues

It provided specific solutions and debugging techniques for each issue.

**Evaluation:** ⭐⭐⭐⭐⭐ Extremely valuable for troubleshooting. These insights saved significant debugging time and helped build robust error handling.

## 7. Common Issues & Fixes

### Issue 1: Authentication Failed
**Error Message:** 
```
Failed to authenticate with M-Pesa API
```

**Cause:** Incorrect Consumer Key or Consumer Secret in environment file

**Solution:**
1. Double-check credentials in Safaricom Developer Portal
2. Ensure no extra spaces in `.env` file
3. Verify app is active in sandbox environment
4. Test authentication separately:
   ```php
   $mpesa = new MpesaAPI();
   $token = $mpesa->getAccessToken();
   var_dump($token); // Should show access token
   ```

**Resources:** 
- [Safaricom Authentication Guide](https://developer.safaricom.co.ke/docs#authentication)

### Issue 2: Invalid Phone Number Format
**Error Message:**
```
Invalid phone number format. Use 254XXXXXXXXX
```

**Cause:** Phone number not in correct international format

**Solution:**
Users can input numbers in multiple formats:
- `0712345678` ✅ (automatically converted to 254712345678)
- `254712345678` ✅ (correct format)  
- `+254712345678` ✅ (plus sign removed)
- `712345678` ✅ (254 prefix added)

**Code Fix:**
```php
private function formatPhoneNumber($phoneNumber): ?string
{
    $phoneNumber = preg_replace('/[\s\-\+]/', '', $phoneNumber);
    if (substr($phoneNumber, 0, 1) === '0') {
        $phoneNumber = '254' . substr($phoneNumber, 1);
    }
    return preg_match('/^254[7][0-9]{8}$/', $phoneNumber) ? $phoneNumber : null;
}
```

### Issue 3: STK Push Timeout
**Error Message:**
```
Request timeout occurred
```

**Cause:** Network connectivity issues or API server problems

**Solution:**
1. Increase timeout in Guzzle client:
   ```php
   $this->client = new Client(['timeout' => 60]);
   ```
2. Implement retry logic for failed requests
3. Check Safaricom API status: https://developer.safaricom.co.ke/

### Issue 4: Callback URL Not Accessible
**Error Message:**
```
Callback failed - URL not reachable
```

**Cause:** M-Pesa cannot reach your callback URL

**Solution:**
For development:
1. Use ngrok to expose local server:
   ```bash
   # Install ngrok
   npm install -g ngrok
   
   # Expose port 8000
   ngrok http 8000
   
   # Use https URL in callback configuration
   ```

2. Update callback URL in code:
   ```php
   'CallBackURL' => 'https://abc123.ngrok.io/callback.php'
   ```

For production:
- Ensure callback URL is publicly accessible
- Use HTTPS (required by M-Pesa)
- Verify SSL certificate is valid

**Resources:**
- [Ngrok Documentation](https://ngrok.com/docs)
- [Safaricom Callback Guide](https://developer.safaricom.co.ke/docs#lipa-na-m-pesa-online-api)

### Issue 5: SSL Verification Errors
**Error Message:**
```
cURL error 60: SSL certificate verification failed
```

**Cause:** SSL certificate verification issues in development environment

**Solution:**
For development only (NOT for production):
```php
$this->client = new Client([
    'timeout' => 30,
    'verify' => false // Only for development
]);
```

For production:
```php
$this->client = new Client([
    'timeout' => 30,
    'verify' => true, // Always verify in production
    'cert' => '/path/to/certificate.pem' // If custom cert needed
]);
```

## 8. References

### Official Documentation
- **[Safaricom Daraja API Portal](https://developer.safaricom.co.ke/)** - Main developer portal with comprehensive API documentation
- **[M-Pesa API Reference](https://developer.safaricom.co.ke/docs)** - Complete API endpoint documentation  
- **[STK Push Guide](https://developer.safaricom.co.ke/docs#lipa-na-m-pesa-online-api)** - Detailed STK Push implementation guide

### PHP Resources
- **[PHP Guzzle HTTP Client](https://docs.guzzlephp.org/en/stable/)** - HTTP client library documentation
- **[Composer Dependency Manager](https://getcomposer.org/doc/)** -