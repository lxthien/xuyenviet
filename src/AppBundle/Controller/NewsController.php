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
use AppBundle\Entity\Page;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Tag;

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
        $breadcrumbs = $this->buildBreadcrums((!empty($subCategory) && $subCategory != null) ? $subCategory : $category, null, null);

        // Init pagination for category page.
        if (empty($level2)) {
            // Get all post for this category and sub category
            $catIds = array($category->getId());

            $allSubCategory = $this->getDoctrine()
                ->getRepository(NewsCategory::class)
                ->createQueryBuilder('c')
                ->where('c.parentcat = (:parentcat)')
                ->setParameter('parentcat', $category->getId())
                ->getQuery()->getResult();

            foreach ($allSubCategory as $value) {
                $catIds[] = $value->getId();
            }

            $news = $this->getDoctrine()
                ->getRepository(News::class)
                ->createQueryBuilder('p')
                ->where('p.category IN (:catIds)')
                ->setParameter('catIds', $catIds)
                ->getQuery()->getResult();
        } else {
            $news = $this->getDoctrine()
                ->getRepository(News::class)
                ->createQueryBuilder('p')
                ->where('p.category = :catId')
                ->setParameter('catId', $subCategory->getId())
                ->getQuery()->getResult();
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $news,
            $page,
            $this->get('settings_manager')->get('numberRecordOnPage') || 10
        );

        return $this->render('news/list.html.twig', [
            'category' => (!empty($subCategory) && $subCategory != null) ? $subCategory : $category,
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
            ->findBy(array('postType' => 'post'));

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
                'post'          => $post
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
            10
        );

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
                array('postType' => 'post'),
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
                array('postType' => 'post'),
                array('viewCounts' => 'DESC'),
                10
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
            ->findAll();

        return $this->render('news/listByCategory.html.twig', [
            'category' => $category,
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
        
        if ( !$form->isSubmitted() && empty($request->query->get('q')) ) {
            return $this->render('news/formSearch.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        $q = $form->getData()['q'];
        if( !empty($q) ) {
            return $this->redirectToRoute('news_search', array('q' => $q));
        }

        $query = $this->getDoctrine()
            ->getRepository(News::class)
            ->createQueryBuilder('a')
            ->where('a.title LIKE :q')
            ->setParameter('q', '%'.$request->query->get('q').'%')
            ->getQuery();
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query->getResult(),
            1,
            10
        );

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
            ->add('content', TextareaType::class)
            ->add('author', TextType::class)
            ->add('email', EmailType::class)
            ->add('ip', HiddenType::class)
            ->add('news_id', HiddenType::class)
            ->add('comment_id', HiddenType::class)
            ->add('send', SubmitType::class, array('label' => 'Send'))
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
                        'status'=>'fail',
                        'message' => 'You can access this only using Ajax!'
                    )
                )
            );
        } else {
            $em = $this->getDoctrine()->getManager();
            
            $comment = new Comment();
            $comment->setContent( $request->request->get('form')['content'] );
            $comment->setAuthor( $request->request->get('form')['author'] );
            $comment->setEmail( $request->request->get('form')['email'] );
            $comment->setNewsId( $request->request->get('form')['news_id'] );
            $comment->setCommentId( $request->request->get('form')['comment_id'] );
            $comment->setIp( $request->request->get('form')['ip'] );

            $em->persist($comment);
            $em->flush();
            
            if (null != $comment->getId()) {

                $mailLogger = new \Swift_Plugins_Loggers_ArrayLogger();
                $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($mailLogger));

                $message = (new \Swift_Message('Hello Email'))
                    ->setFrom('send@example.com')
                    ->setTo('lxthien@gmail.com')
                    ->setBody(
                        $this->renderView(
                            'Emails/comment.html.twig',
                            array('name' => $request->request->get('form')['author'])
                        ),
                        'text/html'
                    )
                ;
                
                if ($mailer->send($message)) {
                    //echo '[SWIFTMAILER] sent email to ' . $request->request->get('form')['email'];
                } else {
                    //echo '[SWIFTMAILER] not sending email: ' . $mailLogger->dump();
                }

                return new Response(
                    json_encode(
                        array(
                            'status'=>'success',
                            'message' => 'Thank for your comment. We will review your comment before display on this page'
                        )
                    )
                );
            } else {
                return new Response(
                    json_encode(
                        array(
                            'status'=>'fail',
                            'message' => 'Have a problem on your comment. Please try again'
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
        $breadcrumbs->addItem("Home", $this->generateUrl("homepage"));
        
        // Breadcrum for category page
        if( !empty($category) ) {
            if( $category->getParentcat() === 'root') {
                $breadcrumbs->addItem($category->getName(), $this->generateUrl("news_category", array('level1' => $category->getUrl() )));
            } else {
                $breadcrumbs->addItem($category->getParentcat()->getName(), $this->generateUrl("news_category", array('level1' => $category->getParentcat()->getUrl() )));
                $breadcrumbs->addItem($category->getName(), $this->generateUrl("list_category", array('level1' => $category->getParentcat()->getUrl(), 'level2' => $category->getUrl() )));
            }
        }

        // Breadcrum for post page
        if ( !empty($post) ) {
            $category = $post->getCategory();

            if ( !empty($category) ) {
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