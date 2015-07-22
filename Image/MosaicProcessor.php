<?php
/**
 * Created by PhpStorm.
 * User: scherk01
 * Date: 06.07.2015
 * Time: 13:44
 */

namespace GFB\MosaicBundle\Image;

use GFB\MosaicBundle\Entity\Color;
use GFB\MosaicBundle\Entity\Segment;
use GFB\MosaicBundle\Repo\PartRepo;

class MosaicProcessor
{
    /** @var ImagickExt */
    private $imagick;

    /** @var int */
    private $partSize;

    /** @var int */
    private $levels;

    /** @var boolean */
    private $debug;

    /** @var Segment[] */
    private $segments = array();

    /** @var PartRepo */
    private $partRepo;

    /**
     * @param ImagickExt $imagick
     * @param string $webDir
     * @param bool $debug
     */
    public function __construct($imagick, $webDir, $debug = false)
    {
        $this->imagick = $imagick;
        $this->debug = $debug;

        $this->webDir = $webDir;
        $this->basePath = "mosaic/base/";
    }

    /**
     * Entry point for segmentation process
     * Используется для запуска сегментации
     * @param $partSize
     * @param $levels
     */
    public function segmentation($partSize, $levels)
    {
        $this->imagick->cutSizeMultipleOf($partSize);
        $this->partSize = $partSize;
        $this->levels = $levels;

        $width = $this->imagick->getWidth();
        $height = $this->imagick->getHeight();

        $wPartsCount = intval($width / $this->partSize);
        $hPartsCount = intval($height / $this->partSize);

        for ($wi = 0; $wi < $wPartsCount; $wi++) {
            for ($hi = 0; $hi < $hPartsCount; $hi++) {
                $this->makeSegment(
                    $wi * $this->partSize,
                    $hi * $this->partSize,
                    $wi * $this->partSize + $this->partSize,
                    $hi * $this->partSize + $this->partSize,
                    1
                );
            }
        }
    }

    /**
     * Make segmentation if need else store segment to map
     * Выполняет сегментацию если нужно иначе записывает информацию о текущем сегменте в карту
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     * @param int $level
     */
    private function makeSegment($sx, $sy, $ex, $ey, $level)
    {
        $level++;

        $width = $ex - $sx;
        $height = $ey - $sy;

        $part = $this->imagick->getImageFromRect($sx, $sy, $ex, $ey);
        if ($level <= $this->levels && $this->needDividePart($part)) {
            $this->makeSegment($sx, $sy, $ex - $width / 2, $ey - $height / 2, $level); // top left
            $this->makeSegment($sx + $width / 2, $sy, $ex, $ey - $height / 2, $level); // top right
            $this->makeSegment($sx, $sy + $height / 2, $ex - $width / 2, $ey, $level); // bottom left
            $this->makeSegment($sx + $width / 2, $sy + $height / 2, $ex, $ey, $level); // bottom right
        } else {
            $this->segments[] = new Segment($sx, $sy, $ex, $ey, $part->getAvgColor());
            if ($this->debug) {
                $this->drawCrossMark($sx, $sy, $ex, $ey);
            }
        }
    }

    /**
     * Check segmentation need of image part
     * Проверяет необходимость деления части изображения
     * @param ImagickExt $imagick
     * @return bool
     */
    private function needDividePart($imagick)
    {
        $width = $imagick->getWidth();
        $height = $imagick->getHeight();

        $topLeftPart = $imagick->getImageFromRect(0, 0, $width / 2, $height / 2);
        $bottomRightPart = $imagick->getImageFromRect($width / 2, $height / 2, $width, $height);

        $topRightPart = $imagick->getImageFromRect($width / 2, 0, $width, $height / 2);
        $bottomLeftPart = $imagick->getImageFromRect(0, $height / 2, $width / 2, $height);

        $sub1 = Color::compare($topLeftPart->getAvgColor(), $bottomRightPart->getAvgColor());
        $sub2 = Color::compare($topRightPart->getAvgColor(), $bottomLeftPart->getAvgColor());

        return (($sub1 + $sub2) / 2 > 8);
    }

    /**
     * Drawing marks for segments
     * Рисование меток для сегментов
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     */
    private function drawCrossMark($sx, $sy, $ex, $ey)
    {
        $offset = ($ex - $sx) * 0.1;

        /*$this->imagick->drawRect(
            $sx + $offset, $sy + $offset,
            $ex - $offset, $ey - $offset
        );*/

        $this->imagick->drawLine(
            $sx + $offset, $sy + $offset,
            $ex - $offset, $ey - $offset
        );

        $this->imagick->drawLine(
            $ex - $offset, $sy + $offset,
            $sx + $offset, $ey - $offset
        );
    }

    /**
     * Paving entry point
     * Используется для запуска механизма замащивания
     * @param int $accuracy
     * @param float|int $partOpacity
     * @return bool
     */
    public function paving($accuracy = 32, $partOpacity = 1)
    {
        if (count($this->segments) == 0) {
            echo "Segmentation map is empty!\n";
            return false;
        }

        if (!$this->partRepo) {
            echo "PartRepo is not defined!\n";
            return false;
        }

        $segmentsCount = count($this->segments);
        echo "Paving for " . $segmentsCount . " segments...\n";

        $counter = 1;
        foreach ($this->segments as $segment) {
            /** @var Segment $segment */
            $part = $this->partRepo->findOneWithColorLike($segment->getAvgColor(), $accuracy);

            if (!$part) {
                continue;
            }
            echo "Found image for {$segment} ({$counter} / {$segmentsCount})\n";
            $segment->setPart($part);

            $tile = new ImagickExt($this->webDir . $part->getPath());
            $tile->resizeImage(
                $segment->getEndX() - $segment->getStartX(),
                $segment->getEndY() - $segment->getStartY(),
                \Imagick::FILTER_LANCZOS, 1
            );
            $tile->setImageOpacity($partOpacity);
            $this->imagick->compositeImage(
                $tile, \Imagick::COMPOSITE_OVER,
                $segment->getStartX(), $segment->getStartY()
            );

            $counter++;
        }

        return true;
    }

    /**
     * @return PartRepo
     */
    public function getPartRepo()
    {
        return $this->partRepo;
    }

    /**
     * @param PartRepo $partRepo
     */
    public function setPartRepo($partRepo)
    {
        $this->partRepo = $partRepo;
    }

    /**
     * @return \GFB\MosaicBundle\Entity\Segment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param \GFB\MosaicBundle\Entity\Segment[] $segments
     */
    public function setSegments($segments)
    {
        $this->segments = $segments;
    }
}