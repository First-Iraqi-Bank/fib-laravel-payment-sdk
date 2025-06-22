# FIB Laravel Payment SDK

The FIB Laravel Payment SDK provides a seamless integration with the FIB payment system for Laravel applications, enabling secure and efficient payment transactions and refund handling.

**Table of Contents**
- [Features](#features)
- [Installation](#installation)
  - [Composer Installation](#composer-installation)
  - [Alternative Installation (Without Composer)](#alternative-installation-without-composer)
- [Registering the Service Provider and Running Migrations](#Registering-the-Service-Provider-and-Running-Migrations)
- [Usage](#usage)
  - [Creating a Payment](#creating-a-payment)
  - [Checking Payment Status](#checking-payment-status)
  - [Refunding a Payment](#refunding-a-payment)
  - [Cancelling a Payment](#cancelling-a-payment)
  - [Handling Payment Callbacks](#handling-payment-callbacks)
- [FIB Payment Documentation](#fib-payment-documentation)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)
- [Acknowledgments](#acknowledgments)
- [Versioning](#versioning)
- [FAQ](#faq)

## Features

- **Payment Transactions**: Facilitate secure payments through the FIB payment system directly in your Laravel application.
- **Refund Processing**: Manage refunds through the FIB payment system with ease.
- **Payment Status Checking**: Retrieve the status of payments to ensure proper transaction tracking.
- **Payment Cancellation**: Cancel payments as needed through the FIB payment system.

## Installation

To integrate the SDK into your Laravel project, install it via Composer:

```bash
composer require First-Iraqi-Bank/fib-laravel-payment-sdk
```

### Alternative Installation (Without Composer)
If you prefer not to use Composer, follow these steps:

- **Clone the Repository**: Clone the FIB Payment SDK repository:

  ```bash
  git clone https://github.com/First-Iraqi-Bank/fib-laravel-payment-sdk.git
  ```

- **Include in Your Project**: Move or copy the cloned `fib-laravel-payment-sdk` directory into your Laravel project.

- **Autoloading**: Ensure that the `src` directory of the SDK is included in your `composer.json` autoloader configuration if not using Composer:

  ```json
  {
      "autoload": {
          "psr-4": {
              "FirstIraqiBank\\FIBPaymentSDK\\": "path/to/fib-laravel-payment-sdk/src"
          }
      }
  }
  ```




- **Usage**: After including the SDK, use its classes and functionality in your Laravel application.


## Registering the Service Provider and Running Migrations

### Step 1: Register the Service Provider
Before using the SDK, ensure that you register the `FIBPaymentServiceProvider`. This service provider binds the SDK's services into the Laravel service container and loads necessary resources like routes, migrations, and configurations.

#### For Laravel 10 and Lower:
In your config/app.php file, add the following to the providers array:

```php
'providers' => [
    // Other Service Providers...

    FirstIraqiBank\FIBPaymentSDK\FIBPaymentServiceProvider::class,
],
```

#### For Laravel 11 and Higher:
In Laravel 11, service providers are registered in the bootstrap/providers.php file. To register the FIBPaymentServiceProvider, add it to the returned array in the bootstrap/providers.php file like this:

```<?php

return [
    // Other service providers...

    FirstIraqiBank\FIBPaymentSDK\FIBPaymentServiceProvider::class,
];
```

### Step 2: Publish the Configuration
To customize the SDK's configuration, you need to publish the configuration file. Run the following Artisan command:

```shell
php artisan vendor:publish --tag=fib-payment-sdk-config
```

This will publish the SDK configuration file to your application's `config/fib.php` file, where you can modify the SDK's behavior according to your needs.

### Step 3: Running Migrations
The SDK comes with migration files that create the necessary database tables. To run these migrations, use the following Artisan command:

```shell
php artisan migrate
```

This command will execute the migration files located in the SDK's `database/migrations` directory and create the required database tables.



Add the following environment variables to your `.env` file:

- `FIB_API_KEY`: Your FIB payment API key.
- `FIB_API_SECRET`: Your FIB payment API secret.
- `FIB_BASE_URL`: The base URL for the FIB payment API (default: https://api.fibpayment.com).
- `FIB_GRANT_TYPE`: The grant type for authentication (default: client_credentials).
- `FIB_REFUNDABLE_FOR`: The period for which transactions can be refunded (default: P7D).
- `FIB_CURRENCY`: The currency used for transactions (default: IQD).
- `FIB_CALLBACK_URL`: The callback URL for payment notifications.
- `FIB_DEFAULT_ACCOUNT`: The FIB default payment account identifier.

### Usage of the SDK

#### Ensure Dependencies are Installed:
Install required dependencies using Composer:

```bash
composer install
```

#### Set Up Environment Variables:
Create a `.env` file in the root directory of your Laravel project and set the necessary environment variables.

#### Creating a Payment

Here's an example of how to create a payment:

```php
<?php

use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

protected $paymentService;

// Inject the FIBPaymentIntegrationService in your controller's construct and FIBAuthIntegrationService for using multi account funtionality.
public function __construct(FIBPaymentIntegrationService $paymentService, FIBAuthIntegrationService $fibAuthIntegrationService)
{
    $this->paymentService = $paymentService;
    $this->fibAuthIntegrationService = $fibAuthIntegrationService;
}

try {
    
    //call the setAccount method of the FIBAuthIntegrationService for using another account rather than default one
    $this->fibAuthIntegrationService->setAccount('second_account');
     
    // Call the createPayment method of the FIBPaymentIntegrationService
    $response = $this->paymentService->createPayment(1000, 'http://localhost/callback', 'Your payment description', 'http://localhost/redirectUri');

    $paymentData = json_decode($response->getBody(), true);

    // Return a response with the payment details and structure it as per your need.
    if($response->successful()) {
        return response()->json([
            'message' => 'Payment created successfully!',
            'payment' => $paymentData,
        ]);
    }
} catch (Exception $e) {
    // Handle any errors that might occur.
    return response()->json([
        'message' => 'Error creating payment: ' . $e->getMessage()
    ], 500);
}
```

#### Checking the Payment Status

To check the status of a payment:

```php
<?php

use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

protected $paymentService;

// Inject the FIBPaymentIntegrationService in your controller's construct and FIBAuthIntegrationService for using multi account funtionality.
public function __construct(FIBPaymentIntegrationService $paymentService, FIBAuthIntegrationService $fibAuthIntegrationService)
{
    $this->paymentService = $paymentService;
    $this->fibAuthIntegrationService = $fibAuthIntegrationService;
}

try {
    $paymentId = 'your_payment_id'; // Retrieve from your storage
    
    //call the setAccount method of the FIBAuthIntegrationService for using another account rather than default one
    $this->fibAuthIntegrationService->setAccount('second_account');

    // Call the checkPaymentStatus method of the FIBPaymentIntegrationService
    $response = $this->paymentService->checkPaymentStatus($paymentId);
    $paymentData = json_decode($response->getBody(), true);

    //return the status and structure it as per your need.
    if($response->successful()) {
        return response()->json([
            'status' => $paymentData['status'],
        ]);
    } else{
        return response()->json([
            'data' => $paymentData,
        ]);
    }

} catch (Exception $e) {
    // Handle any errors that might occur
    return response()->json([
        'message' => 'Error creating payment: ' . $e->getMessage()
    ], 500);
}
```

#### Refunding a Payment

To process a refund:

```php
<?php

use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

protected $paymentService;

// Inject the FIBPaymentIntegrationService in your controller's construct and FIBAuthIntegrationService for using multi account funtionality.
public function __construct(FIBPaymentIntegrationService $paymentService, FIBAuthIntegrationService $fibAuthIntegrationService)
{
    $this->paymentService = $paymentService;
    $this->fibAuthIntegrationService = $fibAuthIntegrationService;
}

try {
    $paymentId = 'your_payment_id'; // Retrieve from your storage
    
    //call the setAccount method of the FIBAuthIntegrationService for using another account rather than default one
    $this->fibAuthIntegrationService->setAccount('second_account');
     
    $response = $this->paymentService->refund($paymentId);
    echo "Refund Payment Status: " . $response;
} catch (Exception $e) {
    echo "Error Refunding payment: " . $e->getMessage();
}
```

#### Cancelling a Payment

To cancel a payment:

```php
<?php

use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;

protected $paymentService;

// Inject the FIBPaymentIntegrationService in your controller's construct and FIBAuthIntegrationService for using multi account funtionality.
public function __construct(FIBPaymentIntegrationService $paymentService, FIBAuthIntegrationService $fibAuthIntegrationService)
{
    $this->paymentService = $paymentService;
    $this->fibAuthIntegrationService = $fibAuthIntegrationService;
}

try {
    $paymentId = 'your_payment_id'; // Retrieve from your storage

    //call the setAccount method of the FIBAuthIntegrationService for using another account rather than default one
    $this->fibAuthIntegrationService->setAccount('second_account');
     
    // Call the cancelPayment method of the FIBPaymentIntegrationService
    $response = $this->paymentService->cancelPayment($paymentId);

    //return the response and structure it as per your need.
    if (in_array($response->getStatusCode(), [200, 201, 202, 204])) {
        return response()->json([
            'message' => "payment canceled Successfully",
        ]);
    } else {
        return response()->json([
            'message' => "payment cancelation faild ",
            'data' => $response->json()
        ]);
    }
} catch (Exception $e) {
    // Handle any errors that might occur
    return response()->json([
        'message' => 'Error creating payment: ' . $e->getMessage()
    ], 500);
}
```

#### Handling Payment Callbacks

To handle payment callbacks, create a route and controller method:

```php
// web.php or api.php
Route::post('/callback', [PaymentController::class, 'handleCallback']);

// PaymentController.php
public function handleCallback(Request $request)
{
    $payload = $request->all();

    $paymentId = $payload['id'] ?? null;
    $status = $payload['status'] ?? null;

    if (!$paymentId || !$status) {
        return response()->json(['error' => 'Invalid callback payload'], 400);
    }

    try {
        // Implement your callback handling logic
        return response()->json(['message' => 'Callback processed successfully']);
    } catch (Exception $e) {
        return response()->json(['error' => 'Failed to process callback: ' . $e->getMessage()], 500);
    }
}
```

### FIB Payment Documentation

For detailed documentation on FIB Online Payment, refer to the [full documentation](https://documenter.getpostman.com/view/18377702/UVCB93tc).

### Testing

Run tests using PHPUnit:

```bash
vendor/bin/phpunit --testdox
```

### Contributing

Contributions are welcome! Please read `CONTRIBUTING.md` for details on our code of conduct and the process for submitting pull requests.

### License

This project is licensed under the MIT License. See the [LICENSE.md](LICENSE.md) file for details.

### Support

For support, please contact support@fib-payment.com or visit our website.

### Acknowledgments

Thanks to the FIB Payment development team for their contributions. This SDK uses the cURL library for API requests.

### Versioning

We use semantic versioning (SemVer) principles. For available versions, see the tags on this repository.

### FAQ

**Q: How do I get an API key for the FIB Payment system?**

A: Contact our support team at support@fib-payment.com to request an API key.

**Q: Can I use this SDK in a production environment?**

A: Yes, the SDK is designed for production, but ensure it is configured correctly and you have the necessary credentials.
