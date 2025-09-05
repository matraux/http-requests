<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Response;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use RuntimeException;
use Traversable;
use UnexpectedValueException;

/**
 * @implements IteratorAggregate<int|string,Response>
 * @implements ArrayAccess<int|string,Response>
 */
final readonly class ResponseCollection implements IteratorAggregate, ArrayAccess, Countable
{

	/** @var array<int|string,Response> */
	protected array $responses;

	/**
	 * @param array<int|string,Response> $responses
	 */
	protected function __construct(array $responses)
	{
		foreach ($responses as $response) {
			if (!$response instanceof Response) {
				throw new UnexpectedValueException(sprintf('Expected value type "%s", "%s" given.', Response::class, get_debug_type($response)));
			}
		}

		$this->responses = $responses;
	}

	/**
	 * @param array<int|string,Response> $responses
	 */
	public static function create(array $responses): static
	{
		return new static($responses);
	}

	public function count(): int
	{
		return count($this->responses);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string", "%s" given.', get_debug_type($offset)));
		}

		return array_key_exists($offset, $this->responses);
	}

	public function offsetGet(mixed $offset): Response
	{
		if (!$this->offsetExists($offset)) {
			throw new OutOfBoundsException(sprintf('No such offset "%s"', $offset));
		}

		return $this->responses[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new RuntimeException(sprintf('%s is read-only.', static::class));
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new RuntimeException('Response collection is read-only.');
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->responses);
	}

}
