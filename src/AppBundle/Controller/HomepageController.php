<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\News;

class HomepageController extends Controller
{
    public function indexAction(Request $request)
    {
        $services = $this->getDoctrine()
            ->getRepository(News::class)
            ->findBy(
                array('postType' => 'post'),
                array('createdAt' => 'DESC'),
                12
            );

        return $this->render('homepage/index.html.twig', [
            'services' => $services,
        ]);
    }
}
