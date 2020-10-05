<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\NewsCategory;
use AppBundle\Entity\News;
use AppBundle\Entity\Banner;

class HomepageController extends Controller
{
    public function indexAction(Request $request)
    {
        $listCategoriesOnHomepage = $this->get('settings_manager')->get('listCategoryOnHomepage');
        $blocksOnHomepage = array();

        if (!empty($listCategoriesOnHomepage)) {
            $listCategoriesOnHomepage = json_decode($listCategoriesOnHomepage, true);

            if (is_array($listCategoriesOnHomepage)) {
                for ($i = 0; $i < count($listCategoriesOnHomepage); $i++) {
                    $blockOnHomepage = [];
                    $category = $this->getDoctrine()
                                    ->getRepository(NewsCategory::class)
                                    ->find($listCategoriesOnHomepage[$i]["id"]);

                    if ($category) {
                        $posts = $this->getDoctrine()
                            ->getRepository(News::class)
                            ->findBy(
                                array('postType' => 'post', 'enable' => 1, 'category' => $category->getId()),
                                array('createdAt' => 'DESC'),
                                $listCategoriesOnHomepage[$i]["items"]
                            );
                    }

                    $blockOnHomepage = (object) array('category' => $category, 'posts' => $posts, 'description' => $listCategoriesOnHomepage[$i]["description"]);
                    $blocksOnHomepage[] = $blockOnHomepage;
                }
            }
        }

        $banners = $this->getDoctrine()
            ->getRepository(Banner::class)
            ->findBy(
                array('bannercategory' => 1),
                array('createdAt' => 'DESC')
            );

        return $this->render('homepage/index.html.twig', [
            'blocksOnHomepage' => $blocksOnHomepage,
            'banners' => $banners,
            'showSlide' => true
        ]);
    }
}
