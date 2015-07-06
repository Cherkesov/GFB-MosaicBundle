<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02.07.2015
 * Time: 19:33
 */

namespace GFB\MosaicBundle\Command;

use Imagick;
use ImagickPixel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMosaicCommand extends ContainerAwareCommand
{
    private $docRoot;
    private $imagesPath;
    private $mosaicsPath;

    private $partSize = 64;
    private $sizeLevels = 4;
    /** @var OutputInterface */
    private $output;

    public function __construct()
    {
        parent::__construct();

        if (extension_loaded('imagick')) {
            $this->docRoot = __DIR__ . "/../../../../web";
            $this->imagesPath = $this->docRoot . "/mosaic/images/";
            $this->mosaicsPath = $this->docRoot . "/mosaic/res/";

            $this->black = new ImagickPixel();
            $this->black->setColor("black");
        }
    }

    protected function configure()
    {
        $this
            ->setName('gfb:mosaic:create')
            ->setDescription('Create mosaic command')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Which file do you want to process?'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        if (!extension_loaded('imagick')) {
            $output->writeln("Error: Imagick not found! You should install it!");
            return -1;
        }

        if (!is_writable($this->mosaicsPath)) {
            chmod($this->mosaicsPath, 0777);
        }

        $imagick = new Imagick();

        $needleFormats = array("JPG", "JPEG", "PNG", "GIF");
        $formatsAll = $imagick->queryFormats();
        if (count(array_intersect($needleFormats, $formatsAll)) != count($needleFormats)) {
            $output->writeln("Error: not all the required formats are supported!");
            exit;
        }

        $name = $input->getArgument('file');
        $imagick->readImage($this->imagesPath . $name);

        $this->cutImage($imagick);
        $this->makeGrid($imagick);

        $imagick->writeImage($this->mosaicsPath . $name);
    }

    /**
     * @param Imagick $imagick
     */
    private function cutImage($imagick)
    {
        $d = $imagick->getImageGeometry();
        $width = intval($d['width']);
        $height = intval($d['height']);

        // Needed image size
        $nWidth = intval($width / $this->partSize) * $this->partSize;
        $nHeight = intval($height / $this->partSize) * $this->partSize;

        // Size remainder's
        $rWidth = $width - $nWidth;
        $rHeight = $height - $nHeight;

        $imagick->cropImage(
            $nWidth, $nHeight,
            intval($rWidth / 2), intval($rHeight / 2)
        );
    }

    /**
     * @param Imagick $imagick
     * @param $sx
     * @param $sy
     * @param $ex
     * @param $ey
     */
    private function drawLine($imagick, $sx, $sy, $ex, $ey)
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeColor($this->black);
        $draw->setStrokeWidth(1);
        $draw->line($sx, $sy, $ex, $ey);
        $imagick->drawImage($draw);
    }

    /**
     * @param Imagick $imagick
     * @param $sx
     * @param $sy
     * @param $ex
     * @param $ey
     */
    private function drawRect($imagick, $sx, $sy, $ex, $ey)
    {
        $this->drawLine($imagick, $sx, $sy, $ex, $sy); // top
        $this->drawLine($imagick, $ex, $sy, $ex, $ey); // right
//        $this->drawRect($imagick, $sx, $sy, $sx, $ey); // left
//        $this->drawRect($imagick, $sx, $ey, $ex, $ey); //bottom
    }

    /**
     * @param Imagick $imagick
     * @param $sx
     * @param $sy
     * @param $ex
     * @param $ey
     * @return Imagick
     */
    private function getImageFromRect($imagick, $sx, $sy, $ex, $ey)
    {
        $half = $this->partSize / 2;
        $pointX = $sx + $half;
        $pointY = $sy + $half;

        $copy = clone $imagick;
        $copy->cropImage($ex - $sx, $ey - $sy, $pointX, $pointY);

        return $copy;
    }

    /**
     * @param Imagick $imagick
     * @param bool $asHex
     * @return array|null|string
     */
    private function getAvgColor($imagick, $asHex = true)
    {
        try {
            $imagick->scaleimage(1, 1);
            /** @var ImagickPixel $pixel */
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

        if ($asHex) {
            return sprintf('%02X%02X%02X', $rgb['r'], $rgb['g'], $rgb['b']);
        }

        return $rgb;
    }

    /**
     * @param Imagick $imagick
     */
    private function makeGrid($imagick)
    {
        $d = $imagick->getImageGeometry();
        $width = intval($d['width']);
        $height = intval($d['height']);

        $wPartsCount = intval($width / $this->partSize);
        $hPartsCount = intval($height / $this->partSize);

        for ($wi = 0; $wi < $wPartsCount; $wi++) {
            for ($hi = 0; $hi < $hPartsCount; $hi++) {
                $imgContentPart = $this->getImageFromRect(
                    $imagick,
                    $wi * $this->partSize,
                    $hi * $this->partSize,
                    $wi * $this->partSize + $this->partSize,
                    $hi * $this->partSize + $this->partSize
                );

                $this->output->writeln(
                    print_r(
                        $this->getAvgColor($imgContentPart, false),
                        true
                    )
                );
            }
        }
    }
}