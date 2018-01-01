<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 01/01/2018
 * Time: 22:52
 */

namespace FeedBuilderBundle\Service;

use Pimcore\Config;

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

    private function getSelection(){

    }
}