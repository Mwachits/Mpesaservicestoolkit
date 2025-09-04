# Mpesaservicestoolkit
M-Pesa Daraja API integration for Kenya government services


# 🇰🇪 M-Pesa Government Services Payment Toolkit

A PHP-based integration with M-Pesa Daraja API for processing payments for Kenya government services including Business Permits, Certificates of Good Conduct, Driving License Renewals, and Marriage Certificates.

## 🎯 Project Overview

This project demonstrates how to integrate M-Pesa Daraja API with PHP to create a payment gateway for government services. It includes:

- **STK Push Integration** - Send payment prompts directly to customer phones
- **Government Services Configuration** - Pre-configured services with accurate pricing
- **Modern Web Interface** - Responsive HTML frontend with smooth UX
- **Error Handling** - Comprehensive error management and logging
- **Callback Processing** - Handle M-Pesa payment confirmations

## 🏛️ Supported Government Services

| Service | Amount | Processing Time |
|---------|--------|----------------|
| Business Permit Application | KES 2,000 | 7-14 working days |
| Certificate of Good Conduct | KES 1,050 | 2-3 working days |
| Driving License Renewal | KES 3,050 | 1-2 working days |
| Marriage Certificate | KES 500 | Same day service |

## ⚡ Quick Start

### Prerequisites
- PHP 8.0 or higher
- Composer
- Safaricom Developer Account
- WSL/Linux/Mac environment

### 1. Clone Repository
```bash
git clone https://github.com/Mwachits/Mpesaservicestoolkit.git
cd Mpesaservicestoolkit
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
# Copy and edit environment file
cp .env.example .env

# Add your M-Pesa credentials
MPESA_CONSUMER_KEY=your_consumer_key_here
MPESA_CONSUMER_SECRET=your_consumer_secret_here
MPESA_PASSKEY=your_passkey_here
```

### 4. Create Required Directories
```bash
mkdir logs
chmod 755 logs
```

### 5. Run Application
```bash
# Start PHP development server
php -S localhost:8000 -t public/

# Open browser to http://localhost:8000
```

## 📁 Project Structure

```
mpesa-government-services/
├── README.md
├── composer.json
├── .env
├── config/
│   └── services.php          # Government services configuration
├── src/
│   └── MpesaAPI.php          # Core M-Pesa API integration
├── public/
│   ├── index.html            # Frontend payment interface
│   ├── process_payment.php   # Payment processing endpoint  
│   └── callback.php          # M-Pesa callback handler
├── logs/                     # Payment and error logs
└── docs/
    └── TOOLKIT.md            # Detailed documentation
```

## 🔧 API Endpoints

### POST `/process_payment.php`
Process payment request and initiate STK Push

**Parameters:**
- `service_type` - Service identifier (business_permit, good_conduct, etc.)
- `phone_number` - Customer M-Pesa number
- `customer_name` - Customer full name
- `amount` - Payment amount

**Response:**
```json
{
    "success": true,
    "message": "Payment request sent successfully",
    "data": {
        "checkout_request_id": "ws_CO_123456789",
        "account_reference": "BP001_20240904123456_5678"
    }
}
```

### POST `/callback.php`
Handle M-Pesa payment callbacks (webhook)

## 🚀 How It Works

1. **Service Selection** - User selects government service from available options
2. **Payment Form** - User enters M-Pesa number and personal details
3. **STK Push** - System sends payment prompt to user's phone via M-Pesa API
4. **User Confirmation** - User enters M-Pesa PIN to complete payment
5. **Callback Processing** - M-Pesa sends payment confirmation to callback URL
6. **Service Processing** - Government service application is processed

## 📱 Testing

### Sandbox Testing
The project is configured for Safaricom sandbox environment:

- **Test Phone Numbers:** 254708374149, 254708374149
- **Test Amounts:** Any amount from 1-70000 KES
- **Test PIN:** Use your actual M-Pesa PIN in sandbox

### Local Testing
```bash
# Test authentication
php -r "
require_once 'vendor/autoload.php';
\$mpesa = new App\MpesaAPI();
\$token = \$mpesa->getAccessToken();
echo \$token ? 'Auth Success' : 'Auth Failed';
"
```

## 🛡️ Security Features

- **Input Validation** - All user inputs are validated and sanitized
- **Phone Number Formatting** - Automatic formatting to international format
- **Amount Verification** - Prevents payment tampering by validating amounts
- **Error Logging** - Comprehensive logging for debugging and monitoring
- **Environment Variables** - Sensitive credentials stored securely

## 🔍 Troubleshooting

### Common Issues

**1. Authentication Failed**
```
Error: Failed to authenticate with M-Pesa API
Solution: Check your Consumer Key and Consumer Secret in .env file
```

**2. Invalid Phone Number**
```
Error: Invalid phone number format
Solution: Use format 0712345678 or 254712345678
```

**3. STK Push Timeout**
```
Error: Request timeout
Solution: Check network connection and try again
```

### Debug Mode
Enable detailed logging by adding to `.env`:
```bash
DEBUG_MODE=true
LOG_LEVEL=debug
```

## 📚 Learning Resources

- [Safaricom Daraja API Documentation](https://developer.safaricom.co.ke/)
- [PHP Guzzle HTTP Client](https://docs.guzzlephp.org/)
- [M-Pesa Integration Guide](https://developer.safaricom.co.ke/docs)

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🏗️ Built With

- **PHP 8.3** - Backend language
- **Guzzle HTTP** - HTTP client for API requests
- **M-Pesa Daraja API** - Payment processing
- **HTML/CSS/JavaScript** - Frontend interface
- **Composer** - Dependency management

## 📞 Support

For technical support or questions about this implementation:
- Create an issue on GitHub
- Check the troubleshooting section
- Review Safaricom Developer documentation

---

**⚡ Built with AI assistance as part of Moringa School's GenAI Capstone Project**