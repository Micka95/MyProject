<?php

namespace Lddt\MainBundle\Repository;

/**
 * DrawRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DrawRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllDraws() {
        $q = $this->getEntityManager()
           ->createQuery('SELECT d, c, p FROM LddtMainBundle:Draw d JOIN d.category c INNER JOIN d.pic p WHERE d.isOnline = TRUE ORDER BY d.updatedAt DESC');
        return $q->getResult();
    }

    public function findAllDrawsByCat($category) {
        // 'd' = Draw (les dessins) et 'c'= Catégory
        $q = $this->createQueryBuilder('d')
            ->orderBy('d.updatedAt','DESC')
            ->where('d.category = :cat')
            ->join('d.pic','p')
              ->andWhere('d.isOnline = true')
            ->setParameter('cat',$category)
            ->addSelect('c','p');
        return $q->getQuery()->getResult();
    }

    public function findAllDrawsByColor(array $colors) {
        $q = $this->createQueryBuilder('d');
        $q->join('d.color','c')
          ->join('d.pic','p')
          ->join('d.category','cat')
          ->where($q->expr()->in('c.name',$colors))
            ->andWhere('d.isOnline = true')
          ->addSelect('c','p','cat');
        return $q ->getQuery()->getResult();
    }

    public function findAllDrawsByTag(array $tag) {
        $q = $this->createQueryBuilder('d');
        $q->join('d.tags','t')
            ->join('d.category','cat')
            ->join('d.pic','p')
            ->where($q->expr()->in('t.name',$tag))
              ->andWhere('d.isOnline = true')
            ->addSelect('t','p','cat');
        return $q->getQuery()->getResult();
    }

    public function findAllDrawToPushOnLine() {
        $q = $this->createQueryBuilder('d')
        ->join('d.category','cat')
        ->join('d.pic','p')
        ->where('d.isOnline = false')
        ->addSelect('cat','p');
    return $q->getQuery()->getResult();
    }

}
