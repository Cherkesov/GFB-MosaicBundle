<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 03.07.2015
 * Time: 14:16
 */

namespace GFB\MosaicBundle\Image;

use GFB\MosaicBundle\Entity\Color;

class ImagickExt extends \Imagick
{
    /** @var \ImagickPixel */
    private $black;

    /**
     * @param mixed|null $files
     */
    public function __construct($files = null)
    {
        parent::__construct($files);
        $this->black = new \ImagickPixel();
        $this->black->setColor("black");
    }

    /**
     * Draw line between points
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     */
    public function drawLine($sx, $sy, $ex, $ey)
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeColor($this->black);
        $draw->setStrokeWidth(1);
        $draw->line($sx, $sy, $ex, $ey);
        $this->drawImage($draw);
    }

    /**
     * Draw rect (4 lines)
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     */
    public function drawRect($sx, $sy, $ex, $ey)
    {
        $this->drawLine($sx, $sy, $ex, $sy); // top
        $this->drawLine($ex, $sy, $ex, $ey); // right
//        $this->drawRect($imagick, $sx, $sy, $sx, $ey); // left
//        $this->drawRect($imagick, $sx, $ey, $ex, $ey); //bottom
    }

    /**
     * Make width and height of image are multiple of defined size
     * @param int $size
     */
    public function cutSizeMultipleOf($size)
    {
        $d = $this->getImageGeometry();
        $width = intval($d['width']);
        $height = intval($d['height']);

        // Needed image size
        $nWidth = intval($width / $size) * $size;
        $nHeight = intval($height / $size) * $size;

        $this->cropImage(
            $nWidth, $nHeight,
            intval($width / 2), intval($height / 2)
        );
    }

    /**
     * Make square image - cut image by min dimension (width or height)
     */
    public function cutToSquare()
    {
        $d = $this->getImageGeometry();
        $width = intval($d['width']);
        $height = intval($d['height']);

        $size = ($width < $height) ? $width : $height;

        $this->cropImage(
            $size, $size,
            intval($width / 2) - $size / 2, intval($height / 2) - $size / 2
        );

//        return clone $this;
//        $this->setImagePage(0, 0, 0, 0);
//        $this->coalesceImages();
//        $this->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, $size);
    }

    /**
     * Get clone of image's part
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     * @return ImagickExt
     */
    public function getImageFromRect($sx, $sy, $ex, $ey)
    {
        $width = $ex - $sx;
        $height = $ey - $sy;

        $copy = clone $this;
        $copy->cropImage($width, $height, $sx + $width / 2, $sy + $height / 2);

        return $copy;
    }

    /**
     * @return Color|null
     */
    public function getAvgColor()
    {
        try {
            $imagick = clone $this;
            $imagick->scaleimage(1, 1);
            /** @var \ImagickPixel $pixel */
            if (!$pixels = $imagick->getimagehistogram()) {
                return null;
            }
        } catch (\ImagickException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }

        $pixel = reset($pixels);
        $rgb = $pixel->getcolor();

        return new Color($rgb['r'], $rgb['g'], $rgb['b'], $rgb['a']);
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        $d = $this->getImageGeometry();

        return intval($d['width']);
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        $d = $this->getImageGeometry();

        return intval($d['height']);
    }
}