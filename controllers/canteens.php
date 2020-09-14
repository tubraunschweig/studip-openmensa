<?php

require_once $this->trails_root.'/models/OMModel.php';

class CanteensController extends StudipController
{
    public function before_filter(&$action, &$args)
    {
        // set default layout
        $this->templateFactory = $GLOBALS['template_factory'];
        $layout = $this->templateFactory->open('layouts/base');
        $this->set_layout($layout);

        $this->flash = Trails_Flash::instance();
    }

    public function index_action()
    {
        PageLayout::setTitle(_("Mensa"));
        Navigation::activateItem('/canteens');
        $OMModel = new OMModel;
        $this->canteens=$OMModel->getCanteens(true);
        $this->default_canteen=$OMModel->getDefaultCanteen();
        $this->overview=$OMModel->getOverview();
        $this->id=Request::get('id', null);
        $this->req_overview=Request::get('overview', false);
        $this->select_overview=false;
        $this->date=Request::get('date', false);
        $this->today_closed=false;
        $this->no_data=false;
        $this->today_closed_text='';
        $nav_head = Navigation::getItem("/canteens");

        if ($this->canteens) {
            if ($this->overview) {
                $nav_sub = new navigation(_('Übersicht').' '._('für').' '._('heute'), PluginEngine::getURL("openmensa/canteens"), ['overview'=>1]);
                Navigation::addItem('/canteens/overview', $nav_sub);
                $nav_head->addSubNavigation('overview', $nav_sub);
            }
            foreach ($this->canteens as $canteen) {
                $nav_sub = new navigation($canteen['info']->name, PluginEngine::getURL("openmensa/canteens"), ['id'=>$canteen['info']->id]);
                Navigation::addItem('/canteens/'.$canteen['info']->id, $nav_sub);
                $nav_head->addSubNavigation($canteen['info']->id, $nav_sub);
                if (!empty($canteen['days'])) {
                    foreach ($canteen['days'] as $day) {
                        if (!$day->closed) {
                            $nav_sub_day = new Navigation(date('l, j.F', strtotime($day->date)), PluginEngine::getURL("openmensa/canteens"), ['id'=>$canteen['info']->id,'date'=>$day->date]);
                            $nav_sub_item = Navigation::getItem('/canteens/'.$canteen['info']->id);
                            $nav_sub_item->addSubNavigation($day->date, $nav_sub_day);
                        }
                    }
                }
            }
        }
        if ($this->canteens && (($this->id==null && !$this->default_canteen) || $this->req_overview) && $this->overview) {
            $this->select_overview=true;
            $this->date=date('Y-m-d', strtotime('today midnight'));
            Navigation::activateItem('/canteens/overview');
        } elseif (!$this->id && $this->default_canteen) {
            $this->id=$this->default_canteen;
        } elseif (!$this->overview && !$this->default_canteen) {
            $this->id=array_slice($this->canteens, 0, 1)[0]['info']->id;
        }
        if (!$this->canteens && !$this->id) {
            $this->no_data=true;
        }

        if ($this->id && $this->date) {
            Navigation::activateItem('/canteens/'.$this->id.'/'.$this->date);
        } elseif ($this->id) {
            $today=date('Y-m-d', strtotime('today midnight'));
            if (array_key_exists($today, $this->canteens[$this->id]['meals'])) {
                $this->date=$today;
            } elseif (!empty($this->canteens[$this->id]['days'])) {
                $this->date=array_slice($this->canteens[$this->id]['days'], 0, 1)[0]->date;
            }
            if ($this->date) {
                Navigation::activateItem('/canteens/'.$this->id.'/'.$this->date);
            } else {
                Navigation::activateItem('/canteens/'.$this->id);
                $this->no_data=true;
            }
        }
        if ($this->id && $this->date && count($this->canteens[$this->id]['meals'][$this->date]) == 1) {
            foreach ($this->canteens[$this->id]['meals'][$this->date] as $meals) {
                if (strpos($meals->name, 'geschlossen') !== false) {
                    $this->today_closed=true;
                    $this->today_closed_text=$meals->name;
                    break;
                }
            }
        }
        if ($this->id && $this->date) {
            foreach ($this->canteens[$this->id]['days'] as $day) {
                if ($day->date==$this->date) {
                    if ($day->closed) {
                        $this->today_closed=true;
                    }
                    break;
                }
            }
        }
    }
}
