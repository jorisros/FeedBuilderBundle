<?php

namespace FeedBuilderBundle\Controller;

use FeedBuilderBundle\Service\ExportProviderService;
use FeedBuilderBundle\Service\FeedBuilderService;
use OutputDataConfigToolkitBundle\Service;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Config\Config;
use Pimcore\File;
use Pimcore\Model\DataObject;
use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class AdminInterfaceController
 * @package FeedBuilderBundle\Controller
 * @Route("/admin/feedbuilder")
 */
class AdminInterfaceController extends AdminController
{

    /**
     * @Route("/tree")
     */
    public function treeAction(Request $request)
    {
        $data = [];

        $config = FeedBuilderService::getConfig();

        /** @var Configuration $feed */
        foreach ($config->get('feeds') as $id=>$feed)
        {
            $data[] =
                [
                    'id'=>$id,
                    'text'=>$feed->get('title'),
                    'configuration'=>$this->getConfiguration($feed)
                ];
        }

        return $this->json($data);
    }

    private function getConfiguration(Config $feed)
    {
        return [
            'channel'=>$feed->get('channel'),
            'ipaddress'=>$feed->get('ipaddress'),
            'path'=>$feed->get('path'),
            'published'=>$feed->get('published'),
            'class'=>$feed->get('class'),
            'root'=>$feed->get('root'),
            'type'=>$feed->get('type')
        ];
    }

    /**
     *
     * @Route("/get-classes")
     * @param Request $request
     */
    public function getClassesAction(Request $request) {
        $classesList = new DataObject\ClassDefinition\Listing();
        $classesList->setOrderKey('name');
        $classesList->setOrder('asc');
        $classes = $classesList->load();

        $data = [];

        foreach ($classes as $class) {
            $s = [
                "id"=>"\Pimcore\Model\DataObject\\".$class->getName(),
                "text"=>$class->getName()
            ];

            $data[] = $s;
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

        $config = FeedBuilderService::getConfig();

        $feed = $config->get('feeds')[$request->get('id')];

        $data = [
            'id'=>$request->get('id'),
            'text'=>$feed->get('title'),
            'configuration'=>$this->getConfiguration($feed)
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
     * @Route("/save")
     * @param Request $request
     */
    public function providerAction(Request $request)
    {
        $config = [
            'title'=>$request->get('title'),
            'channel'=>$request->get('channel'),
            'ipaddress'=>$request->get('ipaddress'),
            'path'=>$request->get('path'),
            'published'=>$request->get('published'),
            'class'=>$request->get('class'),
            'root'=>$request->get('root'),
            'type'=>$request->get('type')
        ];

        /** @var Config $current */
        $current = FeedBuilderService::getConfig();
        $arr = $current->toArray();
        $arr['feeds'][$request->get('id')] = $config;

        $configFile = \Pimcore\Config::locateConfigFile(FeedBuilderService::LOCATION_FILE);

        File::putPhpFile($configFile, to_php_data_file_format($arr));


       // ExportProviderService::getProviders('Provider');
        return $this->json(['success' => true]);
    }

    /**
     *
     * @Route("/add")
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $config = [
            'title'=>$request->get('title'),
            'channel'=>$request->get('channel'),
            'ipaddress'=>$request->get('ipaddress'),
            'path'=>$request->get('path'),
            'published'=>$request->get('published'),
            'class'=>$request->get('class'),
            'root'=>$request->get('root'),
            'type'=>$request->get('type')
        ];

        /** @var Config $current */
        $current = FeedBuilderService::getConfig();
        $arr = $current->toArray();
        $arr['feeds'][] = $config;

        $configFile = \Pimcore\Config::locateConfigFile(FeedBuilderService::LOCATION_FILE);

        File::putPhpFile($configFile, to_php_data_file_format($arr));


        // ExportProviderService::getProviders('Provider');
        return $this->json(['success' => true, 'id'=>end(array_keys($arr['feeds']))]);
    }

    /**
     *
     * @Route("/delete")
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function deleteAction(Request $request)
    {
        $current = FeedBuilderService::getConfig();
        $arr = $current->toArray();

        unset($arr['feeds'][$request->get('id')]);

        $configFile = \Pimcore\Config::locateConfigFile(FeedBuilderService::LOCATION_FILE);

        File::putPhpFile($configFile, to_php_data_file_format($arr));

        return $this->json(['success' => true]);
    }
}
