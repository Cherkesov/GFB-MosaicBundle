<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 03.07.2015
 * Time: 15:34
 */

namespace GFB\MosaicBundle\Repo;


use GFB\MosaicBundle\Entity\Color;
use GFB\MosaicBundle\Entity\Part;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class PartRepo extends EntityRepository
{
    /**
     * @param $id
     * @return Part
     */
    public function findOneByCode($id)
    {
        return $this->findOneBy(array("code" => $id));
    }

    /**
     * @param Color $color
     * @param int $accuracy
     * @return Part|null
     */
    public function findOneWithColorLike($color, $accuracy)
    {
        // Пробуем найти полное соответствие цвета
        $partQb = $this->createQueryBuilder("part");
        $partQb->join("part.avgColor", "color");
        $partQb->where($partQb->expr()->eq("color.red", $color->getRed()));
        $partQb->where($partQb->expr()->eq("color.green", $color->getGreen()));
        $partQb->where($partQb->expr()->eq("color.blue", $color->getBlue()));
        $partQb->where($partQb->expr()->eq("part.active", true));
        $parts = $partQb->getQuery()->execute();

        $criteria = new Criteria();
        $expressionRed = $criteria->expr()->andX(
            $criteria->expr()->gt("red", $color->getRed() - $accuracy),
            $criteria->expr()->lt("red", $color->getRed() + $accuracy)
        );
        $expressionGreen = $criteria->expr()->andX(
            $criteria->expr()->gt("green", $color->getGreen() - $accuracy),
            $criteria->expr()->lt("green", $color->getGreen() + $accuracy)
        );
        $expressionBlue = $criteria->expr()->andX(
            $criteria->expr()->gt("blue", $color->getBlue() - $accuracy),
            $criteria->expr()->lt("blue", $color->getBlue() + $accuracy)
        );

        // Если не получилось то ищем похожие цвета с некоторой погрешностью
        if (count($parts) == 0) {
            $partQb = $this->createQueryBuilder("part");
            $partQb->join("part.avgColor", "color");
            $partQb->andWhere($expressionRed);
            $partQb->andWhere($expressionGreen);
            $partQb->andWhere($expressionBlue);
            $partQb->where($partQb->expr()->eq("part.active", true));
            $parts = $partQb->getQuery()->execute();
        }

        if (count($parts) > 0) {
            $index = rand(0, count($parts) - 1);
            return $parts[$index];
        }

        return null;
    }
}