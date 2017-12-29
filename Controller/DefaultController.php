<?php

namespace FeedBuilderBundle\Controller;

use FeedBuilderBundle\Service\ExportProviderService;
use OutputDataConfigToolkitBundle\Service;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AdminController
{
    const LOCATION_FILE = 'feedbuilder.php';
    /**
     * @Route("/tree")
     */
    public function treeAction(Request $request)
    {
        $data = [];


        $systemConfigFile = \Pimcore\Config::locateConfigFile(DefaultController::LOCATION_FILE);

        $config = new \Pimcore\Config\Config(include($systemConfigFile));

        /** @var Configuration $feed */
        foreach ($config->get('feeds') as $id=>$feed)
        {
            $data[] =
                [
                    'id'=>$id,
                    'text'=>$feed->get('title'),
                    'configuration'=>[
                        'channel'=>$feed->get('channel'),
                        'ipaddress'=>$feed->get('ipaddress'),
                        'path'=>$feed->get('path'),
                        'published'=>$feed->get('published'),
                        'class'=>$feed->get('class'),
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

        $systemConfigFile = \Pimcore\Config::locateConfigFile(DefaultController::LOCATION_FILE);

        $config = new \Pimcore\Config\Config(include($systemConfigFile));

        $feed = $config->get('feeds')[$request->get('id')];

        $data = [
            'id'=>$request->get('id'),
            'text'=>$feed->get('title'),
            'configuration'=>[
                'channel'=>$feed->get('channel'),
                'ipaddress'=>$feed->get('ipaddress'),
                'path'=>$feed->get('path'),
                'published'=>$feed->get('published'),
                'class'=>$feed->get('class'),
            ]
        ];

        return $this->json($data);
    }

    /**
     * @Route("/channel")
     * @param Request $request
     */
    public function channelAction(Request $request)
    {
        $channels = Service::getConfig();

        $data = [];

        foreach ($channels['channels'] as $channel) {
            $data[] =[
                'name'=>$channel,
                'abbr' => $channel
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/provider")
     * @param Request $request
     */
    public function providerAction(Request $request)
    {
        ExportProviderService::getProviders('Provider');
        die();
    }
}
