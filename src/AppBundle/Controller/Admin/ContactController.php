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

use AppBundle\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to manage blog contents in the backend.
 *
 * @Route("/admin/contact")
 * @Security("has_role('ROLE_ADMIN')")
 */

class ContactController extends Controller
{
    /**
     * Lists all Contact entities.
     *
     * @Route("/", name="admin_contact_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $contacts = $em->getRepository(Contact::class)->findAll();

        return $this->render('admin/contact/index.html.twig', [
            'objects' => $contacts
        ]);
    }

    /**
     * Deletes a Contact entity.
     *
     * @Route("/{id}/delete", name="admin_contact_delete")
     * @Method("POST")
     * @Security("is_granted('delete', post)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     */
    public function deleteAction(Request $request, Contact $contact)
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_contact_index');
        }

        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite

        $em = $this->getDoctrine()->getManager();
        $em->remove($contact);
        $em->flush();

        $this->addFlash('success', 'deleted_successfully');

        return $this->redirectToRoute('admin_contact_index');
    }
}
