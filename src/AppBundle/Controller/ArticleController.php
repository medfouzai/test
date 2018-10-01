<?php

namespace AppBundle\Controller;

use AppBundle\Form\ArticleType;
use AppBundle\Services\ArticleManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @var ArticleManager
     */
    private $articleManager;

    /**
     * ArticleController constructor.
     * @param ArticleManager $articleManager
     */
    public function __construct(ArticleManager $articleManager)
    {
        $this->articleManager = $articleManager;
    }

    /**
     * @Route("/", name="list_articles", methods={"GET"})
     */
    public function listArticlesAction()
    {

        $articles = $this->articleManager->call('GET', '/articles');

        return $this->render('article/list_articles.html.twig', array(
            'articles' => $articles['result'],
        ));
    }

    /**
     * @Route("/article/{slug}", name="details_article", methods={"GET","DELETE"})
     */
    public function detailsArticleAction(Request $request, $slug)
    {
        if ($request->isMethod('GET')) {
            $articles = $this->articleManager->call('GET', '/articles/' . $slug);
            $deleteForm = $this->articleManager->createDeleteForm($slug);
            return $this->render('article/detail_article.html.twig', array(
                'article' => $articles['result'],
                'delete_form' => $deleteForm->createView(),
            ));
        } else {
            $this->articleManager->call('DELETE', '/articles/' . $slug);
            return $this->redirectToRoute('list_articles');
        }

    }

    /**
     * @Route("/cree", name="create_article")
     */
    public function createArticleAction(Request $request)
    {
        $form = $this->createForm(ArticleType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('article/new_article.html.twig', array(
                'form' => $form->createView(),
            ));
        }
        $data = $this->get('jms_serializer')->toArray($form->getData());
        $article = $this->articleManager->call('POST', '/articles', [
            'json' => $data
        ]);
        return $this->redirectToRoute('details_article', ['slug' => $article['result']['slug']]);
    }


}