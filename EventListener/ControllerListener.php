<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 19/02/2018
 * Time: 00:11
 */

namespace FeedBuilderBundle\EventListener;


use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListener
{
    public function onKernelController(FilterControllerEvent $event) {

        $controller = $event->getController();

        if(!is_array($controller))
        {
            // not a controller do nothing
            return;
        }

        $controllerObject = $controller[0];

        if(is_object($controllerObject) && method_exists($controllerObject,"preExecute") )
        {
            $controllerObject->preExecute();
        }
    }
}