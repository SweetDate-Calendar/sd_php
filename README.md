# SweetDate PHP Client SDK

The official PHP SDK for the [SweetDate Calendar Engine](https://sweetdate.io/).  
This package provides a simple client for interacting with the SweetDate REST API.

---

## Installation

Add the SDK to your project using [Composer](https://getcomposer.org/):

```bash
composer require sweetdate/client
```

---

## Usage

Example: list tenants

```php
<?php

require 'vendor/autoload.php';

use SweetDate\Client;

$client = new Client([
    'base_url' => 'https://api.sweetdate.io',
    'api_key'  => getenv('SWEETDATE_API_KEY'),
]);

$tenants = $client->tenants()->list([
    'limit'  => 10,
    'offset' => 0,
]);

print_r($tenants);
```

---

## Development

### 1) Clone and install
```bash
git clone https://github.com/SweetDate-Calendar/sd_php.git
cd sd_php
```

### 2) Install deps & init Pest
```bash
composer validate
composer install
vendor/bin/pest --init
```

### 3) Run tests
```bash
composer test
```

### 4) Run static analysis
```bash
composer stan
```

### 5) Run linter
```bash
composer lint
```

---

## License

This project is licensed under the MIT License.  
See the [LICENSE](./LICENSE) file for details.
