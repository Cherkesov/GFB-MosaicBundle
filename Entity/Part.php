<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02.07.2015
 * Time: 22:56
 */

namespace GFB\MosaicBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use GFB\CoreBundle\Entity\AbstractEntity;

/**
 * Class Part
 * @package GFB\MosaicBundle\Entity
 * @ORM\Entity(repositoryClass="GFB\MosaicBundle\Repo\PartRepo")
 * @ORM\Table(name="gfb_mosaic_part")
 */
class Part extends AbstractEntity
{
    /**
     * @var Color
     * @ORM\OneToOne(targetEntity="Color", cascade={"persist", "remove"})
     */
    private $avgColor;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $color;

    /**
     * @return Color
     */
    public function getAvgColor()
    {
        return $this->avgColor;
    }

    /**
     * @param Color $avgColor
     * @return $this
     */
    public function setAvgColor($avgColor)
    {
        $this->avgColor = $avgColor;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = intval($width);
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = intval($height);
        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }
}