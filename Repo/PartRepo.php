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
        $colorRepo = $this->_em->getRepository("ADWMosaicBundle:Color");

        // Пробуем найти полное соответствие цвета
        $colors = $colorRepo->findBy(array(
            "red" => $color->getRed(),
            "green" => $color->getGreen(),
            "blue" => $color->getBlue(),
        ));

        // Если не получилось то ищем похожие цвета с некоторой погрешностью
        if (count($colors) == 0) {
            $criteria = new Criteria();
            $criteria->andWhere(
                $criteria->expr()->orX(
                    $criteria->expr()->gt("red", $color->getRed() - $accuracy),
                    $criteria->expr()->lt("red", $color->getRed() + $accuracy)
                )
            );
            $criteria->andWhere(
                $criteria->expr()->orX(
                    $criteria->expr()->gt("green", $color->getGreen() - $accuracy),
                    $criteria->expr()->lt("green", $color->getGreen() + $accuracy)
                )
            );
            $criteria->andWhere(
                $criteria->expr()->orX(
                    $criteria->expr()->gt("blue", $color->getBlue() - $accuracy),
                    $criteria->expr()->lt("blue", $color->getBlue() + $accuracy)
                )
            );

            $colors = $colorRepo->matching($criteria);
        }

        if (count($colors) > 0) {
            // TODO: Нужно выбирать один из элементов рандомно
            return $this->findOneBy(array("avgColor" => $color[0]));
        }

        return null;
    }
}