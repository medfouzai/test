<?php

namespace AppBundle\Controller\Api;

use AppBundle\Services\ArticleManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


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
     * @ApiDoc(
     *     resource=true,
     *     description="Get the list of all articles",
     *     section="Articles",
     *     statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when not found"
     *   }
     * )
     * @Route("/articles",name="api_list_articles", methods={"GET"})
     */
    public function listArticles()
    {
        return $this->articleManager->listArticles();
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get one single article",
     *     requirements={
     *         {
     *             "name"="slug",
     *             "dataType"="string",
     *             "description"="The article unique identifier."
     *         }
     *     },
     *     section="Articles",
     *     statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when not found"
     *   }
     * )
     * @Route("/articles/{slug}",name="api_details_article", methods={"GET"})
     */
    public function detailsArticle($slug)
    {
        return $this->articleManager->detailsArticle($slug);
    }


    /**
     * @ApiDoc(
     *    description="Create a new article",
     *    section="Articles",
     *    statusCodes = {
     *        201 = "Returned when creation with success",
     *        400 = "Returned when posted data is invalid"
     *    },
     *    responseMap={
     *         201 = {"class"=Article::class},
     *
     *    },
     * )
     * @Route("/articles",name="api_create_article", methods={"POST"})
     */
    public function createArticle(Request $request, ArticleManager $articleManager)
    {
        return $this->articleManager->createArticle($request);
    }


    /**
     * @ApiDoc(
     *     description="Deletes an existing article",
     *     section="Articles",
     *     statusCodes={
     *         204="Returned when an existing article has been successfully deleted",
     *         404="Returned when article not found"
     *     }
     * )
     * @Route("/articles/{slug}",name="api_delete_article" , methods={"DELETE"})
     */
    public function deletePost($slug)
    {
        return $this->articleManager->deletePost($slug);
    }


}