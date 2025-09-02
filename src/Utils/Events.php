<?php declare(strict_types = 1);

namespace Matraux\HttpRequests\Utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use Throwable;
use Traversable;
use UnexpectedValueException;

/**
 * @implements IteratorAggregate<int|string,callable>
 * @implements ArrayAccess<int|string,callable>
 */
final class Events implements IteratorAggregate, ArrayAccess, Countable
{

	/** @var array<callable> */
	protected array $events = [];

	protected function __construct()
	{
	}

	public static function create(): static
	{
		return new static();
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->events);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string", "%s" given.', get_debug_type($offset)));
		}

		return array_key_exists($offset, $this->events);
	}

	public function offsetGet(mixed $offset): callable
	{
		if (!$this->offsetExists($offset)) {
			throw new OutOfBoundsException(sprintf('No such offset "%s"', $offset));
		}

		return $this->events[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset !== null && !is_int($offset) && !is_string($offset)) {
			throw new UnexpectedValueException(sprintf('Expected offset type "int|string|null", "%s" given.', get_debug_type($offset)));
		} elseif (!is_callable($value)) {
			throw new UnexpectedValueException(sprintf('Expected value type "callable", "%s" given.', get_debug_type($offset)));
		}

		if ($offset === null) {
			$this->events[] = $value;
		} else {
			$this->events[$offset] = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->events[$offset]);
	}

	public function count(): int
	{
		return count($this->events);
	}

	public function __invoke(mixed ...$arguments): void
	{
		foreach ($this->events as $callable) {
			try {
				$callable(...$arguments);
			} catch (Throwable $th) {

			}
		}
	}

}
