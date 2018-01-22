<?php

namespace FeedBuilderBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class FeedBuilderBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/feedbuilder/js/pimcore/outputDataConfigToolkit/operator/Language.js',
            '/bundles/feedbuilder/js/pimcore/portlets/feedbuilder.js',
            '/bundles/feedbuilder/js/pimcore/startup.js',
            '/bundles/feedbuilder/js/pimcore/panel.js',
            '/bundles/feedbuilder/js/pimcore/item.js'
        ];
    }

    /**
     *
     * @return Installer|null|\Pimcore\Extension\Bundle\Installer\InstallerInterface|void
     */
    public function getInstaller()
    {
        return new Installer();
    }
}
