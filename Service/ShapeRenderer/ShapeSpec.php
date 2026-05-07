<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\ShapeRenderer;

use Iranimij\Badger\Model\Enum\ShapeKind;

final class ShapeSpec
{
    public function __construct(
        public readonly ShapeKind $kind,
        public readonly int $width,
        public readonly int $height,
        public readonly string $fillColor,
        public readonly string $borderColor,
        public readonly int $borderWidth,
        public readonly int $cornerRadius,
        public readonly string $text,
        public readonly string $textColor,
        public readonly int $fontSize
    ) {
    }

    public function hash(): string
    {
        return substr(hash('sha256', implode('|', [
            $this->kind->value,
            $this->width,
            $this->height,
            $this->fillColor,
            $this->borderColor,
            $this->borderWidth,
            $this->cornerRadius,
            $this->text,
            $this->textColor,
            $this->fontSize,
        ])), 0, 32);
    }
}
