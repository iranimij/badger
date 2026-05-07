<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder;

class PlaceholderRegistry
{
    /** @var array<string, PlaceholderResolverInterface> */
    private array $resolvers = [];

    /** @var array<string, PlaceholderResolverInterface> */
    private array $patternResolvers = [];

    /**
     * @param PlaceholderResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        foreach ($resolvers as $resolver) {
            $this->register($resolver);
        }
    }

    public function register(PlaceholderResolverInterface $resolver): void
    {
        $token = $resolver->token();
        if (str_contains($token, '*')) {
            $this->patternResolvers[$token] = $resolver;
            return;
        }
        if (isset($this->resolvers[$token])) {
            throw new \LogicException("Duplicate placeholder token: $token");
        }
        $this->resolvers[$token] = $resolver;
    }

    public function get(string $token): ?PlaceholderResolverInterface
    {
        if (isset($this->resolvers[$token])) {
            return $this->resolvers[$token];
        }
        foreach ($this->patternResolvers as $pattern => $resolver) {
            $prefix = rtrim($pattern, '*');
            if ($prefix !== '' && str_starts_with($token, $prefix)) {
                return $resolver;
            }
        }
        return null;
    }

    /** @return list<string> */
    public function tokens(): array
    {
        return array_values(array_merge(array_keys($this->resolvers), array_keys($this->patternResolvers)));
    }
}
