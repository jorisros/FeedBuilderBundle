<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 28/12/2017
 * Time: 20:58
 */

namespace FeedBuilderBundle\Provider;


use Pimcore\Model\Object;

class ProviderBase implements ProviderInterface
{
    protected $array = [];

    public function run(){

    }

    public function beforeRun(){

    }
    public function selectionObject($object = null){

    }
    public function beforeRow(){

    }
    public function row(Object $object){

    }
    public function afterRow(){

    }
    public function afterRun(){

    }
}