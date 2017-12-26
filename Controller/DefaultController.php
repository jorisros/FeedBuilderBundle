<?php

namespace FeedBuilderBundle\Controller;

use FeedBuilderBundle\Entity\Configuration;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AdminController
{
    /**
     * @Route("/tree")
     */
    public function treeAction(Request $request)
    {
        $data = [];

        $feeds = $this->getDoctrine()
            ->getRepository(Configuration::class)
            ->findAll();

        /** @var Configuration $feed */
        foreach ($feeds as $feed)
        {
            $data[] =
                [
                    'id'=>$feed->getId(),
                    'text'=>$feed->getTitle(),
                    'configuration'=>[
                        'channel'=>1
                    ]
                ];
        }

        return $this->json($data);
    }

    /**
     *
     * @Route("/get")
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function getAction(Request $request)
    {
        $data = [];

        /** @var Configuration $feed */
        $feed = $this->getDoctrine()
            ->getRepository(Configuration::class)
            ->find($request->get('id'));

        $data = [
            'id'=>$feed->getId(),
            'title'=>$feed->getTitle()
        ];
        return $this->json($data);
    }

    /**
     * @param Request $request
     */
    public function channelAction(Request $request)
    {

        
    }
}
