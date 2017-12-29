<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 28/12/2017
 * Time: 01:31
 */

namespace FeedBuilderBundle\Provider;


use Pimcore\Model\Object;

interface ProviderInterface
{
    public function run();
    public function beforeRun();
    public function selectionObject();
    public function beforeRow();
    public function row(Object $object);
    public function afterRow();
    public function afterRun();
}