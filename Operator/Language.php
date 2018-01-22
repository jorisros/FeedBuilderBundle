<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 21/01/2018
 * Time: 02:06
 */

//namespace FeedBuilderBundle\Operator;

namespace OutputDataConfigToolkitBundle\ConfigElement\Operator;

use OutputDataConfigToolkitBundle\ConfigElement\Operator\AbstractOperator;
use OutputDataConfigToolkitBundle\ConfigElement\Value\DefaultValue;
use OutputDataConfigToolkitBundle\Service;
use Pimcore\Model\DataObject\Product;

class Language extends AbstractOperator
{
    private $addon;

    private $channel;

    public function __construct($config, $context = null) {
        parent::__construct($config, $context);

        $this->addon = $config->addon;
    }

    public function setChannel($channel) {
        $this->channel = $channel;
    }
    public function getLabeledValue($object) {

        $e = new \stdClass();
        $e->label = $this->addon;
        $e->def = $object->o_class->fieldDefinitions['localizedfields'];
        $e->value = $object->localizedfields->items;
        return $e;
    }

    /**
     * @return mixed
     */
    public function getAddon()
    {
        return $this->addon;
    }

    /**
     * @param mixed $prefix
     */
    public function setAddon($addon)
    {
        $this->addon = $addon;
    }


}