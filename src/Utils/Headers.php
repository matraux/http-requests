<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;
use UnexpectedValueException;

/**
 * @implements IteratorAggregate<string,string>
 * @implements ArrayAccess<string,string>
 */
final class Headers implements IteratorAggregate, ArrayAccess, Countable
{

	/** @var array<string,string> */
	protected array $headers = [];

	protected function __construct()
	{
	}

	public static function create(): static
	{
		return new static();
	}

	public function getIterator(): Traversable
	{
		/** @var ArrayIterator<string,string> */
		return new ArrayIterator($this->headers);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "string", "%s" given.', get_debug_type($offset)));
		}

		return array_key_exists($offset, $this->headers);
	}

	public function offsetGet(mixed $offset): string
	{
		if (!$this->offsetExists($offset)) {
			throw new OutOfBoundsException(sprintf('No such offset "%s"', $offset));
		}

		return $this->headers[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "string", "%s" given.', get_debug_type($offset)));
		} elseif (!is_scalar($value) && $value !== null) {
			throw new UnexpectedValueException(sprintf('Expected value type "scalar|null", "%s" given.', get_debug_type($value)));
		}

		$this->headers[$offset] = (string) $value;
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->headers[$offset]);
	}

	public function count(): int
	{
		return count($this->headers);
	}

}
