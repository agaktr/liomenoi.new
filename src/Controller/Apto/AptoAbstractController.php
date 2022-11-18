<?php

namespace App\Controller\Apto;

use App\Entity\Apto\User;
use App\Interfaces\AppInterface;
use App\Service\AppService;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AptoAbstractController extends AbstractController implements AppInterface
{

    protected Client $cache;
    protected AppService $appService;
    protected FormInterface $filterForm;
    protected bool $isApi;
    protected string $environment;
    protected string $entityName;
    protected array $criteria;
    protected array $orderBy;
    protected int $currentPage;
    protected int $offset;


    /**
     * basic variables we use on all controllers
     * @param AppService $appService
     * @param Client $client
     * @param string $environment
     */
    public function __construct(AppService $appService,Client $client,string $environment)
    {

        //This allows us to change menu items
        //and get app constants
        $this->appService = $appService;

        //This allows us to cache data
        $this->cache = $client;

        //This checks if we are using the api
        $this->isApi = isset($_SERVER["HTTP_APTO_API"]);

        //This checks on what environment we are
        //so we can perform production tasks
        //like minify twig files, caching data, etc
        $this->environment = $environment;

        //This is the entity name we are using
        //so we can use it on the automation stuff
        //its based on the controller name
        //so check how you name your controllers
        $this->entityName = strtolower(
            str_replace(
                'Controller',
                '',
                substr(
                    get_class($this),
                    strrpos(
                        get_class($this),
                        '\\'
                    )+1
                )
            )
        );
    }

    protected function handleFilterForm(Request $request){

        $filterType = 'App\Form\\'.ucfirst($this->entityName).'FilterType';
        $form = $this->createForm(get_class(new $filterType));

        $form->handleRequest($request);

        $criteria = $form->getData() ?? [];
        $currentPage = $criteria['page'] ?? 1;
        $orderBy = [
            isset($criteria['sortBy']) && !empty($criteria['sortBy'])
                ? $criteria['sortBy']
                : 'id',
            isset($criteria['sort']) && !empty($criteria['sort'])
                ? $criteria['sort']
                : 'ASC'
        ];
        unset($criteria['sortBy'],$criteria['sort'],$criteria['page']);

        $offset = ($currentPage - 1) * self::PER_PAGE;

        $this->criteria = $criteria;
        $this->orderBy = $orderBy;
        $this->offset = $offset;
        $this->filterForm = $form;
    }

    /**
     * Just a reference to the user so we don't have polymorphic code
     * @return User
     */
    protected function getUser() :User
    {
        return parent::getUser();
    }

    /**
     * Renders a view with html compression.
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {

        //add $this->appService to all views
        $parameters['APP_SERVICE'] = $this->appService;

        if ($this->isApi){

            $encoders = [new JsonEncoder()];
            $defaultContext = [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return $object->getId();
                },
            ];
            $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];
            $serializer = new Serializer($normalizers, $encoders);

            $respArray = [];

            foreach ($parameters as $key => $value){

                var_dump($value);

                if (is_array($value) && is_object($value[0])){
                    $respArray[$key] = json_decode($serializer->serialize($parameters[$key],'json'));
                }else{
                    $respArray[$key] = $value;
                }
            }

            return new JsonResponse($respArray);
        }

        $content = $this->renderView($view, $parameters);

        if (null === $response) {
            $response = new Response();
        }

        //compress HTML if we are on production
        if ($this->environment === 'prod'){

            $content = preg_replace(array('/<!--(.*)-->/Uis',"/[[:blank:]]+/"),array('',' '),str_replace(array("\n","\r","\t"),'',$content));
        }

        $response->setContent($content);

        return $response;
    }
}
