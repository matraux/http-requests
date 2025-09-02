<?php declare(strict_types=1);

namespace Matraux\HttpRequests;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use UnexpectedValueException;
use Matraux\HttpRequests\Utils\Events;
use Matraux\HttpRequests\Utils\Headers;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException;
use Matraux\HttpRequests\Request\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Matraux\HttpRequests\Response\Response;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Matraux\HttpRequests\Request\RequestCollection;
use Matraux\HttpRequests\Response\ResponseCollection;

final class Requester
{

	protected Client $client {
		set(Client $value) {
			$this->client = $value;
		}
		get {
			return $this->client ?? new Client($this->config);
		}
	}

	public readonly Headers $headers;

	public readonly Events $onBefore;

	public readonly Events $onAfter;

	/** @var array<string,null|string|int|float|bool> */
	public array $config = ['verify' => false] {
		set(array $values) {
			foreach($values as $index => $value) {
				if(!is_string($index)) {
					throw new UnexpectedValueException(sprintf('Expected index type "string", "%s" given.', get_debug_type($index)));
				} elseif(!is_scalar($value) && $value !== null) {
					throw new UnexpectedValueException(sprintf('Expected value type "scalar|null", "%s" given.', get_debug_type($value)));
				}
			}

			$this->config = $values;
		}
	}

	protected function __construct()
	{
		$this->headers = Headers::create();
		$this->onAfter = Events::create();
		$this->onBefore = Events::create();
	}

	public static function create(): Requester
	{
		return new static();
	}

	/**
	 * @param array<string,string|int|bool> $value
	 */
	public function addConfig(array $value): static
	{
		$this->config = array_merge($this->config, $value);
		$this->client = new Client($this->config);

		return $this;
	}

	protected function createPromise(Request $request): PromiseInterface
	{
		foreach($this->headers as $index => $value) {
			$headers = $request->headers;
			$headers[$index] = $value;
		}

		($request->onBefore)();

		$method = is_string($request->method) ? $request->method : $request->method->value;
		$psr7Request = new Psr7Request($method, (string) $request->uri, iterator_to_array($request->headers), $request->body);
		$promise = $this->client->sendAsync($psr7Request);
		$promise->then(
			function (ResponseInterface $response) use ($request): void {
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
	 * @param array{state:string,value?:ResponseInterface,reason?:GuzzleException} $data
	 */
	protected function createResponse(array $data): ResponseInterface
	{
		if($response = $data['value'] ?? null) {
			return $response;
		} elseif($exception = $data['reason'] ?? null) {
			return $exception instanceof RequestException && $exception->getResponse() ?
				$exception->getResponse() :
				new Psr7Response(500, [], $exception->getMessage(), '1.1', $exception->getMessage());
		}

		return new Psr7Response(500, [], 'Invalid response data', '1.1', 'Invalid response  data');
	}

	public function send(Request $request): Response
	{
		($this->onBefore)();

		/** @var array{state:string,value?:ResponseInterface,reason?:GuzzleException} $data */
		$data = $this->createPromise($request)->wait();
		$response = $this->createResponse($data);

		($this->onAfter)();

		return Response::create($response, $request);
	}

	protected function sendBatch(RequestCollection $requests): ResponseCollection
	{
		($this->onBefore)();

		$promises = [];
		foreach($requests as $index => $request) {
			$promises[$index] = $this->createPromise($request);
		}

		/** @var array<int|string,array{state:string,value?:ResponseInterface,reason?:GuzzleException}> $data */
		$data = (array) Utils::settle($promises)->wait();

		$responses = [];
		foreach($data as $index => $d) {
			$response = $this->createResponse($d);
			$responses[$index] = Response::create($response, $requests[$index]);
		}

		($this->onAfter)();

		return ResponseCollection::create($responses);
	}

}
