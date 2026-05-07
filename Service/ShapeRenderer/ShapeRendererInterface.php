<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\ShapeRenderer;

interface ShapeRendererInterface
{
    /**
     * Render the spec into a PNG byte stream.
     */
    public function renderPng(ShapeSpec $spec): string;
}
