<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 01/01/2018
 * Time: 22:52
 */

namespace FeedBuilderBundle\Service;

use FeedBuilderBundle\Event\FeedBuilderEvent;
use Pimcore\Config;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Object;
use Symfony\Component\EventDispatcher\EventDispatcher;

class FeedBuilderService
{
    const LOCATION_FILE = 'feedbuilder.php';

    /**
     * Returns the config file for the feedbuilder, see the feedbuilder.example.php
     *
     * @return Config\Config
     * @throws \Exception
     */
    public static function getConfig()
    {
        $systemConfigFile = Config::locateConfigFile(self::LOCATION_FILE);

        if(!file_exists($systemConfigFile)){
            throw new \Exception("Config file not found");
        }

        return new Config\Config(include($systemConfigFile));
    }

    /**
     * Returns the profile by ID or name
     *
     * @param $id
     * @throws \Exception
     */
    public static function getConfigOfProfile($id) {
        $config = self::getConfig();

        if(is_integer($id)){
            return $config->get('feeds')[$id];
        }

        if(is_string($id)){
            foreach ($config->get('feeds') as $feed) {
                if($feed->get('channel') === $id) {
                    return $feed;
                }
            }
        }

        return null;
    }

    /**
     * Run the feedbuilder
     *
     * @param Config\Config $config
     */
    public function run(Config\Config $config) {
        $eventDispatcher = new EventDispatcher();

        $event = new FeedBuilderEvent();
        $event->setConfig($config);
        $config = $eventDispatcher->dispatch(FeedBuilderEvent::BEFORE_RUN, $event)->getConfig();

        $class = $config->get('class');
        $listing = $class.'\Listing';

        $criteria = new $listing();
        $criteria->setUnpublished(!$config->get('published'));
        $event->setListing($criteria);

        $criteria = $eventDispatcher->dispatch(FeedBuilderEvent::AFTER_SELECTION, $event)->getListing();
        $objects = $criteria->load();

        $result = [];
        /** @var Concrete $object */
        foreach ($objects as $object){
            $event->setObject($object);
            $object = $eventDispatcher->dispatch(FeedBuilderEvent::BEFORE_ROW, $event)->getObject();

            //@TODO Load the output configuration
            $event->setArray([]);
            $arr = $eventDispatcher->dispatch(FeedBuilderEvent::AFTER_ROW, $event)->getArray();
            $result[] = $arr;
        }
        $event->setResult($result);
        $eventDispatcher->dispatch(FeedBuilderEvent::AFTER_RUN, $event)->getResult();
    }
}
