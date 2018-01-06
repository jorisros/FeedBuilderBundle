<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 01/01/2018
 * Time: 22:52
 */

namespace FeedBuilderBundle\Service;

use FeedBuilderBundle\Event\FeedBuilderEvent;
use OutputDataConfigToolkitBundle\Service;
use Pimcore\Cache;
use Pimcore\Config;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FeedBuilderService
{
    private $dispatcher = null;
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    const LOCATION_FILE = 'feedbuilder.php';

    const TYPE_OBJECT = 1;
    const TYPE_EXPORT = 2;
    const TYPE_FEED = 3;


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
                if($feed->get('title') === $id) {
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

        $event = new FeedBuilderEvent();
        $event->setConfig($config);

        $config = $this->dispatcher->dispatch(FeedBuilderEvent::BEFORE_RUN, $event)->getConfig();

        if(!$result = Cache::load('feedbuilder-'.$config->get('title'))) {

            $class = $config->get('class');
            $listing = $class . '\Listing';

            $criteria = new $listing();
            $published = $config->get('published') === 'true' ? true : false;
            $criteria->setUnpublished($published);
            $event->setListing($criteria);

            $criteria = $this->dispatcher->dispatch(FeedBuilderEvent::AFTER_SELECTION, $event)->getListing();
            $objects = $criteria->load();

            $result = [];
            /** @var Concrete $object */
            foreach ($objects as $object) {
                $event->setObject($object);
                $object = $this->dispatcher->dispatch(FeedBuilderEvent::BEFORE_ROW, $event)->getObject();

                $specificationOutputChannel = Service::getOutputDataConfig($object, $config->get('channel'));

                $arrProperties = [];
                foreach ($specificationOutputChannel as $property) {

                    switch ($property->getLabeledValue($object)->def->fieldtype)
                    {
                        case 'href':
                            $name = $property->getLabeledValue($object)->def->name;

                            $hrefObject = $object->$name;
                            $arrData = [];

                            if($hrefObject) {
                                $outputChannelHref = Service::getOutputDataConfig($hrefObject, $config->get('channel'));

                                foreach ($outputChannelHref as $hrefProperty) {
                                    $arrData[$hrefProperty->getLabeledValue($hrefObject)->label] = $hrefProperty->getLabeledValue($hrefObject)->value;
                                }
                            }

                            $arrProperties[$property->getLabeledValue($object)->label] = $arrData;
                            break;
                        default:
                            $value = $property->getLabeledValue($object)->value;
                            $arrProperties[$property->getLabeledValue($object)->label] = $value;
                        break;
                    }

                }

                $event->setArray($arrProperties);
                $arr = $this->dispatcher->dispatch(FeedBuilderEvent::AFTER_ROW, $event)->getArray();
                $result[$config->get('root')][] = $arr;
            }
            $event->setResult($result);

            $result = $this->dispatcher->dispatch(FeedBuilderEvent::AFTER_RUN, $event)->getResult();

            Cache::save($result, 'feedbuilder-' . $config->get('title'), ['output'], 3600);
        }


        return $result;
    }
}
