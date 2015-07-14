<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 03.07.2015
 * Time: 13:24
 */

namespace GFB\MosaicBundle\Command;

use GFB\MosaicBundle\Entity\Part;
use GFB\MosaicBundle\Image\ImagickExt;
use GFB\MosaicBundle\Repo\PartRepo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FillBaseFromGoogleCommand extends ContainerAwareCommand
{
    /** @var array */
    private static $GOOGLE_COLORS = array(
        "black",
        "blue",
        "brown",
        "gray",
        "green",
        "orange",
        "pink",
        "purple",
        "red",
        "teal",
        "white",
        "yellow",
    );

    /** @var string */
    private $webDir;

    /** @var OutputInterface */
    private $output;

    /**
     * Default constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->basePath = "mosaic/base/";
    }

    /**
     * Configure arguments and options sets for command
     */
    protected function configure()
    {
        $this
            ->setName('gfb:mosaic:fill:google')
            ->setDescription('Fill base of parts command')
            ->addOption(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                "Which images do you want to find?",
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
        if (!extension_loaded('imagick')) {
            $output->writeln("Error: Imagick not found! You should install it!");
            return -1;
        }

        $this->webDir = $this->getContainer()->get("kernel")->getRootDir() . "/../web/";
        $this->output = $output;

        $query = $input->getOption("query");
        foreach (self::$GOOGLE_COLORS as $color) {
            $results = $this->collectingResults($query, $color);

            $output->writeln("Compile [{$color}] " . count($results) . " images...");
            $this->processResults($results, $color);
        }

        return 0;
    }

    /**
     * @param string $query
     * @param string $color
     * @return array
     */
    private function collectingResults($query, $color)
    {
        $results = array();
        while (count($results) < 100) {
            $data = $this->getData($query, count($results), $color);
            $results1 = $data["responseData"]["results"];

            if (!is_array($results1)) {
                break;
            }

            $results = array_merge($results, $results1);
        }
        return $results;
    }

    /**
     * @param array $results
     * @param string $color
     */
    private function processResults($results, $color)
    {
        $em = $this->getEm();
        /** @var PartRepo $partRepo */
        $partRepo = $em->getRepository("GFBMosaicBundle:Part");

        foreach ($results as $imgData) {
            $code = md5($imgData["imageId"] . $imgData["url"]);

            $this->output->write("  >>> " . $imgData["url"]);

            $imagick = new ImagickExt();
            try {
                $imagick->readImage($imgData["url"]);
            } catch (\Exception $ex) {
                $this->output->writeln(" [FAIL] ");
                continue;
            }
            $this->output->writeln(" [OK] ");

            $imagick->cutToSquare();
            $imagick->setFormat("png");
            $filename = $code . ".png";
            $imagick->writeImage($this->webDir . $this->basePath . $filename);

            $part = $partRepo->findOneByCode($code);
            if (!$part) {
                $part = new Part();
            }

            $part
                ->setAvgColor($imagick->getAvgColor())
                ->setCode($code)
                ->setPath($this->basePath . $filename)
                ->setWidth($imagick->getWidth())
                ->setHeight($imagick->getHeight())
                ->setColor($color);

            if ($part->getId() > 0) {
                $em->merge($part);
            } else {
                $em->persist($part);
            }
            $em->flush();
        }
    }

    /**
     * @param string $query
     * @param int $offset
     * @param string $color
     * @return array
     */
    public function getData($query, $offset, $color = "")
    {
        $filename = "http://ajax.googleapis.com/ajax/services/search/images?v=1.0&q={$query}&start={$offset}&imgcolor={$color}";
        $s = file_get_contents($filename);

        if ($s == null) {
            $ch = curl_init($filename);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $s = curl_exec($ch);
            curl_close($ch);
        }

        return json_decode($s, true);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|\Doctrine\ORM\EntityManager|object
     */
    private function getEm()
    {
        return $this->getContainer()->get("doctrine")->getEntityManager();
    }
}