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

        $partQb->andWhere($partQb->expr()->eq("color.red", $color->getRed()));
        $partQb->andWhere($partQb->expr()->eq("color.green", $color->getGreen()));
        $partQb->andWhere($partQb->expr()->eq("color.blue", $color->getBlue()));

        $partQb->andWhere($partQb->expr()->eq("part.active", true));
        $parts = $partQb->getQuery()->execute();

        /*$criteria = new Criteria();
        $expressionRed = $criteria->expr()->andX(
            $criteria->expr()->gt("color.red", $color->getRed() - $accuracy),
            $criteria->expr()->lt("color.red", $color->getRed() + $accuracy)
        );
        $expressionGreen = $criteria->expr()->andX(
            $criteria->expr()->gt("color.green", $color->getGreen() - $accuracy),
            $criteria->expr()->lt("color.green", $color->getGreen() + $accuracy)
        );
        $expressionBlue = $criteria->expr()->andX(
            $criteria->expr()->gt("color.blue", $color->getBlue() - $accuracy),
            $criteria->expr()->lt("color.blue", $color->getBlue() + $accuracy)
        );*/

        // Если не получилось то ищем похожие цвета с некоторой погрешностью
        if (count($parts) == 0) {
            $partQb = $this->createQueryBuilder("part");
            $partQb->join("part.avgColor", "color");

//            $partQb->andWhere($expressionRed);
//            $partQb->andWhere($expressionGreen);
//            $partQb->andWhere($expressionBlue);

//            $partQb->andWhere("color.red > ". ($color->getRed() - $accuracy));
//            $partQb->andWhere("color.red < ". ($color->getRed() + $accuracy));
//            $partQb->andWhere("color.green > ". ($color->getGreen() - $accuracy));
//            $partQb->andWhere("color.green < ". ($color->getGreen() + $accuracy));
//            $partQb->andWhere("color.blue > ". ($color->getBlue() - $accuracy));
//            $partQb->andWhere("color.blue < ". ($color->getBlue() + $accuracy));

            $partQb->andWhere($partQb->expr()->gt("color.red", $color->getRed() - $accuracy / 2));
            $partQb->andWhere($partQb->expr()->lt("color.red", $color->getRed() + $accuracy / 2));
            $partQb->andWhere($partQb->expr()->gt("color.green", $color->getGreen() - $accuracy / 2));
            $partQb->andWhere($partQb->expr()->lt("color.green", $color->getGreen() + $accuracy / 2));
            $partQb->andWhere($partQb->expr()->gt("color.blue", $color->getBlue() - $accuracy / 2));
            $partQb->andWhere($partQb->expr()->lt("color.blue", $color->getBlue() + $accuracy / 2));

            $partQb->andWhere($partQb->expr()->eq("part.active", true));
            $parts = $partQb->getQuery()->execute();
        }

        if (count($parts) > 0) {
            $index = rand(0, count($parts) - 1);
            return $parts[$index];
        }

        return null;
    }
}