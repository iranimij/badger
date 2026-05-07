<?php
declare(strict_types=1);

namespace Iranimij\Badger\Model\Evaluation;

class SpecificationRegistry
{
    /** @var array<string, SpecificationFactoryInterface> */
    private array $factories;

    /**
     * @param array<string, SpecificationFactoryInterface> $factories
     */
    public function __construct(array $factories = [])
    {
        foreach ($factories as $token => $factory) {
            $this->register((string) $token, $factory);
        }
    }

    public function register(string $token, SpecificationFactoryInterface $factory): void
    {
        if (isset($this->factories[$token])) {
            throw new \LogicException("Duplicate specification token: $token");
        }
        $this->factories[$token] = $factory;
    }

    public function has(string $token): bool
    {
        return isset($this->factories[$token]);
    }

    public function get(string $token): SpecificationFactoryInterface
    {
        if (!isset($this->factories[$token])) {
            throw new \OutOfBoundsException("Unknown specification token: $token");
        }
        return $this->factories[$token];
    }

    /** @return list<string> */
    public function tokens(): array
    {
        return array_keys($this->factories);
    }
}
