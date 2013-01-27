<?php

namespace Mongobox\Bundle\TumblrBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TumblrRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TumblrRepository extends EntityRepository
{
	public function findLast($groups, $maxResults = 0, $firstResult = 0)
	{
		$em = $this->getEntityManager();
		$qb = $em->createQueryBuilder();

		$qb->select('t')
		->from('MongoboxTumblrBundle:Tumblr', 't')
		->leftJoin('t.groups', 'g')
		->where("g.id IN (:groups)")
		->orderBy('t.date', 'DESC')
		->setParameters( array(
				'groups' => $groups
		));
		if($maxResults != 0)
		{
			$qb->setMaxResults($maxResults)
			->setFirstResult($firstResult);
		}

		$query = $qb->getQuery();
		return $query->getResult();
	}
}
