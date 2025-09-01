![CI](https://github.com/SweetDate-Calendar/sd_php/actions/workflows/ci.yml/badge.svg)

# SweetDate PHP Client SDK

🚧 This package is under active development.  
❗ It is not stable, documented, or supported.  
🧪 Use at your own risk – or wait for the `v1.0` release.

The official PHP SDK for the [SweetDate Calendar Engine](https://sweetdate.io/).  
This package provides a simple client for interacting with the SweetDate REST API.

---

## Requirements

- PHP **8.1+** (tested on 8.1, 8.2, 8.3)  
- Composer for dependency management  
- cURL extension enabled (required by Guzzle)  

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
