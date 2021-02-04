<?php
declare(strict_types=1);

namespace I4code\GlideService\Glide\Manipulators;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic;
use League\Glide\Manipulators\BaseManipulator;
use League\Glide\Manipulators\Crop;
use League\Glide\Manipulators\Pixelate;

class PixelArea extends BaseManipulator
{
    public function createCrop()
    {
        $cropManipulator = new Crop();
        $cropManipulator->setParams([
            'crop' => $this->pixelarea
        ]);
        return $cropManipulator;
    }

    public function getCoordinates(Image $image)
    {
        $cropManipulator = $this->createCrop();
        return $cropManipulator->getCoordinates($image);
    }

    public function getOffsetX(Image $image): int
    {
       $coordinates = $this->getCoordinates($image);
       if (is_array($coordinates) && (4 == count($coordinates))) {
           return $coordinates[2];
       }
       return 0;
    }

    public function getOffsetY(Image $image): int
    {
        $coordinates = $this->getCoordinates($image);
        if (is_array($coordinates) && (4 == count($coordinates))) {
            return $coordinates[3];
        }
        return 0;
    }

    /**
     * Generate and pixelate crop
     * @param  Image      $image The source image.
     * @return Image|null The watermark image.
     */
    public function getCopy(Image $source): Image
    {
        $path = tempnam(sys_get_temp_dir(), 'Glide');

        $sourcePath = $source->basePath();
        $source->save($path);
        $source->setFileInfoFromPath($sourcePath);

        return $source->getDriver()->init($path);
    }

    /**
     * Generate and pixelate crop
     * @param  Image      $image The source image.
     * @return Image|null The watermark image.
     */
    public function getImage(Image $source)
    {
        $image = $this->getCopy($source);

        // crop new image using given coordinates
        $cropManipulator = $this->createCrop();
        $image = $cropManipulator->run($image);

        // pixelate new image
        $pixelateManipulator = new Pixelate();
        $pixelateManipulator->setParams([
            'pixel' => 20
        ]);
        $image = $pixelateManipulator->run($image);

        return $image;
    }

    /**
     * @inheritDoc
     */
    public function run(Image $image)
    {
        $coordinates = $this->getCoordinates($image);

        if ($coordinates) {
            $source = $this->getImage($image);

            $x = $this->getOffsetX($image);
            $y = $this->getOffsetY($image);

            $image = $image->insert($source, 'top-left', $x, $y);
        }

        return $image;
    }
}