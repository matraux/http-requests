<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Request;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use OutOfBoundsException;
use UnexpectedValueException;
use Matraux\HttpRequests\Request\Request;

/**
 * @implements IteratorAggregate<int|string,Request>
 * @implements ArrayAccess<int|string,Request>
 */
final class RequestCollection implements IteratorAggregate, ArrayAccess, Countable
{
	/** @var array<int|string,Request> */
	protected array $requests = [];

	protected function __construct()
	{
	}

	public function count(): int
	{
		return count($this->requests);
	}

	public function offsetExists(mixed $offset): bool
	{
		if(!is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string", "%s" given.', get_debug_type($offset)));
		}

		return array_key_exists($offset, $this->requests);
	}

	public function offsetGet(mixed $offset): Request
	{
		if (!$this->offsetExists($offset)) {
			throw new OutOfBoundsException(sprintf('No such offset "%s"', $offset));
		}

		return $this->requests[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if($offset !== null && !is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string|null", "%s" given.', get_debug_type($offset)));
		} elseif(!$value instanceof Request) {
			throw new UnexpectedValueException(sprintf('Expected value type "%s", "%s" given.', Request::class, get_debug_type($offset)));
		}

		if ($offset === null) {
			$this->requests[] = $value;
		} else {
			$this->requests[$offset] = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->requests[$offset]);
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->requests);
	}

	public static function create(): static
	{
		return new static();
	}

	public function addRequest(Request $request): static
	{
		$this->requests[] = $request;

		return $this;
	}

}
