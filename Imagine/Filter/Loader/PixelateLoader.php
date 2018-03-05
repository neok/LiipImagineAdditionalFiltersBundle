<?php

namespace Neok\LiipImagineAdditionalFiltersBundle\Imagine\Filter\Loader;

use Imagine\Gd\Image;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * Class PixelateLoader
 */
class PixelateLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = [])
    {
        $x = $options['start'][0] ?? 0;
        $y = $options['start'][1] ?? 0;

        $width  = $options['size'][0] ?? 0;
        $height = $options['size'][1] ?? 0;

        $intensity = $options['intensity'] ?? 20;

        $type = $options['type'] ?? 'rectangle';
        $img  = null;
        if ($image instanceof Image) {
            $img = $image->getGdResource();
        }

        if ($image instanceof \Imagine\Imagick\Image) {
            $img = $image->getImagick();
        }

        if ($image instanceof \Imagine\Gmagick\Image) {
            $img = $image->getGmagick();
        }
        if ($img) {
            $this->pixelate($img, $x + $width, $y + $height, $x, $y, $intensity, $type);
        }

        return $image;
    }

    public function pixelate(
        $img,
        $width,
        $height,
        $startX,
        $startY,
        $intensity = 10
    ) {
        for ($y = $startY; $y < $height; $y += $intensity + 1) {
            for ($x = $startX; $x < $width; $x += $intensity + 1) {
                $rgb   = imagecolorsforindex($img, imagecolorat($img, $x, $y));
                $color = imagecolorclosest($img, $rgb['red'], $rgb['green'], $rgb['blue']);

                imagefilledrectangle($img, $x, $y, $x + $intensity, $y + $intensity, $color);
            }
        }

        return $img;
    }
}
