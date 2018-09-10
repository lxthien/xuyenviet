<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @Route("/admin")
 * @Route("/admin/dashboard")
 * @Security("has_role('ROLE_ADMIN')")
 */

class DashboardController extends Controller
{
    /**
     * 
     * @Route("/", name="admin_dashboard_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('admin/dashboard/index.html.twig');
    }
}