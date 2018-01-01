<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 01/01/2018
 * Time: 22:43
 */

namespace FeedBuilderBundle\Event;


use Symfony\Component\EventDispatcher\Event;

class FeedBuilderEvent extends Event
{
    const BEFORE_RUN = 'feedbuilder.before.run';
    const AFTER_SELECTION = 'feedbuilder.after.selection';
    const BEFORE_ROW = 'feedbuilder.before.row';
    const AFTER_ROW = 'feedbuilder.after.row';
    const AFTER_RUN = 'feedbuilder.after.run';
}