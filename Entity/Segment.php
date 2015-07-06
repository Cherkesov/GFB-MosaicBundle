<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 03.07.2015
 * Time: 18:04
 */

namespace GFB\MosaicBundle\Entity;

class Segment
{
    /** @var int */
    private $startX;

    /** @var int */
    private $startY;

    /** @var int */
    private $endX;

    /** @var int */
    private $endY;

    /** @var Color */
    private $avgColor;

    /**
     * @param int $startX
     * @param int $startY
     * @param int $endX
     * @param int $endY
     * @param Color $avgColor
     */
    public function __construct($startX, $startY, $endX, $endY, $avgColor)
    {
        $this->startX = $startX;
        $this->startY = $startY;
        $this->endX = $endX;
        $this->endY = $endY;
        $this->avgColor = $avgColor;
    }

    /**
     * @return int
     */
    public function getStartX()
    {
        return $this->startX;
    }

    /**
     * @param int $startX
     */
    public function setStartX($startX)
    {
        $this->startX = $startX;
    }

    /**
     * @return int
     */
    public function getStartY()
    {
        return $this->startY;
    }

    /**
     * @param int $startY
     */
    public function setStartY($startY)
    {
        $this->startY = $startY;
    }

    /**
     * @return int
     */
    public function getEndX()
    {
        return $this->endX;
    }

    /**
     * @param int $endX
     */
    public function setEndX($endX)
    {
        $this->endX = $endX;
    }

    /**
     * @return int
     */
    public function getEndY()
    {
        return $this->endY;
    }

    /**
     * @param int $endY
     */
    public function setEndY($endY)
    {
        $this->endY = $endY;
    }

    /**
     * @return Color
     */
    public function getAvgColor()
    {
        return $this->avgColor;
    }

    /**
     * @param Color $avgColor
     */
    public function setAvgColor($avgColor)
    {
        $this->avgColor = $avgColor;
    }
}