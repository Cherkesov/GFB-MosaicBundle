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
use Symfony\Component\Console\Output\OutputInterface;

class FillPartsBaseCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    private $output;

    public function __construct()
    {
        parent::__construct();

        $this->docRoot = __DIR__ . "/../../../../web";
        $this->basePath = "/mosaic/base/";
    }

    protected function configure()
    {
        $this
            ->setName('gfb:mosaic:fill-base')
            ->setDescription('Fill base of parts command')/*->addOption(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                "Which images do you want to find?",
                null
            )*/
        ;;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $results = array();
        while (count($results) < 100) {
            $data = $this->getData("face", count($results));
            $results1 = $data["responseData"]["results"];

            if (!is_array($results1)) {
                print_r($data);
                break;
            }

            $results = array_merge($results, $results1);
        }

        $output->writeln("Compile " . count($results) . " images...");
        $this->processResults($results);
    }

    /**
     * @param array $results
     */
    private function processResults($results)
    {
        $em = $this->getEm();
        /** @var PartRepo $partRepo */
        $partRepo = $em->getRepository("ADWMosaicBundle:Part");

        foreach ($results as $imgData) {
            $code = md5($imgData["imageId"] . $imgData["url"]);

            $this->output->write("  >>> " . $imgData["url"]);

            $imagick = new ImagickExt();
            try {
                $imagick->readImage($imgData["url"]);
            } catch (\Exception $ex) {
                $this->output->writeln(" [FAIL] ");
                continue;

                /*$fileContent = file_get_contents($imgData["url"]);
                if (is_string($fileContent) && strpos($fileContent, "base64") > 0) {
                    $fileContent = str_replace("data:image/jpeg;base64,", "", $fileContent);
                    $fileContent = base64_decode($fileContent);
                    if (!$fileContent) {
                        continue;
                    }
                    $imagick->readImageBlob($fileContent);
                    print_r($fileContent);
                } else {
                    continue;
                }*/
            }
            $this->output->writeln(" [OK] ");

            $imagick->cutToSquare();
            $imagick->setFormat("png");
            $filename = $imgData["imageId"] . ".png";
            $imagick->writeImage($this->docRoot . $this->basePath . $filename);

            $part = $partRepo->findOneByCode($code);
            if (!$part) {
                $part = new Part();
            }

            $part
                ->setAvgColor($imagick->getAvgColor())
                ->setCode($code)
                ->setPath($this->basePath . $filename)
                ->setWidth($imagick->getWidth())
                ->setHeight($imagick->getHeight());

            if ($part->getId() > 0) {
                $em->merge($part);
            } else {
                $em->persist($part);
            }
            $em->flush();
        }
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|\Doctrine\ORM\EntityManager|object
     */
    private function getEm()
    {
        return $this->getContainer()->get("doctrine")->getEntityManager();
    }

    /**
     * @param string $query
     * @param int $offset
     * @return array
     */
    public function getData($query, $offset)
    {
        $filename = "http://ajax.googleapis.com/ajax/services/search/images?v=1.0&q={$query}&start={$offset}";
        $s = file_get_contents($filename);

        if ($s == null) {
            $ch = curl_init($filename);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $s = curl_exec($ch);
            curl_close($ch);
        }

        return json_decode($s, true);
    }
}