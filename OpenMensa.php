<?php

/**
 * Stud.IP OpenMensa
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *

 * @category    Stud.IP
 * @author      Sebastian Biller <s.biller@tu-braunschweig.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class OpenMensa extends StudIPPlugin implements SystemPlugin
{
    public function __construct()
    {
        parent::__construct();
        require_once $this->getPluginPath().'/models/OMModel.php';

        if (Navigation::hasItem('/admin/config') && $GLOBALS['perm']->have_perm('root')) {
            Navigation::addItem('/admin/config/openmensa', new Navigation('OpenMensa', PluginEngine::getURL($this, [], 'admin')));

            PageLayout::addScript($this->getpluginUrl() . '/assets/vendor/chosen/chosen.jquery.min.js');
            PageLayout::addStylesheet($this->getpluginUrl() . '/assets/vendor/chosen/chosen.min.css');

            PageLayout::addScript($this->getpluginUrl() . '/assets/vendor/leaflet/leaflet.js');
            PageLayout::addStylesheet($this->getpluginUrl() . '/assets/vendor/leaflet/leaflet.css');

            PageLayout::addScript($this->getpluginUrl() . '/assets/vendor/Leaflet.markercluster/dist/leaflet.markercluster.js');
            PageLayout::addStylesheet($this->getpluginUrl() . '/assets/vendor/Leaflet.markercluster/dist/MarkerCluster.css');
            PageLayout::addStylesheet($this->getpluginUrl() . '/assets/vendor/Leaflet.markercluster/dist/MarkerCluster.Default.css');
        }

        $OMModel = new OMModel;
        $this->canteens=$OMModel->getCanteens(true);
        if ($this->canteens) {
            $navigation=new Navigation('Mensa', PluginEngine::getURL($this, [], 'canteens'));
            $navigation->setImage(Icon::create('mensa', Icon::ROLE_NAVIGATION));
            Navigation::addItem('/canteens', $navigation);
        }
    }
}
