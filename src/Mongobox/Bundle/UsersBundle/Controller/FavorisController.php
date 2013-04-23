<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Mongobox\Bundle\UsersBundle\Entity\UserFavoris;


class FavorisController extends Controller
{
	/**
	 * Fonction pour ajouter une vidéo à la liste des vidéos favorites
	 * @Route("/ajax/favorite/new/{id_video}", name="ajax_new_video_favorite")
	 */
	public function ajaxVideoAddToFavoriteAction($id_video)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		$isAlreadyFavorite = $em->getRepository('MongoboxUsersBundle:UserFavoris')->checkUserFavorite($id_video, $user);

		$result = array(
			'add' =>  false,
			'already' => true
		);

		if(!$isAlreadyFavorite)
		{
			$newFavoris = new UserFavoris();
			$newFavoris
					->setUser($user)
					->setVideo($em->find('MongoboxJukeboxBundle:Videos', $id_video))
					->setDateFavoris(new \DateTime)
			;

			$em->persist($newFavoris);
			$em->flush();

			$result['add'] = true;
			$result['already'] = false;
		}

		return new Response(json_encode($result));
	}

	/**
	 * Fonction pour voir les favoris de l'utilisateur
	 * @Route("/profil/favoris/{page}", name="user_voir_favoris", requirements={"page" = "\d+"}, defaults={"page" = 0})
	 * @Template()
	 */
	public function voirFavorisAction($page)
	{
		$manager = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		$videos_user = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getUniqueFavorisUser($user, $page);
		foreach($videos_user as &$video)
		{
			$array_date_first_ajout = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getDateAddToFavorite($user, $video['id']);
			$video = array_merge($video, $array_date_first_ajout);
			$video['listes'] = $manager->getRepository('MongoboxUsersBundle:ListeFavoris')->getListesUserForOneVideo($user, $video['id']);
			foreach($video['listes'] as &$liste)
			{
				$array_date_ajout = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getDateAddToList($user, $video['id'], $liste['id']);
				$liste = array_merge($liste, $array_date_ajout);
			}
		}
		//echo '<pre>';var_dump($videos_user);exit;

		return array(
			'favoris' => $videos_user
		);
	}

	/**
	 * Fonction pour supprimer une vidéo des favoris
	 * @Route("/ajax/favoris/{id_video}/remove", name="remove_video_bookmark", requirements={"id_video" = "\d+"})
	 */
	public function removeVideoFromBookmarkAction($id_video)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();

		$is_removed = $em->getRepository("MongoboxUsersBundle:UserFavoris")->removeVideoFromBookmark($user, $id_video);

		$retour = array(
			'success' => $is_removed,
			'message' => ($is_removed) ? "Vidéo supprimée avec succès de vos favoris": "Echec lors de la suppression",
			"fav" => $id_video
		);

		return new Response(json_encode($retour));
	}

	/**
	 * Fonction pour supprimer une vidéo dans une liste
	 * @Route("/ajax/favoris/{id_video}/remove/liste/{id_liste}", name="remove_video_liste", requirements={"id_video" = "\d+", "id_liste" = "\d+"})
	 */
	public function removeVideoFromList($id_video, $id_liste)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();

		$fav = $em->getRepository("MongoboxUsersBundle:UserFavoris")->findOneBy(array(
			"user" => $user,
			"video" => $em->find("MongoboxJukeboxBundle:Videos", $id_video),
			"liste" => $em->find("MongoboxUsersBundle:ListeFavoris", $id_liste)
		));

		if( is_object($fav) )
		{
			$em->remove($fav);
			$em->flush();

			$retour = array(
				"success" => true,
				"message" => "Vidéo supprimée de la liste avec succès"
			);
		} else
		{
			$retour = array(
				"success" => false,
				"message" => "Echec lors de la suppression de la vidéo"
			);
		}

		return new Response(json_encode($retour));
	}

	/**
	 * Fonction pour voir les listes de favoris de l'utilisateur
	 * @Route("/profil/listes", name="user_voir_listes")
	 * @Template()
	 */
	public function voirListeFavorisAction()
	{
		return array();
	}
}

?>
