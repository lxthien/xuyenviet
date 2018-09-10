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

use AppBundle\Entity\NewsCategory;
use AppBundle\Form\NewsCategoryType;
use AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to manage blog contents in the backend.
 *
 * @Route("/admin/newscategory")
 * @Security("has_role('ROLE_ADMIN')")
 */

class NewsCategoryController extends Controller
{
    /**
     * Lists all NewsCategory entities.
     *
     * @Route("/", name="admin_newscategory_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(NewsCategory::class)->findAll();

        return $this->render('admin/newscategory/index.html.twig', [
            'objects' => $categories
        ]);
    }

    /**
     * Creates a new NewsCategory entity.
     *
     * @Route("/new", name="admin_newscategory_new")
     * @Method({"GET", "POST"})
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    public function newAction(Request $request, Slugger $slugger)
    {
        $category = new NewsCategory();
        $category->setAuthor($this->getUser());

        // See https://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm(NewsCategoryType::class, $category)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'newscategory.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_newscategory_new');
            }

            return $this->redirectToRoute('admin_newscategory_index');
        }

        return $this->render('admin/newscategory/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a NewsCategory entity.
     *
     * @Route("/{id}", requirements={"id": "\d+"}, name="admin_newscategory_show")
     * @Method("GET")
     */
    public function showAction(NewsCategory $category)
    {
        // This security check can also be performed
        // using an annotation: @Security("is_granted('show', post)")
        //$this->denyAccessUnlessGranted('show', $post, 'Posts can only be shown to their authors.');

        return $this->render('admin/blog/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Displays a form to edit an existing NewsCategory entity.
     *
     * @Route("/{id}/edit", requirements={"id": "\d+"}, name="admin_newscategory_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, NewsCategory $category, Slugger $slugger)
    {
        //$this->denyAccessUnlessGranted('edit', $category, 'Posts can only be edited by their authors.');

        $form = $this->createForm(NewsCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'newscategory.updated_successfully');

            return $this->redirectToRoute('admin_newscategory_index');
        }

        return $this->render('admin/newscategory/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a NewsCategory entity.
     *
     * @Route("/{id}/delete", name="admin_newscategory_delete")
     * @Method("POST")
     * @Security("is_granted('delete', post)")
     *
     * The Security annotation value is an expression (if it evaluates to false,
     * the authorization mechanism will prevent the user accessing this resource).
     */
    public function deleteAction(Request $request, NewsCategory $category)
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_newscategory_index');
        }

        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'newscategory.deleted_successfully');

        return $this->redirectToRoute('admin_newscategory_index');
    }
}
