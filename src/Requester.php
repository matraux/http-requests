<?php declare(strict_types = 1);

namespace Matraux\HttpRequests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Matraux\HttpRequests\Request\Request;
use Matraux\HttpRequests\Request\RequestCollection;
use Matraux\HttpRequests\Response\Response;
use Matraux\HttpRequests\Response\ResponseCollection;
use Matraux\HttpRequests\Utils\Config;
use Matraux\HttpRequests\Utils\Events;
use Matraux\HttpRequests\Utils\Headers;

final class Requester
{

	public readonly Config $config;

	public readonly Headers $headers;

	public readonly Events $onBefore;

	public readonly Events $onAfter;

	protected Client $client
	{
		get {
			return $this->client ??= new Client(iterator_to_array($this->config));
		}
	}

	protected function __construct()
	{
		$this->config = Config::create();
		$this->headers = Headers::create();
		$this->onAfter = Events::create();
		$this->onBefore = Events::create();
	}

	public static function create(): Requester
	{
		return new static();
	}

	public function send(Request $request): Response
	{
		($this->onBefore)();

		$promise = $this->createPromise($request);

		/** @var array{state:string,value?:GuzzleResponse,reason?:GuzzleException} $psrResponse */
		$psrResponse = current((array) Utils::settle($promise)->wait());

		$response = $this->createResponse($psrResponse);

		($this->onAfter)();

		return Response::create($response, $request);
	}

	public function sendBatch(RequestCollection $requests): ResponseCollection
	{
		($this->onBefore)();

		$promises = [];
		foreach ($requests as $index => $request) {
			$promises[$index] = $this->createPromise($request);
		}

		/** @var array<int|string,array{state:string,value?:GuzzleResponse,reason?:GuzzleException}> $psrResponses */
		$psrResponses = (array) Utils::settle($promises)->wait();

		$responses = [];
		foreach ($psrResponses as $index => $psrResponse) {
			$response = $this->createResponse($psrResponse);
			$responses[$index] = Response::create($response, $requests[$index]);
		}

		($this->onAfter)();

		return ResponseCollection::create($responses);
	}

	protected function createPromise(Request $request): PromiseInterface
	{
		$headers = $request->headers;
		foreach ($this->headers as $index => $value) {
			$headers[$index] = $value;
		}

		($request->onBefore)();

		$method = is_string($request->method) ? $request->method : $request->method->value;
		$psr7Request = new GuzzleRequest($method, (string) $request->uri, iterator_to_array($request->headers), $request->body);
		$promise = $this->client->sendAsync($psr7Request);
		$promise->then(
			function (GuzzleResponse $response) use ($request): void {
				($request->onSuccess)($response);
			},
			function (GuzzleException $exception) use ($request): void {
				($request->onFail)($exception);
			}
		);

		$promise->then($request->onAfter, $request->onAfter);

		return $promise;
	}

	/**
	 * @param array{state:string,value?:GuzzleResponse,reason?:GuzzleException} $data
	 */
	protected function createResponse(array $data): GuzzleResponse
	{
		if ($response = $data['value'] ?? null) {
			return $response;
		} elseif ($exception = $data['reason'] ?? null) {
			return $exception instanceof RequestException && $exception->getResponse() instanceof GuzzleResponse ?
				$exception->getResponse() :
				new GuzzleResponse(500, [], $exception->getMessage(), '1.1', $exception->getMessage());
		}

		return new GuzzleResponse(500, [], 'Invalid response data', '1.1', 'Invalid response data');
	}

}
