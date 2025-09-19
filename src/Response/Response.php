<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Response;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Utils\Headers;
use Psr\Http\Message\StreamInterface;

final readonly class Response
{

	public int $code;

	public string $reason;

	public string $protocolVersion;

	public StreamInterface $body;

	public Headers $headers;

	protected function __construct(
		GuzzleResponse $response,
		public Request $request
	)
	{
		$this->code = $response->getStatusCode();
		$this->reason = $response->getReasonPhrase();
		$this->protocolVersion = $response->getProtocolVersion();
		$this->body = $response->getBody();
		$headers = $this->headers = Headers::create();
		foreach($response->getHeaders() as $index => $value) {
			$headers[$index] = $value;
		}
	}

	public static function create(GuzzleResponse $response, Request $request): static
	{
		return new static($response, $request);
	}

}