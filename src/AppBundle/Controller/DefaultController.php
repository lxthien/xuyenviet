<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\News;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
    	$posts = $this->getDoctrine()
            ->getRepository(News::class)
            ->findBy(
                array(),
                array('created_at' => 'DESC'),
                10
            );

        return $this->render('default/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
