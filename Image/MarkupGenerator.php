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
     */
    public function generate($imagick, $filePath, $segments)
    {
        $markup = "
<style>
    .mosaic_canvas {
        background-image: url({$filePath});
        background-size: 100% auto;
        position: relative;
    }

    .mosaic_popup {
        background: #808080;
        padding: 24px;
        display: none;
        max-width: 800px;
    }
</style>
        ";

        $markup .= "<div class=\"mosaic_canvas\">";
        $markup .= "    <img src='{$filePath}' style='visibility:hidden;'/>";

        $cW = $imagick->getWidth();
        $cH = $imagick->getHeight();

        foreach ($segments as $segment) {
            $markup .= $this->itemCode($cW, $cH, $segment);
        }

        $markup .= "</div>";

        $markup .= '
<div class="mosaic_popup">
    <img src="#"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
    if (window.jQuery != undefined) {
        $(function() {
            var $popup = $(".mosaic_popup");

            $(".mosaic_canvas .mosaic_part").hover(function() {
                $popup.find("img").attr("src", $(this).data("orig"));
                $popup.show();
            }, function() {
                $popup.hide();
            });
        });
    }
</script>
';

        file_put_contents($filePath . ".html", $markup);
    }

    /**
     * @param $canvasWidth
     * @param $canvasHeight
     * @param Segment $segment
     * @return string
     */
    private function itemCode($canvasWidth, $canvasHeight, $segment)
    {
        $width = intval($segment->getWidth() / ($canvasWidth / 100));
        $height = intval($segment->getHeight() / ($canvasHeight / 100));

        $left = intval($segment->getStartX() / ($canvasWidth / 100));
        $top = intval($segment->getStartY() / ($canvasHeight / 100));

        return "
    <div class=\"mosaic_part\" style=\"position: absolute;
        width: {$width}px; height: {$height}px;
        top: {$top}px; left: {$left}px;' data-orig='{$segment->getPart()->getPath()}\"></div>";
    }
}