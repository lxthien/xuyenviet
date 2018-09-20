<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\NewsCategory;
use AppBundle\Entity\News;

class HomepageController extends Controller
{
    public function indexAction(Request $request)
    {
        $listCategoryOnHomepage = $this->get('settings_manager')->get('listCategoryOnHomepage');
        $objectOnHomepages = array();

        if (!empty($listCategoryOnHomepage)) {
            // $listCategoryOnHomepage like 1,2
            $listCategoryOnHomepage = explode(',', $listCategoryOnHomepage);

            if (is_array($listCategoryOnHomepage)) {
                for ($i = 0; $i < count($listCategoryOnHomepage); $i++) {
                    $objectOnHomepage = [];
                    $category = $this->getDoctrine()->getRepository(NewsCategory::class)->find($listCategoryOnHomepage[$i]);
                    //echo $category->getId(); die;
                    //print_r($category); die;
                    if ($category) {
                        $posts = $this->getDoctrine()
                            ->getRepository(News::class)
                            ->findBy(
                                array('postType' => 'post', 'category' => $category->getId()),
                                array('createdAt' => 'DESC'),
                                12
                            );
                    }

                    $objectOnHomepage = (object) array('category' => $category, 'posts' => $posts);
                    $objectOnHomepages[] = $objectOnHomepage;
                }
            }
        }

        return $this->render('homepage/index.html.twig', [
            'objectOnHomepages' => $objectOnHomepages,
        ]);
    }
}
