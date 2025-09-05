# MATRAUX JSON ORM
[![Latest Version on Packagist](https://img.shields.io/packagist/v/matraux/http-requests.svg?logo=packagist&logoColor=white)](https://packagist.org/packages/matraux/http-requests)
[![Last release](https://img.shields.io/github/v/release/matraux/http-requests?display_name=tag&logo=github&logoColor=white)](https://github.com/matraux/http-requests/releases)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg?logo=open-source-initiative&logoColor=white)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.4+-blue.svg?logo=php&logoColor=white)](https://php.net)
[![Security Policy](https://img.shields.io/badge/Security-Policy-blue?logo=bitwarden&logoColor=white)](./.github/SECURITY.md)
[![Contributing](https://img.shields.io/badge/Contributing-Disabled-lightgrey?logo=github&logoColor=white)](CONTRIBUTING.md)
[![QA Status](https://img.shields.io/github/actions/workflow/status/matraux/http-requests/qa.yml?label=Quality+Assurance&logo=checkmarx&logoColor=white)](https://github.com/matraux/http-requests/actions/workflows/qa.yml)
[![Issues](https://img.shields.io/github/issues/matraux/http-requests?logo=github&logoColor=white)](https://github.com/matraux/http-requests/issues)
[![Last Commit](https://img.shields.io/github/last-commit/matraux/http-requests?logo=git&logoColor=white)](https://github.com/matraux/http-requests/commits)

<br>

## Introduction
A PHP 8.4+ library for working with HTTP requests and responses in a structured, type‑safe way.
Provides easy creation of single or batch HTTP requests, ergonomic collections, and seamless integration with PSR‑7 and Guzzle.
Includes built‑in event hooks (onBefore, onAfter, onSuccess, onFail) for logging, debugging, authentication, or custom processing at each stage of the request lifecycle.
Errors from the transport layer are normalized into PSR‑7 responses (no exceptions are thrown from send()/sendBatch()), making the flow predictable in both single and parallel/batch scenarios.

<br>

## Features
- Object‑oriented HTTP request/response wrappers (PSR‑7 compatible); Response also keeps the original Request
- Type‑safe API: Method enum or string, Stringable URI, strict containers for headers, config, and collections
- Ergonomic collections: RequestCollection (mutable) and ResponseCollection (read‑only)
- Easy batching and parallel execution via Guzzle promises (sendAsync) and Utils::settle()
- Built‑in events: onBefore, onAfter, onSuccess(ResponseInterface), onFail(GuzzleException) for hooks, logging, authentication, and custom logic
- Default headers merging: headers set on Requester are applied to every Request right before sending
- Unified error handling: exceptions are mapped to a PSR‑7 Response; when no response is available, a synthetic 500 is created with the exception message
- Simple API for reading status, headers, and body (getStatusCode(), getHeaderLine(), getBody())
- Lazy Guzzle client configured through a typed Config container
- Designed for easy testing and debugging; event handler failures are isolated

<br>

## Installation
```bash
composer require matraux/http-requests
```

<br>

## Requirements
| version | PHP | Note
|----|---|---
| 1.0.0 | 8.4+ | Initial release

<br>

## Examples

### Single request

```php
use Matraux\HttpRequests\Requester;
use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;

$requester = Requester::create();
$request = Request::create(Method::Get, 'https://www.example.com');
$response = $requester->send($request);

echo $response->getStatusCode();        // e.g. 200, 404, 500, ...
echo $response->getBody()->getContents(); // Response body
echo $response->getHeaderLine('Content-Type'); // e.g. text/html, application/json
```

### Batch requests (collections)
```php
use Matraux\HttpRequests\Requester;
use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Request\RequestCollection;

$requester = Requester::create();
$requests = RequestCollection::create();
$requests[] = Request::create(Method::Get, 'https://www.example.com/?page=1');
$requests[] = Request::create(Method::Get, 'https://www.example.com/?page=10');
$requests['custom'] = Request::create(Method::Get, 'https://www.example.com/?page=100');

$responses = $requester->sendBatch($requests);

foreach ($responses as $index => $response) {
	echo "$index: " . $response->getStatusCode() . "\n";
}

echo $responses['custom']->getBody()->getContents();
```

### Using events
```php
use Matraux\HttpRequests\Requester;
use Matraux\HttpRequests\Request\Method;
use Matraux\HttpRequests\Request\Request;
use Psr\Http\Message\ResponseInterface

$requester = Requester::create();
$requester->onBefore[] = function() {
	echo "Sending request...\n";
};

$request = Request::create(Method::Get, 'https://www.example.com');
$request->onBefore[] = function() use ($request) {
	echo "Requesting URL: " . $request->uri . "\n";
};
$request->onSuccess[] = function(ResponseInterface $psrResponse): void {
	echo "Success response: " . $psrResponse->getStatusCode() . "\n";
};

$response = $requester->send($request);
```

<br>

## Development
See [Development](./docs/Development.md) for debug, test instructions, static analysis, and coding standards.

<br>

## Support
For bug reports and feature requests, please use the [issue tracker](https://github.com/matraux/http-requests/issues).