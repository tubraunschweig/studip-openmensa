<?php

require_once $this->trails_root.'/models/OMModel.php';

class AdminController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
    }

    public function before_filter(&$action, &$args)
    {
        // Permission check
        if ($GLOBALS['user']->perms !== 'root') {
            throw new AccessDeniedException();
        }

        // set default layout
        $this->templateFactory = $GLOBALS['template_factory'];
        $layout = $this->templateFactory->open('layouts/base');
        $this->set_layout($layout);

        $this->flash = Trails_Flash::instance();
    }

    public function index_action()
    {
        PageLayout::setTitle(_("OpenMensa Administration"));
        Navigation::activateItem('/admin/config/openmensa');

        PageLayout::addScript($this->plugin->getAssetsUrl() . '/vendor/chosen/chosen.jquery.min.js');
        PageLayout::addStylesheet($this->plugin->getAssetsUrl() . '/vendor/chosen/chosen.min.css');

        PageLayout::addScript($this->plugin->getAssetsUrl() . '/vendor/leaflet/leaflet.js');
        PageLayout::addStylesheet($this->plugin->getAssetsUrl() . '/vendor/leaflet/leaflet.css');

        PageLayout::addScript($this->plugin->getAssetsUrl() . '/vendor/Leaflet.markercluster/dist/leaflet.markercluster.js');
        PageLayout::addStylesheet($this->plugin->getAssetsUrl() . '/vendor/Leaflet.markercluster/dist/MarkerCluster.css');
        PageLayout::addStylesheet($this->plugin->getAssetsUrl() . '/vendor/Leaflet.markercluster/dist/MarkerCluster.Default.css');

        $OMModel = new OMModel;
        $this->public_canteens=$OMModel->getPublicCanteens();
        $canteens=$OMModel->getCanteens(false);
        $this->overview=$OMModel->getOverview();
        $this->default_canteen=$OMModel->getDefaultCanteen();
        $this->canteens=($canteens ? $canteens : []);
    }

    public function update_action()
    {
        $canteens=Request::getArray('canteens');
        $default_canteen=intval(Request::get('default_canteen'));
        $overview=Request::get('overview');
        $OMModel = new OMModel;
        if ($canteens) {
            foreach ($canteens as $canteen) {
                $this->canteens[]=intval($canteen);
            }
            if (in_array($default_canteen, $this->canteens)) {
                $this->default_canteen=$default_canteen;
            } else {
                $this->default_canteen=false;
            }
            if ($overview=='on') {
                $this->overview=true;
            } else {
                $this->overview=false;
            }
            if (!$this->overview && !$this->default_canteen && !empty($this->canteens)) {
                $this->default_canteen=$this->canteens[0];
            }
            $OMModel->setCanteens(['canteens'=>$this->canteens,'default_canteen'=>$this->default_canteen,'overview'=>$this->overview]);
        } else {
            $this->overview=$OMModel->getOverview();
            $OMModel->setCanteens(['canteens'=>[],'default_canteen'=>false,'overview'=>$this->overview]);
        }
        $this->redirect(PluginEngine::getLink('openmensa/admin/index'));
    }

    public function clear_action()
    {
        $OMModel = new OMModel;
        $OMModel->expireCache();
        $this->redirect(PluginEngine::getLink('openmensa/admin/index'));
    }

    public function reset_action()
    {
        $OMModel = new OMModel;
        $OMModel->expireCache();
        $OMModel->setCanteens([]);
        $this->redirect(PluginEngine::getLink('openmensa/admin/index'));
    }
}
