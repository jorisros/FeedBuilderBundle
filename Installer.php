<?php
/**
 * Created by PhpStorm.
 * User: jorisros
 * Date: 03/01/2018
 * Time: 02:33
 */

namespace FeedBuilderBundle;


use FeedBuilderBundle\Service\FeedBuilderService;

use Pimcore\Config;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;

class Installer extends AbstractInstaller
{
    public function needsReloadAfterInstall()
    {
        return true;
    }
    public function canBeInstalled()
    {
        $this->isInstalled();
        return true;
    }
    public function canBeUninstalled()
    {
        return true;
    }

    public function install()
    {
        if(!$this->isInstalled()) {
            copy(__DIR__ . "/Resources/config/pimcore/feedbuilder.example.php", PIMCORE_PRIVATE_VAR . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . FeedBuilderService::LOCATION_FILE);
        }
    }

    public function isInstalled()
    {
        $config = Config::locateConfigFile(FeedBuilderService::LOCATION_FILE);
        if(file_exists($config)){
            return true;
        }else{
            return false;
        }
    }

}