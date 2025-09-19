<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Request;

use Stringable;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Matraux\HttpRequests\Utils\Events;
use Matraux\HttpRequests\Utils\Headers;
use GuzzleHttp\Exception\GuzzleException;

final class Request
{

	public ?string $body = null;

	public readonly Headers $headers;

	public readonly Events $onAfter;

	public readonly Events $onBefore;

	/** @var Events<callable(GuzzleException $exception):void> */
	public readonly Events $onFail;

	/** @var Events<callable(GuzzleResponse $response):void> */
	public readonly Events $onSuccess;

	protected function __construct(
		public readonly Method|string $method,
		public readonly string|Stringable $uri
	)
	{
		$this->headers = Headers::create();
		$this->onAfter = Events::create();
		$this->onBefore = Events::create();
		$this->onFail = Events::create();
		$this->onSuccess = Events::create();
	}

	public static function create(Method|string $method, string|Stringable $uri): static
	{
		return new static($method, $uri);
	}

}
