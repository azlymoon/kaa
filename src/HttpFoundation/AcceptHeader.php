<?php

/*
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * This file was rewritten from the Symfony package
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\HttpFoundation;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * Represents an Accept-* header.
 *
 * An accept header is compound with a list of items,
 * sorted by descending quality.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class AcceptHeader
{
    /** @var AcceptHeaderItem[] */
    private array $items = [];

    private bool $sorted = true;

    /** @param AcceptHeaderItem[] $items */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    /**
     * Builds an AcceptHeader instance from a string.
     */
    public static function fromString(?string $headerValue): self
    {
        $index = 0;

        $parts = HeaderUtils::split($headerValue ?? '', ',;=');

        $indexHolder = new IndexHolder($index);
        $result = new self(array_map(static function ($subParts) use ($indexHolder) {
            $part = array_shift($subParts);
            /** @var mixed $attributes2 */
            $attributes2 = HeaderUtils::combine($subParts);

            /** @var string[] $attributes */
            $attributes = array_map('strval', $attributes2);

            $item = new AcceptHeaderItem((string)$part[0], $attributes);
            $item->setIndex($indexHolder->getIndex());
            $indexHolder->incrementIndex();

            return $item;
        }, $parts));

        return $result;
    }

    /**
     * Returns header value's string representation.
     */
    public function __toString(): string
    {
        $str = [];
        foreach ($this->items as $item) {
            $str [] = (string)$item;
        }
        return implode(',', $str);
    }

    /**
     * Tests if header has given value.
     */
    public function has(string $value): bool
    {
        return isset($this->items[$value]);
    }

    /**
     * Returns given value's item, if exists.
     */
    public function get(string $value): ?AcceptHeaderItem
    {
        return $this->items[$value]
            ?? $this->items[explode('/', $value)[0] . '/*']
            ?? $this->items['*/*'] ?? $this->items['*']
            ?? null;
    }

    /**
     * Adds an item.
     *
     * @return $this
     */
    public function add(AcceptHeaderItem $item): static
    {
        $this->items[$item->getValue()] = $item;
        $this->sorted = false;

        return $this;
    }

    /**
     * Returns all items.
     *
     * @return AcceptHeaderItem[]
     */
    public function all()
    {
        $this->sort();

        return $this->items;
    }

    /**
     * Returns first item.
     */
    public function first(): ?AcceptHeaderItem
    {
        $this->sort();

        if (count($this->items) !== 0) {
            return $this->items[array_keys($this->items)[0]];
        }
        return null;
    }

    /**
     * Sorts items by descending quality.
     */
    private function sort(): void
    {
        if (!$this->sorted) {
            uasort($this->items, static function (AcceptHeaderItem $a, AcceptHeaderItem $b) {
                $qA = $a->getQuality();
                $qB = $b->getQuality();

                if ($qA == $qB) {
                    if ($a->getIndex() > $b->getIndex()) {
                        return 1;
                    }
                    return -1;
                }

                if ($qA > $qB) {
                    return -1;
                }
                return 1;
            });

            $this->sorted = true;
        }
    }
}
