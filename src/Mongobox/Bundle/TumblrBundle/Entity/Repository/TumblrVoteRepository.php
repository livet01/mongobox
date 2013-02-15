<?php

namespace Mongobox\Bundle\TumblrBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TumblrVoteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TumblrVoteRepository extends EntityRepository
{
    public function sommeVotes($tumblr)
    {
        $em = $this->getEntityManager();
        $query = $em
                ->createQuery('SELECT t as tumblr, SUM(tv.note) as total FROM MongoboxTumblrBundle:Tumblr t LEFT JOIN t.tumblr_vote tv WHERE tv.tumblr = '.$tumblr->getIdTumblr())
                ;
        $result = $query->getResult();
        if(is_null($result[0][1])) $somme = 0;
        else $somme = (int) $result[0][1];
        return $somme;
    }
    
    public function top($group, $max = 5)
    {
        $em = $this->getEntityManager();
        $q = $em
                ->createQuery('SELECT t as tumblr, SUM(tv.note) as total FROM MongoboxTumblrBundle:Tumblr t LEFT JOIN t.tumblr_vote tv LEFT JOIN t.groups tg WHERE tg.id IN (:groupe) GROUP BY t.id_tumblr ORDER BY total DESC')
                ->setParameters(array(
                		'groupe' => $group
                ))
                ->setMaxResults($max)
                ;
        
        //echo $q->getSQL();exit;
        
        if($max == 1) $result = $q->getOneOrNullResult ();
        else $result = $q->getResult();
        return $result;
    }

    public function topPeriod($group, $days = 7, $max = 5)
    {
        $date = date('Y-m-d 00:00:00', strtotime('-'.$days.' day'));
        $em = $this->getEntityManager();
        $q = $em
                ->createQuery("SELECT t as tumblr, SUM(tv.note) as total FROM MongoboxTumblrBundle:Tumblr t LEFT JOIN t.tumblr_vote tv LEFT JOIN t.groups tg WHERE tg.id IN (:group) AND t.date > '".$date."' GROUP BY t.id_tumblr ORDER BY total DESC")
                ->setParameters(array(
                		'group' => $group
                ))
				->setMaxResults($max)
                ;
        
        if($max == 1) $result = $q->getOneOrNullResult ();
        else $result = $q->getResult();
        return $result;
    }
}