<?php

namespace AppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleManager
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected $httpClient;
    protected $base_url;


    /**
     * ArticleManager constructor.
     * @param ContainerInterface $container
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     */
    public function __construct(ContainerInterface $container, ValidatorInterface $validator, EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->validator = $validator;
        $this->em = $em;
        $this->base_url =$requestStack->getCurrentRequest()->getSchemeAndHttpHost().$requestStack->getCurrentRequest()->getBaseUrl();
        $this->httpClient = new Client();
    }


    public function getRepository()
    {
        return $this->em->getRepository('AppBundle:Article');
    }

    /**
     * @return \AppBundle\Entity\Article[]|array
     */
    public function loadAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param $id
     * @return \AppBundle\Entity\Article|null|object
     */
    public function loadArticle($slug)
    {
        return $this->getRepository()->findOneBy(['slug'=>$slug]);

    }

    /**
     * @return JsonResponse
     */
    public function notFoundArticle()
    {
        return new JsonResponse(['msg' => 'Article not found'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param $articles
     * @return JsonResponse
     */
    public function successLoadArticle($articles)
    {
        return new JsonResponse(['msg' => 'success !', 'result' => json_decode($this->serialize($articles))], Response::HTTP_OK);

    }

    /**
     * @param $article
     */
    public function persistAndFlush($article)
    {
        $this->em->persist($article);
        $this->em->flush();
    }

    /**
     * @param $article
     */
    public function removeAndFlush($article)
    {
        $this->em->remove($article);
        $this->em->flush();
    }

    /**
     * @param $articles
     * @return mixed|string
     */
    public function serialize($articles)
    {
        return $this->container->get('jms_serializer')->serialize($articles, 'json');

    }

    /**
     * @param $data
     * @return array|\JMS\Serializer\scalar|mixed|object
     */
    public function deserialize($data)
    {
        return $this->container->get('jms_serializer')->deserialize($data, 'AppBundle\Entity\Article', 'json');

    }

    /**
     * Method to list all items
     * @return JsonResponse
     */
    public function listArticles()
    {
        $articles = $this->loadAll();
        if (empty($articles)) {
            return $this->notFoundArticle();
        }
        return $this->successLoadArticle($articles);
    }

    /**
     * Method to list one item
     * @param $slug
     * @return JsonResponse
     */
    public function detailsArticle($slug)
    {
        $articles = $this->loadArticle($slug);
        if (empty($articles)) {
            return $this->notFoundArticle();
        }
        return $this->successLoadArticle($articles);
    }

    /**
     * Method to create item
     * @param $request
     * @return JsonResponse
     */
    public function createArticle($request)
    {
        $data = $request->getContent();
        $article = $this->deserialize($data);
        $validate = $this->validateRequest($article);
        if (!empty($validate)) {
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST);
        }
        $this->persistAndFlush($article);
        return new JsonResponse(['msg' => 'Article created !','result' => json_decode($this->serialize($article))], Response::HTTP_CREATED);
    }

    /**
     * Method to delete item
     * @param $slug
     * @return JsonResponse
     */
    public function deletePost($slug)
    {
        $articles = $this->loadArticle($slug);
        if (empty($articles)) {
            return $this->notFoundArticle();
        }
        $this->removeAndFlush($articles);
        return new JsonResponse(['msg' => 'Article deleted !'], Response::HTTP_OK);
    }

    /**
     * @param $article
     * @return array|JsonResponse
     */
    public function validateRequest($article)
    {
        $errors = $this->validator->validate($article);
        $errorsResponse = array();
        foreach ($errors as $error) {
            $errorsResponse[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage()
            ];
        }
        $reponse = count($errors) ? ['message' => 'validation errors', 'errors' => $errorsResponse] : [];
        return $reponse;

    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function call($method, $uri, array $options = [])
    {

        $uri = ltrim($uri, '/');
        $response = $this->httpClient->request($method, $this->base_url .'/'. $uri, $options);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function createDeleteForm($slug)
    {
        return $this->container->get('form.factory')->createBuilder(FormType::class)
            ->setAction($this->container->get('router')->generate('details_article', array('slug' => $slug)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete', 'attr' => array('class' => 'btn btn-default')))
            ->getForm();
    }
}