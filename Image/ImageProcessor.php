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

class ImageProcessor
{
    /** @var ImagickExt */
    private $imagick;

    /** @var int */
    private $partSize;

    /** @var int */
    private $levels;

    /** @var boolean */
    private $debug;

    /** @var array */
    private $segmentationMap = array();

    /** @var PartRepo */
    private $partRepo;

    /**
     * @param ImagickExt $imagick
     * @param int $partSize
     * @param int $levels
     * @param bool $debug
     */
    public function __construct($imagick, $partSize, $levels, $debug = false)
    {
        $this->imagick = $imagick;
        $this->partSize = $partSize;
        $this->levels = $levels;
        $this->debug = $debug;

        $this->docRoot = __DIR__ . "/../../../../web";
        $this->basePath = "/mosaic/base/";
    }

    /**
     *
     */
    public function runSegmentation()
    {
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
     * @param int $sx
     * @param int $sy
     * @param int $ex
     * @param int $ey
     * @param int $level
     */
    public function makeSegment($sx, $sy, $ex, $ey, $level)
    {
        $level++;

        $width = $ex - $sx;
        $height = $ey - $sy;

        $part = $this->imagick->getImageFromRect($sx, $sy, $ex, $ey);
        if (
            $level <= $this->levels && $this->needDividePart($part)
//            && $width >= 16 && $height >= 16
        ) {
//            echo str_repeat(" ", $level) . "-> {$sx}, {$sy}, {$ex}, {$ey} ({$width}x{$height})\n";
            $this->makeSegment($sx, $sy, $ex - $width / 2, $ey - $height / 2, $level); // top left
            $this->makeSegment($sx + $width / 2, $sy, $ex, $ey - $height / 2, $level); // top right
            $this->makeSegment($sx, $sy + $height / 2, $ex - $width / 2, $ey, $level); // bottom left
            $this->makeSegment($sx + $width / 2, $sy + $height / 2, $ex, $ey, $level); // bottom right
        } else {
            $this->segmentationMap[] = new Segment($sx, $sy, $ex, $ey, $part->getAvgColor());
            if ($this->debug) {
                $this->drawCrossMark($sx, $sy, $ex, $ey);
            }
        }
    }

//    private $counter = 0; // TODO: Remove it

    /**
     * @param ImagickExt $imagick
     * @return bool
     */
    public function needDividePart($imagick)
    {
        $width = $imagick->getWidth();
        $height = $imagick->getHeight();

        $topLeftPart = $imagick->getImageFromRect(0, 0, $width / 2, $height / 2);
        $bottomRightPart = $imagick->getImageFromRect($width / 2, $height / 2, $width, $height);

        $topRightPart = $imagick->getImageFromRect($width / 2, 0, $width, $height / 2);
        $bottomLeftPart = $imagick->getImageFromRect(0, $height / 2, $width / 2, $height);

//        echo implode(", ", array(0, 0, $width / 2, $height / 2)) . "\n";
//        echo implode(", ", array($width / 2, $height / 2, $width, $height)) . "\n\n";

        /*if ($this->debug) {
            $imgPath = __DIR__ . "/../../../../web/mosaic/res/compars/";

            if (!file_exists($imgPath)) {
                mkdir($imgPath);
                chmod($imgPath, 0777);
            }

            $topLeftPart->writeImage($imgPath . "{$this->counter}-tl-{$topLeftPart->getWidth()}x{$topLeftPart->getHeight()}.png");
            $bottomRightPart->writeImage($imgPath . "{$this->counter}-br-{$bottomRightPart->getWidth()}x{$bottomRightPart->getHeight()}.png");
            $this->counter++;
        }*/

        $sub1 = Color::compare($topLeftPart->getAvgColor(), $bottomRightPart->getAvgColor());
        $sub2 = Color::compare($topRightPart->getAvgColor(), $bottomLeftPart->getAvgColor());

        return (($sub1 + $sub2) / 2 > 8);
//        return false;
    }

    /**
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
     * @return bool
     */
    public function paveSegments()
    {
        if (count($this->segmentationMap) == 0) {
            return false;
        }

        if (!$this->partRepo) {
            return false;
        }

        /** @var Segment $segment */
        foreach ($this->segmentationMap as $segment) {
            $part = $this->partRepo->findOneWithColorLike($segment->getAvgColor(), 32);

            if (!$part) {
                continue;
            }

            $tile = new ImagickExt($this->docRoot . $part->getPath());

            /*$imagick = $this->imagick->getImageFromRect(
                $segment->getStartX(), $segment->getStartY(),
                $segment->getEndX(), $segment->getEndY()
            );*/
            $tile->resizeImage(
                $segment->getEndX() - $segment->getStartX(),
                $segment->getEndY() - $segment->getStartY(),
                \Imagick::FILTER_LANCZOS, 1
            );

            $this->imagick->compositeImage(
                $tile, \Imagick::COMPOSITE_OVER,
                $segment->getStartX(), $segment->getStartY()
            );
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
}