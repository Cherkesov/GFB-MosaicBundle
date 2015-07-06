<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 03.07.2015
 * Time: 12:37
 */

namespace GFB\MosaicBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Color
 * @package GFB\MosaicBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="gfb_mosaic_color")
 */
class Color
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $red;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $green;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $blue;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $alpha;

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     */
    public function __construct($red = 0, $green = 0, $blue = 0, $alpha = 1)
    {
        $this->red = intval($red);
        $this->green = intval($green);
        $this->blue = intval($blue);
        $this->alpha = intval($alpha);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * @param int $red
     * @return $this
     */
    public function setRed($red)
    {
        $this->red = $red;
        return $this;
    }

    /**
     * @return int
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * @param int $green
     * @return $this
     */
    public function setGreen($green)
    {
        $this->green = $green;
        return $this;
    }

    /**
     * @return int
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * @param int $blue
     * @return $this
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;
        return $this;
    }

    /**
     * @return int
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * @param int $alpha
     * @return $this
     */
    public function setAlpha($alpha)
    {
        $this->alpha = $alpha;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->red}, {$this->green}, {$this->blue}, {$this->alpha}";
    }

    /**
     * @return string
     */
    public function __toHEX()
    {
        return sprintf(
            "%02X%02X%02X",
            $this->red, $this->green, $this->blue
        );
    }

    /**
     * @param Color $color1
     * @param Color $color2
     * @return int
     */
    public static function compare($color1, $color2)
    {
        $weight = 0;
        $weight += abs($color1->getRed() - $color2->getRed());
        $weight += abs($color1->getGreen() - $color2->getGreen());
        $weight += abs($color1->getBlue() - $color2->getBlue());
        return $weight / 3; // TODO: divide on 3
    }
}