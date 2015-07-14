<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02.07.2015
 * Time: 19:33
 */

namespace GFB\MosaicBundle\Command;

use GFB\MosaicBundle\Image\MarkupGenerator;
use GFB\MosaicBundle\Image\MosaicProcessor;
use GFB\MosaicBundle\Image\ImagickExt;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateMosaicCommand
 * @package GFB\MosaicBundle\Command
 */
class CreateMosaicCommand extends ContainerAwareCommand
{
    private $webDir;
    private $imagesPath;
    private $mosaicsPath;

    /** @var OutputInterface */
    private $output;

    public function __construct()
    {
        parent::__construct();

        $this->imagesPath = "mosaic/images/";
        $this->mosaicsPath = "mosaic/res/";
    }

    protected function configure()
    {
        $this
            ->setName('gfb:mosaic:create')
            ->setDescription('Create mosaic command')
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                "Which file do you want to process?",
                null
            )
            ->addOption(
                'size',
                null,
                InputOption::VALUE_REQUIRED,
                "Which file do you want to process?",
                null
            )
            ->addOption(
                'level',
                null,
                InputOption::VALUE_REQUIRED,
                "Which file do you want to process?",
                null
            )
            ->addOption(
                'accuracy',
                null,
                InputOption::VALUE_REQUIRED,
                "Which file do you want to process?",
                null
            )
            ->addOption(
                'opacity',
                null,
                InputOption::VALUE_REQUIRED,
                "Which file do you want to process?",
                null
            )
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // php app/console gfb:mosaic:create --file="4.jpg" --size=32 --level=2 --accuracy=16 --opacity=0.6

        $this->webDir = $this->getContainer()->get("kernel")->getRootDir() . "/../web/";
        $this->output = $output;

        if (!extension_loaded('imagick')) {
            $output->writeln("Error: Imagick not found! You should install it!");

            return -1;
        }

        $mosaicFullPath = $this->webDir . $this->mosaicsPath;

        if (!file_exists($mosaicFullPath)) {
            mkdir($mosaicFullPath, 0777);
        }
        if (!is_writable($mosaicFullPath)) {
            chmod($mosaicFullPath, 0777);
        }

        $imagick = new ImagickExt();

        $needleFormats = array("JPG", "JPEG", "PNG", "GIF");
        $formatsAll = $imagick->queryFormats();
        if (count(array_intersect($needleFormats, $formatsAll)) != count($needleFormats)) {
            $output->writeln("Error: not all the required formats are supported!");
            exit;
        }

        $name = $input->getOption('file');
        $partSize = $input->getOption('size');
        $segmentationLevel = $input->getOption("level");
        $accuracy = $input->getOption('accuracy');
        $partOpacity = $input->getOption("opacity");

        $imagick->readImage($this->webDir . $this->imagesPath . $name);

        // Do some magic!

        try {
            $processor = new MosaicProcessor($imagick, $this->webDir);
            $processor->setPartRepo(
                $this->getContainer()->get("doctrine")->getEntityManager()
                    ->getRepository("GFBMosaicBundle:Part")
            );

            $processor->segmentation($partSize, $segmentationLevel);
            $processor->paving($accuracy, $partOpacity);
            $imagick->writeImage($mosaicFullPath . "R" . $name);

            $markupGen = new MarkupGenerator();
            $markupGen->generate(
                $imagick,
                $this->webDir,
                $this->mosaicsPath . "R" . $name,
                $processor->getSegments()
            );
        } catch (\Exception $ex) {
            echo $ex->getMessage() . "\n";
        }

        // Finish some magic!

        return 0;
    }
}