<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;

use AppBundle\Entity\Contact;

class ContactController extends Controller
{
    /**
     * @Route("contact", name="contact")
     */
    public function indexAction(Request $request)
    {
        $contact = new Contact();
        
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, array('label' => 'label.author'))
            ->add('email', EmailType::class, array('label' => 'label.author_email'))
            ->add('phone', TextType::class, array('label' => 'label.phone'))
            ->add('title', TextType::class, array('label' => 'label.title'))
            ->add('contents', TextareaType::class, array(
                'label' => 'label.content',
                'attr' => array('rows' => '7')
            ))
            ->add('recaptcha', EWZRecaptchaType::class)
            ->add('send', SubmitType::class, array('label' => 'label.send'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            
            if (null === $contact->getId()) {
                $this->addFlash(
                    'error',
                    'Have a problem into process. Go back and try it again.'
                );

                return $this->render('contact/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            } else {
                $this->addFlash(
                    'notice',
                    'Thank for your contact'
                );

                return $this->redirectToRoute('contact');
            }
        }

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("home", $this->generateUrl("homepage"));
        $breadcrumbs->addItem('contactus');

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}