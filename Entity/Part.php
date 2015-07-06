<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 02.07.2015
 * Time: 22:56
 */

namespace GFB\MosaicBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Part
 * @package GFB\MosaicBundle\Entity
 * @ORM\Entity(repositoryClass="GFB\MosaicBundle\Repo\PartRepo")
 * @ORM\Table(name="adw_mosaic_part")
 */
class Part
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Color
     * @ORM\OneToOne(targetEntity="Color",cascade={"persist", "remove"})
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    /*public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/documents';
    }*/
}