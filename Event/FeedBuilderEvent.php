<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 01/01/2018
 * Time: 22:43
 */

namespace FeedBuilderBundle\Event;


use Pimcore\Config\Config;
use Pimcore\Model\DataObject\Listing\Concrete;
use Symfony\Component\EventDispatcher\Event;

class FeedBuilderEvent extends Event
{
    const BEFORE_RUN = 'feedbuilder.before.run';
    const AFTER_SELECTION = 'feedbuilder.after.selection';
    const BEFORE_ROW = 'feedbuilder.before.row';
    const AFTER_ROW = 'feedbuilder.after.row';
    const AFTER_RUN = 'feedbuilder.after.run';

    /** @var Config */
    private $config = null;

    /** @var Concrete */
    private $listing;

    /** @var \Pimcore\Model\DataObject\Concrete */
    private $object;

    private $array;

    private $result;

    public function setConfig(Config $config) {
        $this->config = $config;
    }
    public function getConfig() {
        return $this->config;
    }
    public function setListing( $listing) {
        $this->listing = $listing;
    }
    public function getListing() {
        return $this->listing;
    }
    public function setObject(\Pimcore\Model\DataObject\Concrete $object) {
        $this->object = $object;
    }
    public function getObject() {
        return $this->object;
    }
    public function setArray($array) {
        $this->array = $array;
    }
    public function getArray() {
        return $this->array;
    }
    public function setResult($result) {
        $this->result = $result;
    }
    public function getResult() {
        return $this->result;
    }

}