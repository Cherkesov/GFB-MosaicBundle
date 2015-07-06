<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02.07.2015
 * Time: 19:33
 */

namespace GFB\MosaicBundle\Command;

use GFB\MosaicBundle\Image\ImageProcessor;
use GFB\MosaicBundle\Image\ImagickExt;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMosaicCommand extends ContainerAwareCommand
{
    private $docRoot;
    private $imagesPath;
    private $mosaicsPath;

    /** @var OutputInterface */
    private $output;

    public function __construct()
    {
        parent::__construct();

        $this->docRoot = __DIR__ . "/../../../../web";
        $this->imagesPath = $this->docRoot . "/mosaic/images/";
        $this->mosaicsPath = $this->docRoot . "/mosaic/res/";
    }

    protected function configure()
    {
        $this
            ->setName('adw:mosaic:create')
            ->setDescription('Create mosaic command')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Which file do you want to process?'
            )
            ->addArgument(
                'partSize',
                InputArgument::REQUIRED,
                'Which file do you want to process?'
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
        $this->output = $output;

        if (!extension_loaded('imagick')) {
            $output->writeln("Error: Imagick not found! You should install it!");

            return -1;
        }

        if (!is_writable($this->mosaicsPath)) {
            chmod($this->mosaicsPath, 0777);
        }

        $imagick = new ImagickExt();

        $needleFormats = array("JPG", "JPEG", "PNG", "GIF");
        $formatsAll = $imagick->queryFormats();
        if (count(array_intersect($needleFormats, $formatsAll)) != count($needleFormats)) {
            $output->writeln("Error: not all the required formats are supported!");
            exit;
        }

        $name = $input->getArgument('file');
        $partSize = $input->getArgument('partSize');

        $imagick->readImage($this->imagesPath . $name);

        // TODO: Do some magic!

        try {
            $imagick->cutSizeMultipleOf($partSize);

            $processor = new ImageProcessor($imagick, $partSize, 4, true);
            $processor->setPartRepo(
                $this->getContainer()->get("doctrine")->getEntityManager()
                    ->getRepository("GFBMosaicBundle:Part")
            );

            $processor->runSegmentation();
            $processor->paveSegments();
        } catch (\Exception $ex) {
//            echo $ex->getMessage() . "\n";
//            echo $ex->getFile() . " # " .  $ex->getLine() . "\n";
        }

        // TODO: Finish some magic!

        $imagick->writeImage($this->mosaicsPath . "R" . $name);

        return 0;
    }
}