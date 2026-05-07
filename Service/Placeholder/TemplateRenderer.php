<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\Placeholder;

class TemplateRenderer
{
    public function __construct(
        private readonly PlaceholderRegistry $registry
    ) {
    }

    public function render(string $template, PlaceholderContext $context): string
    {
        if (!str_contains($template, '{{')) {
            return $template;
        }
        return (string) preg_replace_callback(
            '/\{\{\s*([a-z0-9_:.]+)\s*\}\}/i',
            function (array $match) use ($context): string {
                $token = (string) $match[1];
                $resolver = $this->registry->get($token);
                if ($resolver === null) {
                    return $match[0];
                }
                $value = $resolver->resolve($token, $context);
                return $value ?? '';
            },
            $template
        );
    }
}
