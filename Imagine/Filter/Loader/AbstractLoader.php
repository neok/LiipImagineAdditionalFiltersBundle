<?php

namespace Neok\LiipImagineAdditionalFiltersBundle\Imagine\Filter\Loader;

use Imagine\Gd\Image;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;


/**
 * Class AbstractLoader
 */
abstract class AbstractLoader implements LoaderInterface
{
    /** {@inheritdoc} */
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $x = $options['start'][0] ?? 0;
        $y = $options['start'][1] ?? 0;

        $width  = $options['size'][0] ?? 0;
        $height = $options['size'][1] ?? 0;

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
            $this->applyFilter($img, $width, $height, $x, $y, $type);
        }

        return $image;
    }

    /**
     * @param $img - resource
     * @param float $width
     * @param float $height
     * @param float $x
     * @param float $y
     * @param string $type - rectangle or ellipse
     *
     * @return resource
     */
    abstract protected function applyFilter($img, $width, $height, $x, $y, $type);
}
