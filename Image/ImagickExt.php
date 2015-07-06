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
     * Default constructor
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
     * Нарисовать линии между указанными точками
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
     * Нарисовать прямоугольник (4 линии)
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     */
    public function drawRect($sx, $sy, $ex, $ey)
    {
        $this->drawLine($sx, $sy, $ex, $sy); // top
        $this->drawLine($ex, $sy, $ex, $ey); // right
        $this->drawLine($sx, $sy, $sx, $ey); // left
        $this->drawLine($sx, $ey, $ex, $ey); //bottom
    }

    /**
     * Make width and height of image are multiple of defined size
     * Сделать ширину и высоту изображения кратными некоторому числу
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
            intval($width / 2) - $nWidth / 2, intval($height / 2 - $nHeight / 2)
        );
        $this->setImagePage(0, 0, 0, 0);
    }

    /**
     * Make square image - cut image by min dimension (width or height)
     * Сделать изображение квадратным - обрезать используя значение меньшего измерения (ширина и высота)
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
        $this->setImagePage(0, 0, 0, 0);
    }

    /**
     * Get clone of image's part
     * Получить часть изображения
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
        try {
            $copy->cropImage($width, $height, $sx, $sy);
            $copy->setImagePage(0, 0, 0, 0);
        } catch (\Exception $ex) {
            // TODO: Разобраться почему возникает ошибка
        }

        return $copy;
    }

    /**
     * Get image average color
     * Получить средн. значение цвета изображения
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
     * Get average color of some image part
     * Получить сред. значение цвета для некоторой области изображения
     * @param $sx
     * @param $sy
     * @param $ex
     * @param $ey
     * @return Color|null
     */
    public function getAvgColorOfRect($sx, $sy, $ex, $ey)
    {
        $image = $this->getImageFromRect($sx, $sy, $ex, $ey);

        return $image->getAvgColor();
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