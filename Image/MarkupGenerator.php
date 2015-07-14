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
     * @param string $docRoot
     * @param string $filePath
     * @param Segment[] $segments
     */
    public function generate($imagick, $docRoot, $filePath, $segments)
    {
        $markup = "
<style>
    .mosaic_canvas {
        width: 1280px;
        margin: 0 auto;
        background-image: url(/{$filePath});
        background-size: 100% auto;
        position: relative;
    }

    .mosaic_canvas img {
        width: 100%;
    }

    .mosaic_part:hover {
        background: rgba(95, 158, 160, 0.5);
    }

    .mosaic_popup {
        margin-left: -210px;
        width: 420px;
        background: #808080;
        padding: 24px;
        display: none;
        position: absolute;
        left: 50%;
        top: 25%;
    }

    .mosaic_popup img {
        width: 100%;
    }
</style>
        ";

        $markup .= "<div class=\"mosaic_canvas\">";
        $markup .= "    <img src='/{$filePath}' style='visibility:hidden;'/>";

        $cW = $imagick->getWidth();
        $cH = $imagick->getHeight();

        echo "\nCanvas size : {$cW}x{$cH}\n";

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

            $(".mosaic_canvas .mosaic_part").click(function(event) {
                event.stopPropagation();
                $popup.find("img").attr("src", $(this).data("orig"));
                $popup.show();
            });

            $("body").click(function() {
                $popup.hide();
            });
        });
    }
</script>
';

        file_put_contents($docRoot. $filePath . ".html", $markup);
    }

    /**
     * @param $canvasWidth
     * @param $canvasHeight
     * @param Segment $segment
     * @return string
     */
    private function itemCode($canvasWidth, $canvasHeight, $segment)
    {
        if ($segment->getPart() == null) {
            return "";
        }

        $width = floatval($segment->getWidth() / ($canvasWidth / 100));
        $height = floatval($segment->getHeight() / ($canvasHeight / 100));

        $left = floatval($segment->getStartX() / ($canvasWidth / 100));
        $top = floatval($segment->getStartY() / ($canvasHeight / 100));

        echo "Part size : {$width}x{$height} {$left} {$top}\n";

        $path = ($segment->getPart()) ? $segment->getPart()->getPath() : "";

        return "
    <div class=\"mosaic_part\" style=\"position: absolute;
        width: {$width}%; height: {$height}%;
        top: {$top}%; left: {$left}%;\" data-orig=\"/{$path}\"></div>";
    }
}