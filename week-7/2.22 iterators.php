<?php

declare(strict_types=1);

class NumberIterator implements Iterator
{
    private array $items;
    private int $position = 0;

    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    public function current(): mixed
    {
        return $this->items[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return array_key_exists($this->position, $this->items);
    }
}

$numbers = new NumberIterator([10, 20, 30]);

foreach ($numbers as $key => $value) {
    echo $key . ' => ' . $value . '<br />';
}
