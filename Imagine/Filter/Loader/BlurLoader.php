<?php

namespace Neok\LiipImagineAdditionalFiltersBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;

/**
 * Class BlurLoader.
 */
class BlurLoader extends AbstractLoader
{
    /**
     * @param resource $originalImg
     * @param float    $width
     * @param float    $height
     * @param float    $xStart
     * @param float    $yStart
     * @param string   $type
     *
     * @return resource
     */
    protected function applyFilter($originalImg, $width, $height, $xStart, $yStart, $type)
    {
        $img = $originalImg;

        $cut  = imagecreatetruecolor($width, $height);
        $cut2 = imagecreatetruecolor($width, $height);
        imagecopyresampled($cut, $img, 0, 0, $xStart, $yStart, $width, $height, $width, $height);
        imagecopyresampled($cut2, $img, 0, 0, $xStart, $yStart, $width, $height, $width, $height);

        $diffx = round($width * 0.1);
        $diffy = round($height * 0.1);
        $mask  = imagecreatetruecolor($width, $height);
        imagecolorallocate($mask, 0, 0, 0);

        imagefilledellipse(
            $mask,
            round($width / 2),
            round($height / 2),
            $width - $diffx,
            $height - $diffy,
            imagecolorallocate($mask, 255, 255, 255)
        );
        for ($i = 0; $i < 100; ++$i) {
            imagefilter($cut, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }
        imagefilter($cut, IMG_FILTER_SMOOTH, 99);
        imagefilter($cut, IMG_FILTER_BRIGHTNESS, 10);

        $cut = $this->copyalpha($cut, $mask);

        imagecopyresampled($cut2, $cut, 0, 0, 0, 0, $width, $height, $width, $height);
        imagecopymerge($img, $cut2, $xStart, $yStart, 0, 0, $width, $height, 100);

        return $img;
    }

    /**
     * @param null|resource $image - resource
     * @param null|resource $alpha
     *
     * @return null
     */
    public function copyalpha($image = null, $alpha = null)
    {
        $img_image = $image;
        $img_alpha = $alpha;

        $wi = imagesx($img_image);
        $hi = imagesy($img_image);

        $wa = imagesx($img_alpha);
        $ha = imagesy($img_alpha);

        $_img_alpha = imagecreatetruecolor($wi, $hi);
        imagecopyresampled($_img_alpha, $img_alpha, 0, 0, 0, 0, $wi, $hi, $wa, $ha);
        imagedestroy($img_alpha);
        $img_alpha = $_img_alpha;

        imagealphablending($img_image, false);
        imagesavealpha($img_image, true);

        for ($x = 0; $x < $wi; ++$x) {
            for ($y = 0; $y < $hi; ++$y) {
                $srcrgb = imagecolorat($img_image, $x, $y);

                $src_red   = ($srcrgb >> 16) & 0xFF;
                $src_green = ($srcrgb >> 8) & 0xFF;
                $src_blue  = $srcrgb & 0xFF;

                $rgb         = imagecolorat($img_alpha, $x, $y);
                $alpha_red   = ($rgb >> 16) & 0xFF;
                $alpha_green = ($rgb >> 8) & 0xFF;
                $alpha_blue  = $rgb & 0xFF;
                $alpha       = ($alpha_red + $alpha_green + $alpha_blue) / 3;
                $alpha       = 127 - round(127 * $alpha / 255);

                $color = imagecolorallocatealpha($img_image, $src_red, $src_green, $src_blue, $alpha);
                imagesetpixel($img_image, $x, $y, $color);
            }
        }

        imagedestroy($img_alpha);

        return $img_image;
    }
}
