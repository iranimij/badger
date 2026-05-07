<?php
declare(strict_types=1);

namespace Iranimij\Badger\Service\ShapeRenderer;

use Iranimij\Badger\Model\Enum\ShapeKind;

class GdShapeRenderer implements ShapeRendererInterface
{
    public function renderPng(ShapeSpec $spec): string
    {
        if (!\function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD extension required to render shapes.');
        }

        $image = \imagecreatetruecolor($spec->width, $spec->height);
        \imagesavealpha($image, true);
        $transparent = \imagecolorallocatealpha($image, 0, 0, 0, 127);
        \imagefill($image, 0, 0, $transparent);

        [$fr, $fg, $fb] = $this->hexToRgb($spec->fillColor);
        [$br, $bg, $bb] = $this->hexToRgb($spec->borderColor);
        $fill = \imagecolorallocate($image, $fr, $fg, $fb);
        $border = \imagecolorallocate($image, $br, $bg, $bb);

        match ($spec->kind) {
            ShapeKind::CIRCLE => $this->drawCircle($image, $spec, $fill, $border),
            ShapeKind::RIBBON => $this->drawRibbon($image, $spec, $fill, $border),
            default => $this->drawRoundedRect($image, $spec, $fill, $border),
        };

        if ($spec->text !== '') {
            $this->drawText($image, $spec);
        }

        \ob_start();
        \imagepng($image);
        $bytes = (string) \ob_get_clean();
        \imagedestroy($image);
        return $bytes;
    }

    private function drawRoundedRect($image, ShapeSpec $s, int $fill, int $border): void
    {
        $r = max(0, $s->cornerRadius);
        $w = $s->width;
        $h = $s->height;
        \imagefilledrectangle($image, $r, 0, $w - $r - 1, $h - 1, $fill);
        \imagefilledrectangle($image, 0, $r, $w - 1, $h - $r - 1, $fill);
        \imagefilledellipse($image, $r, $r, $r * 2, $r * 2, $fill);
        \imagefilledellipse($image, $w - $r - 1, $r, $r * 2, $r * 2, $fill);
        \imagefilledellipse($image, $r, $h - $r - 1, $r * 2, $r * 2, $fill);
        \imagefilledellipse($image, $w - $r - 1, $h - $r - 1, $r * 2, $r * 2, $fill);
        if ($s->borderWidth > 0) {
            \imagesetthickness($image, $s->borderWidth);
            \imagerectangle($image, 0, 0, $w - 1, $h - 1, $border);
        }
    }

    private function drawCircle($image, ShapeSpec $s, int $fill, int $border): void
    {
        $cx = (int) ($s->width / 2);
        $cy = (int) ($s->height / 2);
        $d = min($s->width, $s->height) - 1;
        \imagefilledellipse($image, $cx, $cy, $d, $d, $fill);
        if ($s->borderWidth > 0) {
            \imagesetthickness($image, $s->borderWidth);
            \imageellipse($image, $cx, $cy, $d, $d, $border);
        }
    }

    private function drawRibbon($image, ShapeSpec $s, int $fill, int $border): void
    {
        $w = $s->width;
        $h = $s->height;
        $notch = (int) ($h / 3);
        $points = [
            0, 0,
            $w - 1, 0,
            $w - 1 - $notch, (int) ($h / 2),
            $w - 1, $h - 1,
            0, $h - 1,
            $notch, (int) ($h / 2),
        ];
        \imagefilledpolygon($image, $points, $fill);
        if ($s->borderWidth > 0) {
            \imagesetthickness($image, $s->borderWidth);
            \imagepolygon($image, $points, $border);
        }
    }

    private function drawText($image, ShapeSpec $s): void
    {
        [$tr, $tg, $tb] = $this->hexToRgb($s->textColor);
        $color = \imagecolorallocate($image, $tr, $tg, $tb);
        $font = max(1, min(5, (int) round($s->fontSize / 6)));
        $approxCharW = \imagefontwidth($font);
        $approxCharH = \imagefontheight($font);
        $x = max(0, (int) (($s->width - $approxCharW * \strlen($s->text)) / 2));
        $y = max(0, (int) (($s->height - $approxCharH) / 2));
        \imagestring($image, $font, $x, $y, $s->text, $color);
    }

    /** @return array{0:int,1:int,2:int} */
    private function hexToRgb(string $hex): array
    {
        $hex = \ltrim($hex, '#');
        if (\strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (\strlen($hex) !== 6 || !\ctype_xdigit($hex)) {
            return [0, 0, 0];
        }
        return [
            (int) \hexdec(\substr($hex, 0, 2)),
            (int) \hexdec(\substr($hex, 2, 2)),
            (int) \hexdec(\substr($hex, 4, 2)),
        ];
    }
}
