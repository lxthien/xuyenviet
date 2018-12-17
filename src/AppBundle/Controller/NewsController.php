<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Entity\NewsCategory;
use AppBundle\Entity\News;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Tag;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

class NewsController extends Controller
{
    /**
     * Render the list posts by the category
     * 
     * @return News
     */
    public function listAction($level1, $level2 = null, $page = 1)
    {
        $category = $this->getDoctrine()
            ->getRepository(NewsCategory::class)
            ->findOneBy(array('url' => $level1, 'enable' => 1));

        if (!$category) {
            throw $this->createNotFoundException("The item does not exist");
        }

        if (!empty($level2)) {
            $subCategory = $this->getDoctrine()
                ->getRepository(NewsCategory::class)
                ->findOneBy(array('url' => $level2, 'enable' => 1));

            if (!$subCategory) {
                throw $this->createNotFoundException("The item does not exist");
            }
        }

        // Init breadcrum for category page
        $breadcrumbs = $this->buildBreadcrums(!empty($level2) ? $subCategory : $category, null, null);

        if (empty($level2)) {
            // Get all post for this category and sub category
            $listCategoriesIds = array($category->getId());

            $allSubCategories = $this->getDoctrine()
                ->getRepository(NewsCategory::class)
                ->createQueryBuilder('c')
                ->where('c.parentcat = (:parentcat)')
                ->setParameter('parentcat', $category->getId())
                ->getQuery()->getResult();

            foreach ($allSubCategories as $value) {
                $listCategoriesIds[] = $value->getId();
            }

            $news = $this->getDoctrine()
                ->getRepository(News::class)
                ->createQueryBuilder('p')
                ->where('p.category IN (:listCategoriesIds)')
                ->andWhere('p.enable = :enable')
                ->setParameter('listCategoriesIds', $listCategoriesIds)
                ->setParameter('enable', 1)
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery()->getResult();
        } else {
            $news = $this->getDoctrine()
                ->getRepository(News::class)
                ->createQueryBuilder('p')
                ->where('p.category = :category')
                ->andWhere('p.enable = :enable')
                ->setParameter('category', $subCategory->getId())
                ->setParameter('enable', 1)
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery()->getResult();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $news,
            $page,
            $this->get('settings_manager')->get('numberRecordOnPage') ?: 10
        );

        return $this->render('news/list.html.twig', [
            'category' => !empty($level2) ? $subCategory : $category,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("{slug}.html",
     *      defaults={"_format"="html"},
     *      name="news_show",
     *      requirements={
     *          "slug": "[^/\.]++"
     *      })
     */
    public function showAction($slug)
    {
        $post = $this->getDoctrine()
            ->getRepository(News::class)
            ->findOneBy(
                array('url' => $slug, 'enable' => 1)
            );

        if (!$post) {
            throw $this->createNotFoundException("The item does not exist");
        }

        // Update viewCount for post
        $post->setViewCounts( $post->getViewCounts() + 1 );
        $this->getDoctrine()->getManager()->flush();

        // Get news related
        $relatedNews = $this->getDoctrine()
            ->getRepository(News::class)
            ->createQueryBuilder('r')
            ->where('r.id <> :id')
            ->andWhere('r.postType = :postType')
            ->andWhere('r.category = :category')
            ->andWhere('r.enable = :enable')
            ->setParameter('id', $post->getId())
            ->setParameter('postType', $post->getPostType())
            ->setParameter('category', $post->getCategory())
            ->setParameter('enable', 1)
            ->setMaxResults( 6 )
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Get the list comment for post
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->createQueryBuilder('c')
            ->where('c.news_id = :news_id')
            ->andWhere('c.approved = :approved')
            ->setParameter('news_id', $post->getId())
            ->setParameter('approved', 1)
            ->getQuery()->getResult();

        // Render form comment for post.
        $form = $this->renderFormComment($post);

        // Init breadcrum for the post
        $breadcrumbs = $this->buildBreadcrums(null, $post, null);

        if ($post->isPage()) {
            return $this->render('news/page.html.twig', [
                'post'          => $post,
                'form'          => $form->createView(),
                'comments'      => $comments
            ]);
        } else {
            return $this->render('news/show.html.twig', [
                'post'          => $post,
                'relatedNews'   => $relatedNews,
                'form'          => $form->createView(),
                'comments'      => $comments,
            ]);
        }
    }

    /**
     * @Route("/tags/{slug}.html",
     *      defaults={"_format"="html"},
     *      name="tags",
     *      requirements={
     *          "slug": "[^\n]+"
     *      }))
     */
    public function tagAction($slug, Request $request)
    {
        $tag = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->findOneBy(
                array('url' => $slug)
            );

        // Get the list post related to tag
        $posts = $this->getDoctrine()
            ->getRepository(News::class)
            ->createQueryBuilder('n')
            ->innerJoin('n.tags', 't')
            ->where('t.id = :tags_id')
            ->setParameter('tags_id', $tag->getId())
            ->getQuery()->getResult();

        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $posts,
            !empty($request->query->get('page')) ? $request->query->get('page') : 1,
            $this->get('settings_manager')->get('numberRecordOnPage') ?: 10
        );

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("home", $this->generateUrl("homepage"));
        $breadcrumbs->addItem('post.tags');
        $breadcrumbs->addItem($tag->getName());

        return $this->render('news/tags.html.twig', [
            'tag' => $tag,
            'pagination' => $pagination
        ]);
    }

    /**
     * Render list recent news
     * @return News
     */
    public function recentNewsAction()
    {
        $posts = $this->getDoctrine()
            ->getRepository(News::class)
            ->findBy(
                array('postType' => 'post', 'enable' => 1),
                array('createdAt' => 'DESC'),
                10
            );

        return $this->render('news/recent.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * Render list hot news
     * @return News
     */
    public function hotNewsAction()
    {
        $posts = $this->getDoctrine()
            ->getRepository(News::class)
            ->findBy(
                array('postType' => 'post', 'enable' => 1),
                array('viewCounts' => 'DESC'),
                15
            );

        return $this->render('news/hot.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * Render list news by category
     * @return News
     */
    public function listNewsByCategoryAction($categoryId)
    {
        $category = $this->getDoctrine()
            ->getRepository(NewsCategory::class)
            ->find($categoryId);

        $posts = $this->getDoctrine()
            ->getRepository(News::class)
            ->createQueryBuilder('r')
            ->where('r.category = :category')
            ->andWhere('r.enable = :enable')
            ->setParameter('category', $categoryId)
            ->setParameter('enable', 1)
            ->setMaxResults( 10 )
            ->orderBy('r.viewCounts', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('news/listByCategory.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/search", name="news_search")
     * 
     * @return News
     */
    public function handleSearchFormAction(Request $request)
    {
        $form = $this->createFormBuilder(null, array(
                'csrf_protection' => false,
            ))
            ->setAction($this->generateUrl('news_search'))
            ->setMethod('POST')
            ->add('q', TextType::class)
            ->add('search', ButtonType::class, array('label' => 'Search'))
            ->getForm();

        $form->handleRequest($request);
        
        if (!$form->isSubmitted() && empty($request->query->get('q'))) {
            return $this->render('news/formSearch.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $q = $form->getData()['q'];
        if (!empty($q)) {
            return $this->redirectToRoute('news_search', array('q' => $q));
        }

        $query = $this->getDoctrine()
            ->getRepository(News::class)
            ->createQueryBuilder('p')
            ->where('p.title LIKE :q')
            ->andWhere('p.enable = :enable')
            ->andWhere('p.postType = :postType')
            ->setParameter('q', '%'.$request->query->get('q').'%')
            ->setParameter('enable', 1)
            ->setParameter('postType', 'post')
            ->getQuery();
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query->getResult(),
            1,
            $this->get('settings_manager')->get('numberRecordOnPage') ?: 10
        );

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("home", $this->generateUrl("homepage"));
        $breadcrumbs->addItem('search');
        $breadcrumbs->addItem(ucfirst($request->query->get('q')));

        return $this->render('news/search.html.twig', [
            'q' => ucfirst($request->query->get('q')),
            'pagination' => $pagination
        ]);
    }

    /**
     * Render the form comment of news
     * 
     * @return Form
     **/
    private function renderFormComment($post)
    {
        $comment = new Comment();
        $comment->setIp( $this->container->get('request_stack')->getCurrentRequest()->getClientIp() );
        $comment->setNewsId( $post->getId() );

        $form = $this->createFormBuilder($comment)
            ->setAction($this->generateUrl('handle_comment_form'))
            ->add('content', TextareaType::class, array(
                'required' => true,
                'label' => 'label.content',
                'attr' => array('rows' => '7')
            ))
            ->add('author', TextType::class, array('label' => 'label.author'))
            ->add('email', EmailType::class, array('label' => 'label.author_email'))
            ->add('recaptcha', EWZRecaptchaType::class)
            ->add('ip', HiddenType::class)
            ->add('news_id', HiddenType::class)
            ->add('comment_id', HiddenType::class)
            ->add('send', ButtonType::class, array('label' => 'label.send'))
            ->getForm();

        return $form;
    }

    /**
     * Handle form comment for post
     * 
     * @return JSON
     **/
    public function handleCommentFormAction(Request $request, \Swift_Mailer $mailer)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response(
                json_encode(
                    array(
                        'status'=>'error',
                        'message' => 'You can access this only using Ajax!'
                    )
                )
            );
        } else {
            $comment = new Comment();
            
            $form = $this->createFormBuilder($comment)
                ->add('content', TextareaType::class)
                ->add('author', TextType::class)
                ->add('email', EmailType::class)
                ->add('recaptcha', EWZRecaptchaType::class, array(
                    'mapped'      => false,
                    'constraints' => array(
                        new RecaptchaTrue()
                    )
                ))
                ->add('ip', HiddenType::class)
                ->add('news_id', HiddenType::class)
                ->add('comment_id', HiddenType::class)
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();

                if (null !== $comment->getId()) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject($this->get('translator')->trans('comment.email.title', ['%siteName%' => $this->get('settings_manager')->get('siteName')]))
                        ->setFrom(['hotro.xaydungminhduy@gmail.com' => $this->get('settings_manager')->get('siteName')])
                        ->setTo($this->get('settings_manager')->get('emailContact'))
                        ->setBody(
                            $this->renderView(
                                'Emails/comment.html.twig',
                                array(
                                    'name' => $request->request->get('form')['author'],
                                    'body' => $request->request->get('form')['content']
                                )
                            ),
                            'text/html'
                        )
                    ;

                    $mailer->send($message);
    
                    return new Response(
                        json_encode(
                            array(
                                'status'=>'success',
                                'message' => '<div class="alert alert-success" role="alert">'.$this->get('translator')->trans('comment.thank_for_your_comment').'</div>'
                            )
                        )
                    );
                } else {
                    return new Response(
                        json_encode(
                            array(
                                'status'=>'error',
                                'message' => '<div class="alert alert-warning" role="alert">'.$this->get('translator')->trans('comment.have_a_problem_on_your_request').'</div>'
                            )
                        )
                    );
                }
            } else {
                return new Response(
                    json_encode(
                        array(
                            'status'=>'error',
                            'message' => '<div class="alert alert-warning" role="alert">'.$this->get('translator')->trans('comment.have_a_problem_on_your_request').'</div>'
                        )
                    )
                );
            }
        }
    }

    /**
     * Handle the breadcrumb
     * 
     * @return Breadcrums
     **/
    public function buildBreadcrums($category = null, $post = null, $page = null)
    {
        // Init october breadcrum
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        
        // Add home item into first breadcrum.
        $breadcrumbs->addItem("home", $this->generateUrl("homepage"));
        
        // Breadcrum for category page
        if (!empty($category)) {
            if ($category->getParentcat() === 'root') {
                $breadcrumbs->addItem($category->getName(), $this->generateUrl("news_category", array('level1' => $category->getUrl() )));
            } else {
                $breadcrumbs->addItem($category->getParentcat()->getName(), $this->generateUrl("news_category", array('level1' => $category->getParentcat()->getUrl() )));
                $breadcrumbs->addItem($category->getName(), $this->generateUrl("list_category", array('level1' => $category->getParentcat()->getUrl(), 'level2' => $category->getUrl() )));
            }
        }

        // Breadcrum for post page
        if (!empty($post)) {
            $category = $post->getCategory();

            if (!empty($category)) {
                if ($category->getParentcat() === 'root') {
                    $breadcrumbs->addItem($category->getName(), $this->generateUrl("news_category", array('level1' => $category->getUrl() )));
                    $breadcrumbs->addItem($post->getTitle());
                } else {
                    $parentCategory = $category->getParentcat();
                    $breadcrumbs->addItem($parentCategory->getName(), $this->generateUrl("news_category", array('level1' => $parentCategory->getUrl() )));
                    $breadcrumbs->addItem($category->getName(), $this->generateUrl("list_category", array('level1' => $parentCategory->getUrl(), 'level2' => $category->getUrl() )));
                    $breadcrumbs->addItem($post->getTitle());
                }
            } else {
                $breadcrumbs->addItem($post->getTitle());
            }
        }

        return $breadcrumbs;
    }
}