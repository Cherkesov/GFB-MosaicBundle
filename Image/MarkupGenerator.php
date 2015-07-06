<?php
/**
 * Created by PhpStorm.
 * User: scherk01
 * Date: 06.07.2015
 * Time: 19:48
 */

namespace GFB\MosaicBundle\Image;

use GFB\MosaicBundle\Entity\Segment;

class MarkupGenerator
{
    /**
     * @param ImagickExt $imagick
     * @param string $filePath
     * @param Segment[] $segments
     * @return string
     */
    public function generate($imagick, $filePath, $segments)
    {
        $markup = "<div style='
background-image: url({$filePath});
background-size: 100% auto;
position: relative;'>";

        foreach ($segments as $segment) {
            $w = $segment->getWidth();
            $h = $segment->getHeight();
            $markup .= "<div style='position: absolute;'></div>";
        }

        $markup .= "</div>";

        return $markup;
    }
}