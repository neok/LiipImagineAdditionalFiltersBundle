<?php

namespace Neok\LiipImagineAdditionalFiltersBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

/**
 * Class PixelateLoader.
 */
class PixelateLoader extends AbstractLoader
{
    /** @var int */
    protected $intensity;

    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $this->intensity = $options['intensity'] ?? 16;

        return parent::load($image, $options);
    }

    /**
     * @param     $img
     * @param     $width
     * @param     $height
     * @param     $startX
     * @param     $startY
     * @param     $type
     *
     * @return resource
     */
    public function applyFilter(
        $img,
        $width,
        $height,
        $startX,
        $startY,
        $type
    ) {
        if ('ellipse' === $type) {
            $originalWidth  = imagesx($img);
            $originalHeight = imagesy($img);

            $img_ = imagecreatetruecolor($originalWidth, $originalHeight);
            imagealphablending($img_, false);
            imagecopyresampled($img_, $img, 0, 0, $startX, $startY, $width, $height, $width, $height);

            $r  = ($width / 2);
            $r2 = ($height / 2);
            for ($y = 0; $y < $height; $y += $this->intensity + 1) {
                for ($x = 0; $x < $width; $x += $this->intensity + 1) {
                    $_x = $x - ($width) / 2;
                    $_y = $y - ($height) / 2;

                    if ((($_x * $_x) + ($_y * $_y)) < ($r * $r2)) {
                        $this->fillRectangle($img_, $x, $y);
                    }
                }
            }

            imagecopymerge($img, $img_, $startX, $startY, 0, 0, $width, $height, 100);
        } else {
            $width  += $startX;
            $height += $startY;
            for ($y = $startY; $y < $height; $y += $this->intensity + 1) {
                for ($x = $startX; $x < $width; $x += $this->intensity + 1) {
                    $this->fillRectangle($img, $x, $y);
                }
            }
        }

        return $img;
    }

    /**
     * @param $img
     * @param $x
     * @param $y
     */
    protected function fillRectangle($img, $x, $y)
    {
        $rgb   = imagecolorsforindex($img, imagecolorat($img, $x, $y));
        $color = imagecolorclosest($img, $rgb['red'], $rgb['green'], $rgb['blue']);

        imagefilledrectangle($img, $x, $y, $x + $this->intensity, $y + $this->intensity, $color);
    }
}
