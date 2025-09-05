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
 * @implements IteratorAggregate<int|string,mixed>
 * @implements ArrayAccess<int|string,mixed>
 */
final class Config implements IteratorAggregate, ArrayAccess, Countable
{

	/** @var array<int|string,mixed> */
	protected array $configs = [];

	protected function __construct()
	{
	}

	public static function create(): static
	{
		return new static();
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->configs);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string", "%s" given.', get_debug_type($offset)));
		}

		return array_key_exists($offset, $this->configs);
	}

	public function offsetGet(mixed $offset): mixed
	{
		if (!$this->offsetExists($offset)) {
			throw new OutOfBoundsException(sprintf('No such offset "%s"', $offset));
		}

		return $this->configs[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset !== null && !is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string|null", "%s" given.', get_debug_type($offset)));
		}

		if ($offset === null) {
			$this->configs[] = $value;
		} else {
			$this->configs[$offset] = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->configs[$offset]);
	}

	public function count(): int
	{
		return count($this->configs);
	}

}
