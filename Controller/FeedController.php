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
use Pimcore\Model\DataObject\Product;
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
        $result = $feedbuilder->run($config, $request->get('ignoreCache', false));

        //@TODO Add check for ipaddress
        switch($request->get('_format')){
            case 'xml':
                return $this->XMLResponse($result, $config,$feedbuilder);
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

    private function XMLResponse($array, $config, FeedBuilderService $feedbuilder){

        $class = $config->get('class');
        $objectDefinition = new $class();

        $xml = new \SimpleXMLElement('<'.$config->get('root').'/>');

        $this->convertArrayXML($array[$config->get('root')],$xml, $objectDefinition,$feedbuilder);
        
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
    private function convertArrayXML($data, &$xml_data,$objectDefinition, FeedBuilderService $feedbuilder ){

        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
               // $key = 'item'.$key; //dealing with <0/>..<n/> issues
                $key = $objectDefinition->getClassName();
            }
            if( is_array($value) ) {
                if($feedbuilder->hasAttribute($key)){
                    $arr = $feedbuilder->getAttribute($key);
                    $subnode = $xml_data->addChild($arr['label']);
                    $subnode->addAttribute('code',$arr['code']);
                    $this->convertArrayXML($value, $subnode, $objectDefinition, $feedbuilder);
                }else {
                    $subnode = $xml_data->addChild($key);
                    $this->convertArrayXML($value, $subnode, $objectDefinition, $feedbuilder);
                }
            } else {
                //var_dump($key);
                /** @var Product $objectDefinition */

                $rating = $xml_data->addChild("$key",htmlspecialchars("$value"));

            }
        }

    }
    /**
     * Converts the array to a recursive html
     *
     * @param $data
     * @param $xml_data
     * @param $objectDefinition
     */
    private function convertArrayHTML($data,$objectDefinition ){
        $html = '<table border="1">';
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
               // $key = 'item'.$key; //dealing with <0/>..<n/> issues
                $key = $objectDefinition->getClassName();
            }
            if( is_array($value) ) {
                ///$subnode = $xml_data->addChild($key);
                $sub = $this->convertArrayHTML($value,$objectDefinition);
                $html .= '<tr><td>'.$key.'</td><td>'.$sub.'</td></tr>';
            } else {
                //$xml_data->addChild("$key",htmlspecialchars("$value"));
                $html .= '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
            }
        }
        $html .= '</table>';
        return $html;
    }

    private function HtmlResponse($array, Config $config){

        $class = $config->get('class');
        $objectDefinition = new $class();

        $title = $config->get('title');

        $body = '<!doctype html><meta charset=utf-8><title>'.$title.'</title><body>';
        $body .= '<h1>'.$title.'</h1>';

        $body .= $this->convertArrayHTML($array, $objectDefinition);

        $body .= '</body>';
        $response = new Response($body);

        return $response;
    }
}

