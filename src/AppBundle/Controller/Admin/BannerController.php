<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\BannerCategory;
use AppBundle\Entity\Banner;

use AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to manage banner in the backend.
 *
 * @Security("has_role('ROLE_ADMIN')")
 */

class BannerController extends Controller
{
    /**
     * Lists all Banner entities.
     *
     * @Route("admin/banner", name="admin_banner_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $banners = $em->getRepository(Banner::class)->findAll();

        return $this->render('admin/banner/index.html.twig', ['objects' => $banners]);
    }

    /**
     * Lists all Banner categories entities.
     *
     * @Route("admin/bannercategory", name="admin_bannercategory_index")
     * @Method("GET")
     */
    public function bannerCategoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $bannercategories = $em->getRepository(BannerCategory::class)->findAll();

        return $this->render('admin/banner/index.html.twig', ['objects' => $bannercategories]);
    }
}