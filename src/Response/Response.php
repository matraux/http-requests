<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Response;

use Matraux\HttpRequests\Request\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final readonly class Response implements ResponseInterface
{

	protected function __construct(
		public ResponseInterface $psrResponse,
		public Request $request
	)
	{
	}

	public static function create(ResponseInterface $psrResponse, Request $request): static
	{
		return new static($psrResponse, $request);
	}

	public function getStatusCode(): int
	{
		return $this->psrResponse->getStatusCode();
	}

	public function withStatus(int $code, string $reasonPhrase = ''): static
	{
		return static::create($this->psrResponse->withStatus($code, $reasonPhrase), $this->request);
	}

	public function getReasonPhrase(): string
	{
		return $this->psrResponse->getReasonPhrase();
	}

	public function getProtocolVersion(): string
	{
		return $this->psrResponse->getProtocolVersion();
	}

	public function withProtocolVersion(string $version): static
	{
		return static::create($this->psrResponse->withProtocolVersion($version), $this->request);
	}

	public function getHeaders(): array
	{
		return $this->psrResponse->getHeaders();
	}

	public function hasHeader(string $name): bool
	{
		return $this->psrResponse->hasHeader($name);
	}

	public function getHeader(string $name): array
	{
		return $this->psrResponse->getHeader($name);
	}

	public function getHeaderLine(string $name): string
	{
		return $this->psrResponse->getHeaderLine($name);
	}

	public function withHeader(string $name, $value): static
	{
		return static::create($this->psrResponse->withHeader($name, $value), $this->request);
	}

	public function withAddedHeader(string $name, $value): static
	{
		return static::create($this->psrResponse->withAddedHeader($name, $value), $this->request);
	}

	public function withoutHeader(string $name): static
	{
		return static::create($this->psrResponse->withoutHeader($name), $this->request);
	}

	public function getBody(): StreamInterface
	{
		return $this->psrResponse->getBody();
	}

	public function withBody(StreamInterface $body): static
	{
		return static::create($this->psrResponse->withBody($body), $this->request);
	}

}
