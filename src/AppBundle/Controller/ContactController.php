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

use AppBundle\Entity\Contact;

// Include the recaptcha lib
use ReCaptcha\ReCaptcha;

class ContactController extends Controller
{
    /**
     * @Route("contact", name="contact")
     */
    public function indexAction(Request $request)
    {
        $contact = new Contact();
        
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, array('label' => 'Author'))
            ->add('email', EmailType::class, array('label' => 'Email'))
            ->add('phone', TextType::class, array('label' => 'Phone'))
            ->add('title', TextType::class, array('label' => 'Title'))
            ->add('contents', TextareaType::class, array('label' => 'Contents'))
            ->add('send', SubmitType::class, array('label' => 'Send'))
            ->getForm();

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {
            
            $recaptcha = new ReCaptcha($this->container->getParameter('g_recaptcha_secret'));
            $response = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());

            if ( !$response->isSuccess() ) {
                $this->addFlash(
                    'error',
                    'The reCAPTCHA was not entered correctly. Go back and try it again.'
                );

                return $this->render('contact/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            } else {
                $task = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $em->persist($contact);
                $em->flush();
                
                if( null == $contact->getId() ) {
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
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}