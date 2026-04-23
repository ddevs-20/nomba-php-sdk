# Nomba PHP SDK

A PHP SDK for integrating with the [Nomba](https://nomba.com) payment API. This SDK provides a convenient way to accept payments, manage transactions, create virtual accounts, handle transfers, and more in your PHP applications.

## Features

- **Authentication** – OAuth2 client-credentials and PKCE flow support
- **Accept Payments** – Card payments (Visa, Verve, Mastercard), USSD, bank transfers, and QR payments
- **Checkout** – Seamless checkout integration
- **Virtual Accounts** – Create and manage dynamic virtual accounts with BVN verification and expected amount support
- **Transfers** – Initiate and manage transfers (V2 API)
- **Direct Debit** – Recurring billing and scheduled payments with prior authorization
- **Transaction Management** – Verify, requery, and fetch transaction history
- **Webhooks** – Receive and verify webhook events with signature verification
- **Sub-Accounts** – Manage sub-accounts for split settlements
- **Terminal Actions** – Inject custom logic at specific transaction stages
- **Betting API** – Betting-related operations
- **Cable TV** – Fetch cable TV plans

## Requirements

- PHP >= 7.4
- Composer
- cURL extension enabled

## Installation

Install via Composer:

```bash
composer require ddevs-20/nomba-php-sdk
```

## Quick Start

### 1. Obtain API Keys

Sign up at [Nomba Developers](https://developer.nomba.com) to get your:
- Client ID
- Client Secret
- Account ID (for sandbox/production environments)

### 2. Initialize the SDK

```php
<?php
require_once 'vendor/autoload.php';

use Nomba\NombaClient;

$client = new NombaClient([
    'client_id'     => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'account_id'    => 'your-account-id',
    'environment'   => 'sandbox', // or 'production'
]);

// Authenticate
$client->authenticate();
```

### 3. Create a Payment Order

```php
$order = $client->payments->createOrder([
    'amount'       => 10000, // Amount in kobo
    'currency'     => 'NGN',
    'customerEmail'=> 'customer@example.com',
    'callbackUrl'  => 'https://yourdomain.com/callback',
    'description'  => 'Payment for order #12345',
]);

echo "Order Reference: " . $order['data']['orderRef'];
```

### 4. Charge a Card

```php
$charge = $client->payments->chargeCard([
    'orderRef'     => $order['data']['orderRef'],
    'card'         => [
        'number'      => '5399838383838381',
        'expiryMonth' => '12',
        'expiryYear'  => '2025',
        'cvv'         => '123',
        'pin'         => '1234',
    ],
]);

echo "Transaction Reference: " . $charge['data']['transactionRef'];
```

### 5. Verify a Transaction

```php
$verification = $client->transactions->verify($charge['data']['transactionRef']);

if ($verification['data']['status'] === 'successful') {
    echo "Payment successful!";
}
```

## Virtual Accounts

### Create a Virtual Account

```php
$virtualAccount = $client->virtualAccounts->create([
    'accountName'    => 'John Doe',
    'bvn'            => '12345678901', // Optional: for BVN verification
    'expectedAmount' => 500000,        // Optional: expected incoming amount
    'currency'       => 'NGN',
]);

echo "Virtual Account Number: " . $virtualAccount['data']['accountNumber'];
```

### Fetch Virtual Account Transactions

```php
$transactions = $client->virtualAccounts->fetchTransactions([
    'accountNumber' => $virtualAccount['data']['accountNumber'],
    'from'          => '2025-01-01',
    'to'            => '2025-12-31',
]);
```

### Lookup Virtual Account

```php
$lookup = $client->virtualAccounts->lookup([
    'accountNumber' => $virtualAccount['data']['accountNumber'],
]);
```

## Transfers

### Initiate a Transfer

```php
$transfer = $client->transfers->initiate([
    'amount'          => 100000, // Amount in kobo
    'currency'        => 'NGN',
    'destinationBankCode' => '057', // Zenith Bank
    'destinationAccountNumber' => '0123456789',
    'narration'       => 'Salary payment for March',
]);

echo "Transfer Reference: " . $transfer['data']['transactionRef'];
```

### Check Transfer Status

```php
$status = $client->transfers->getStatus($transfer['data']['transactionRef']);
echo "Transfer Status: " . $status['data']['status'];
```

## Webhooks

### Setup Webhook Handler

```php
use Nomba\Webhook;

$webhook = new Webhook('your-webhook-secret');

// Verify webhook signature
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_NOMBA_SIGNATURE'] ?? '';

if ($webhook->verify($payload, $signature)) {
    $event = json_decode($payload, true);

    switch ($event['event']) {
        case 'payment_success':
            // Handle successful payment
            handlePaymentSuccess($event['data']);
            break;
        case 'transfer_success':
            // Handle successful transfer
            handleTransferSuccess($event['data']);
            break;
        // ... handle other events
    }

    http_response_code(200);
} else {
    http_response_code(400);
    echo "Invalid signature";
}
```

### Webhook Re-push

```php
$client->webhooks->rePush([
    'transactionRef' => 'your-transaction-ref',
]);
```

## Direct Debit

### Create a Direct Debit Mandate

```php
$mandate = $client->directDebit->createMandate([
    'customerEmail'   => 'customer@example.com',
    'customerName'    => 'John Doe',
    'accountNumber'   => '0123456789',
    'bankCode'        => '057',
    'amount'          => 100000,
    'startDate'       => '2025-05-01',
    'endDate'         => '2025-12-31',
    'frequency'       => 'monthly', // daily, weekly, monthly
]);
```

## Terminal Actions

```php
$terminalAction = $client->terminals->createAction([
    'terminalId' => 'your-terminal-id',
    'action'     => 'pre_payout_validation',
    'payload'    => [
        // Custom validation logic
    ],
]);
```

## Error Handling

The SDK throws exceptions for API errors:

```php
use Nomba\Exceptions\NombaApiException;
use Nomba\Exceptions\AuthenticationException;
use Nomba\Exceptions\ValidationException;

try {
    $order = $client->payments->createOrder([...]);
} catch (AuthenticationException $e) {
    // Handle authentication errors (401)
    echo "Authentication failed: " . $e->getMessage();
} catch (ValidationException $e) {
    // Handle validation errors (422)
    echo "Validation error: " . $e->getMessage();
    print_r($e->getErrors());
} catch (NombaApiException $e) {
    // Handle other API errors
    echo "API Error (" . $e->getCode() . "): " . $e->getMessage();
}
```

## Configuration Options

```php
$client = new NombaClient([
    'client_id'      => 'your-client-id',
    'client_secret'  => 'your-client-secret',
    'account_id'     => 'your-account-id',
    'environment'    => 'sandbox', // 'sandbox' or 'production'
    'timeout'        => 30,        // Request timeout in seconds
    'base_url'       => null,      // Override base URL (optional)
]);
```

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test -- --coverage-html coverage
```

## Sandbox Environment

Use the sandbox environment for testing:

```php
$client = new NombaClient([
    'client_id'     => 'your-sandbox-client-id',
    'client_secret' => 'your-sandbox-client-secret',
    'account_id'    => 'your-sandbox-account-id',
    'environment'   => 'sandbox',
]);
```

Test card details for sandbox:
- **Card Number**: `5399838383838381`
- **Expiry**: `12/2025`
- **CVV**: `123`
- **PIN**: `1234`
- **OTP**: `123456`

## Documentation

- [Nomba API Documentation](https://developer.nomba.com)
- [Nomba API Reference](https://developer.nomba.com/nomba-api-reference/introduction)
- [Nomba API Changelog](https://developer.nomba.com/nomba-api-changelog/api/updates)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and follow the existing code style.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please:
- Open an issue on [GitHub Issues](https://github.com/ddevs-20/nomba-php-sdk/issues)
- Contact Nomba support at [developer.nomba.com](https://developer.nomba.com)

## Author

**ddevs-20**

- GitHub: [@ddevs-20](https://github.com/ddevs-20)
- Repository: [nomba-php-sdk](https://github.com/ddevs-20/nomba-php-sdk)
- [Demfati](https://demfati.com)

---

<p align="center">Built with ❤️ by Demfati for the Nomba developer community</p>
