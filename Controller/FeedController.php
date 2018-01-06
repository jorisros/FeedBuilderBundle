<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 02/01/2018
 * Time: 22:02
 */

namespace FeedBuilderBundle\Controller;


use FeedBuilderBundle\Service\FeedBuilderService;
use Pimcore\Config\Config;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/feedbuilder")
 */
class FeedController extends FrontendController
{
    /**
     *
     * @Route(
     *     "/{slug}.{_format}",
     *     defaults={"_format": "json"},
     *     requirements={
     *         "_format": "json|xml|html",
     *         "methods": "GET"
     *     }
     * )
     *
     * @param Response $response
     * @return JsonResponse
     */
    public function testAction(Request $request){

        $config = FeedBuilderService::getConfigOfProfile($request->get('slug'));

        if($config->get('type') != FeedBuilderService::TYPE_FEED){
            throw new NotFoundHttpException('Feed not found.');
        }

        $feedbuilder = new FeedBuilderService($this->get('event_dispatcher'));
        $result = $feedbuilder->run($config);

        //@TODO Add check for ipaddress
        switch($request->get('_format')){
            case 'xml':
                return $this->XMLResponse($result, $config);
                break;
            case 'json':
                return new JsonResponse($result);
                break;
            case 'html':
                return $this->HtmlResponse($result, $config);
                break;
        }

        throw new NotFoundHttpException('Sorry feed not found.');
    }

    private function XMLResponse($array, $config){

        $class = $config->get('class');
        $objectDefinition = new $class();

        $xml = new \SimpleXMLElement('<'.$config->get('root').'/>');

        $this->convertArrayXML($array[$config->get('root')],$xml, $objectDefinition);
        
        $body = $xml->asXML();

        $response = new Response($body);
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * Converts the array to a recursive xml
     * 
     * @param $data
     * @param $xml_data
     * @param $objectDefinition
     */
    private function convertArrayXML($data, &$xml_data,$objectDefinition ){

        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
               // $key = 'item'.$key; //dealing with <0/>..<n/> issues
                $key = $objectDefinition->getClassName();
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->convertArrayXML($value, $subnode,$objectDefinition);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }

    }

    private function HtmlResponse($array, Config $config){

        $title = $config->get('title');

        $body = '<!doctype html><meta charset=utf-8><title>'.$title.'</title><body>';
        $body .= '<h1>'.$title.'</h1>';
        foreach ($array as $row)
        {
            $body .= '<table border="1">';
            foreach ($row as $key => $item) {
                $body .= '<tr><td>' . $key . '</td><td>' . $item . '</td>';
            }
            $body .= '</table>';
        }
        $body .= '</table></body>';
        $response = new Response($body);

        return $response;
    }
}

