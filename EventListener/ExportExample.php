<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 02/01/2018
 * Time: 20:47
 */

namespace FeedBuilderBundle\EventListener;

use FeedBuilderBundle\Event\FeedBuilderEvent;
use FeedBuilderBundle\FeedBuilderBundle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExportExample
{
    const FEED_TITLE = 'Testfeed';

    public function fileHandler(FeedBuilderEvent $event){

        if($event->getConfig()->get('title') === self::FEED_TITLE)
        {
            $arr['products'] = $event->getResult();

            $dir = PIMCORE_SYSTEM_TEMP_DIRECTORY.DIRECTORY_SEPARATOR.'export';

            $name = 'json_export_'.time().'.json';
            if(!file_exists($dir)){
                mkdir($dir);
            }
            
            file_put_contents($dir.DIRECTORY_SEPARATOR.$name,json_encode($arr, JSON_PRETTY_PRINT));
        }
    }
}