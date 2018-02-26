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
use Pimcore\Model\AbstractModel;
use Pimcore\Model\DataObject\Classificationstore;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FeedBuilderService
{
    private $dispatcher = null;

    private $key_attributes = [];

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
    public function run(Config\Config $config, $ignoreCache = false) {

        $event = new FeedBuilderEvent();
        $event->setConfig($config);

        $config = $this->dispatcher->dispatch(FeedBuilderEvent::BEFORE_RUN, $event)->getConfig();

        if($ignoreCache)
        {
            Cache::clearTag('output');
        }
        
        if(!$result = Cache::load('feedbuilder-'.$config->get('title'))) {

            $class = $config->get('class');
            $listing = $class . '\Listing';

            $criteria = new $listing();

            switch ($class){
                case 'Pimcore\Model\DataObject\Classificationstore\KeyConfig':
                    $result = $this->keyConfigFlow($criteria, $config, $event);
                    break;
                default:
                    $result = $this->objectFlow($criteria, $config, $event);
            }

            $event->setResult($result);

            $result = $this->dispatcher->dispatch(FeedBuilderEvent::AFTER_RUN, $event)->getResult();

            Cache::save($result, 'feedbuilder-' . $config->get('title'), ['output'], 3600);

        }

        return $result;
    }

    /**
     * Flow for attributes of classification store
     *
     * @param AbstractModel $criteria
     * @param Config\Config $config
     * @param FeedBuilderEvent $event
     * @return array
     */
    private function keyConfigFlow(AbstractModel $criteria, Config\Config $config, FeedBuilderEvent $event) {
        $objects = $criteria->load();

        $event->setListing($criteria);

        $criteria = $this->dispatcher->dispatch(FeedBuilderEvent::AFTER_SELECTION, $event)->getListing();
        $objects = $criteria->load();

        $result = [];
        /** @var Concrete $object */
        foreach ($objects as $object) {
            $arrProperties = [];
            foreach (get_object_vars($object) as $key=>$value){
                switch ($key){
                    case 'creationDate':
                    case 'modificationDate':
                        $arrProperties[$key] = date('Y-m-d\TH:i:sO', $value);
                        break;
                    case 'definition':
                        $arrProperties[$key] = json_decode($value);
                        break;
                    default:
                        $arrProperties[$key] = $value;
                        break;
                }
            }
            $event->setArray($arrProperties);
            $arr = $this->dispatcher->dispatch(FeedBuilderEvent::AFTER_ROW, $event)->getArray();

            $result[] = $arr;
        }

        if($config->get('root'))
        {
            $r[$config->get('root')] = $result;
            return $r;
        }else{
            return $result;
        }


    }

    /**
     * Flow for objects
     *
     * @param AbstractModel $criteria
     * @param FeedBuilderEvent $event
     * @return array
     * @throws \Exception
     */
    private function objectFlow(AbstractModel $criteria, Config\Config $config, FeedBuilderEvent $event) {
        $published = $config->get('published') === 'true' ? true : false;
        $criteria->setUnpublished($published);

        if(strlen($config->get('path')) > 0 && $config->get('path') != "/") {
            $criteria->setCondition("o_path LIKE ?", [$config->get('path')."%"]);
        }
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
                    case 'classificationstore':
                        /** @var Classificationstore $value */
                        $value = $property->getLabeledValue($object)->value;
                        // var_dump($value-);
                        //var_dump($value->getItems());
                        $arrProperties[$property->getLabeledValue($object)->label] = $value;
                        break;
                    case 'localizedfields':
                        $property->setChannel($config->get('channel'));
                        $values = $property->getLabeledValue($object)->value;

                        foreach ($values as $key=>$value)
                        {
                            $this->setAttribute($key, ['label'=>'language','code'=>$key]);
                        }
                        $arrProperties[$property->getLabeledValue($object)->label] = $values;
                        break;
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

        return $result;
    }

    public function hasAttribute($key) {
        $exceptions = $this->getAttributes();

        return array_key_exists($key, $exceptions);
    }

    public function getAttribute($key) {
        if($this->hasAttribute($key)){
            return $this->key_attributes[$key];
        }

        return $key;
    }

    public function getAttributes() {
        return $this->key_attributes;
    }

    private function setAttribute($key, $value) {
        if($this->hasAttribute($key))
        {
            $this->key_attributes[$key] = array_merge($this->getAttribute($key),$value);
        }else {
            $this->key_attributes[$key] = $value;
        }
    }


}
