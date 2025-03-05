<p align="center"><a href="https://codenteq.com" target="_blank"><img src="src/Resources/assets/images/paytr.svg" width="288"></a></p>

# PayTR Payment Gateway
[![License](https://poser.pugx.org/codenteq/paytr-payment-gateway/license)](https://github.com/codenteq/paytr-payment-gateway/blob/master/LICENSE)
[![Total Downloads](https://poser.pugx.org/codenteq/paytr-payment-gateway/d/total)](https://packagist.org/packages/codenteq/paytr-payment-gateway)

## 1. Introduction:

Install this package now to receive secure payments in your online store. PayTR offers an easy and secure payment gateway.

## 2. Requirements:

* **PHP**: 8.1 or higher.
* **Bagisto**: v2.*
* **Composer**: 1.6.5 or higher.

## 3. Installation:

- Run the following command
```
composer require codenteq/paytr-payment-gateway
```

- Run these commands below to complete the setup
```
composer dump-autoload
```

> WARNING <br>
> It will check existence of the .env file, if it exists then please update the file manually with the below details.
```
PAYTR_MERCHANT_ID=
PAYTR_MERCHANT_KEY=
PAYTR_MERCHANT_SALT=
```

- Run these commands below to complete the setup
```
php artisan optimize
```

## Installation without composer:

- Unzip the respective extension zip and then merge "packages" and "storage" folders into project root directory.
- Goto config/app.php file and add following line under 'providers'

```
Webkul\PayTR\Providers\PayTRServiceProvider::class,
```

- Goto composer.json file and add following line under 'psr-4'

```
"Webkul\\PayTR\\": "packages/Webkul/PayTR/src"
```

- Run these commands below to complete the setup

```
composer dump-autoload
```

> WARNING <br>
> It will check existence of the .env file, if it exists then please update the file manually with the below details.
```
PAYTR_MERCHANT_ID=
PAYTR_MERCHANT_KEY=
PAYTR_MERCHANT_SALT=
```

```
php artisan optimize
```

> That's it, now just execute the project on your specified domain.

## How to contribute
PayTR Payment Gateway is always open for direct contributions. Contributions can be in the form of design suggestions, documentation improvements, new component suggestions, code improvements, adding new features or fixing problems. For more information please check our [Contribution Guideline document.](https://github.com/codenteq/paytr-payment-gateway/blob/master/CONTRIBUTING.md)
