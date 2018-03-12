<?php

namespace Neok\LiipImagineAdditionalFiltersBundle\Imagine\Filter\Loader;

use Imagine\Gd\Image;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * Class PixelateLoader.
 */
class PixelateLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = [])
    {
        $x = $options['start'][0] ?? 0;
        $y = $options['start'][1] ?? 0;

        $width  = $options['size'][0] ?? 0;
        $height = $options['size'][1] ?? 0;

        $intensity = $options['intensity'] ?? 16;

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
            $this->pixelate($img, $width, $height, $x, $y, $intensity, $type);
        }

        return $image;
    }

    /**
     * @param     $img
     * @param     $width
     * @param     $height
     * @param     $startX
     * @param     $startY
     * @param int $intensity
     * @param     $type
     *
     * @return mixed
     */
    public function pixelate(
        $img,
        $width,
        $height,
        $startX,
        $startY,
        $intensity = 10,
        $type
    ) {
        if ('ellipse' === $type) {
            $originalWidth  = imagesx($img);
            $originalHeight = imagesy($img);

            $img_ = imagecreatetruecolor($originalWidth, $originalHeight);
            imagealphablending($img_, false);
            imagecopyresampled($img_, $img, 0, 0, $startX, $startY, $width, $height, $width, $height);

            $r = ($width / 2) - 10;
            for ($y = 0; $y < $width; $y += $intensity + 1) {
                for ($x = 0; $x < $height; $x += $intensity + 1) {
                    $_x = $x - $width / 2;
                    $_y = $y - $height / 2;

                    if ((($_x * $_x) + ($_y * $_y)) < ($r * $r)) {
                        $this->fillRectangle($img_, $x, $y, $intensity);
                    }
                }
            }

            imagecopymerge($img, $img_, $startX, $startY, 0, 0, $width, $height, 100);
        } else {
            $width  += $startX;
            $height += $startY;
            for ($y = $startY; $y < $height; $y += $intensity + 1) {
                for ($x = $startX; $x < $width; $x += $intensity + 1) {
                    $this->fillRectangle($img, $x, $y, $intensity);
                }
            }
        }

        return $img;
    }

    protected function fillRectangle($img, $x, $y, $intensity)
    {
        $rgb   = imagecolorsforindex($img, imagecolorat($img, $x, $y));
        $color = imagecolorclosest($img, $rgb['red'], $rgb['green'], $rgb['blue']);

        imagefilledrectangle($img, $x, $y, $x + $intensity, $y + $intensity, $color);
    }
}
