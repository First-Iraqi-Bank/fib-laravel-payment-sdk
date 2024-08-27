# FIB Payment SDK

The FIB Payment SDK provides seamless integration with the FIB payment system, empowering developers to streamline payment transactions and facilitate secure refunds within their applications.

## Features

- **Payment Transactions**: Enable users to make payments securely through the FIB payment system, handling transactions effortlessly within your application.

- **Refund Processing**: Process refunds securely through the FIB payment system, managing transactions efficiently within your application.

## Installation

To integrate the SDK into your project, install it via Composer:

```bash
composer require First-Iraqi-Bank/fib-php-payment-sdk
```


### Configuration
To configure the SDK, you need to set the following environment variables:

  - `FIB_API_KEY`: Your FIB payment API key.
  - `FIB_API_SECRET`: Your FIB payment API secret.
  - `FIB_BASE_URL`: The base URL for the FIB payment API (default: https://api.fibpayment.com).
  - `FIB_GRANT_TYPE`: The grant type for authentication with the FIB payment API (default: client_credentials).
  - `FIB_REFUNDABLE_FOR`: The period for which transactions can be refunded (default: P7D, which stands for 7 days).
  - `FIB_CURRENCY`: The currency used for transactions with the FIB payment system (default: IQD).
  - `FIB_CALLBACK_URL`: The callback URL for handling payment notifications from the FIB payment system.
  - `FIB_ACCOUNT`: The FIB payment account identifier.

Make sure to set these environment variables appropriately in your application's environment configuration.

### Publishing Configuration File
To customize the FIB Payment SDK configuration according to your application's requirements, you can publish the configuration file to your Laravel project. Follow the steps below to publish the configuration file:

Run the Command:

Open your terminal and navigate to your Laravel project's directory. Then, run the following Artisan command:

```bash
  php artisan vendor:publish --tag=fib-payment-sdk-config
```

This command will publish the configuration file to your Laravel project.

#### Locate the Configuration File:

After running the command, you can find the published configuration file in the config directory of your Laravel project.

#### Customize Configuration:

Open the published configuration file (config/fib.php) in your preferred text editor.
You can modify the configuration options according to your needs.
Ensure that you set the necessary environment variables in your .env file
or provide default values directly in the configuration file.

### Documentation
For more information on how to use the SDK, refer to the full documentation.

Testing
To ensure the SDK functions correctly, run tests using PHPUnit:
```bash
  phpunit
```

### Contributing
Contributions are welcome! Please read CONTRIBUTING.md for details on our code of conduct, and the process for submitting pull requests.

### License
This project is licensed under the MIT License - see the LICENSE.md file for details.

### Support
For support, please contact support@fib-payment.com or visit our website.

### Acknowledgments
Thanks to the FIB Payment development team for their contributions.
This SDK uses the Guzzle HTTP client library for API requests.

### Versioning
We use SemVer for versioning. For the versions available, see the tags on this repository.

### FAQ
#### Q: How do I obtain an API key for the FIB Payment system?
A: Please contact our support team at support@fib-payment.com to request an API key.

#### Q: Can I use this SDK in a production environment?
A: Yes, the SDK is designed for use in production environments, but please ensure you have configured it correctly and have obtained the necessary credentials.
