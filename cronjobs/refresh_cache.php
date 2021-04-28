<?php

require_once __DIR__.'/../models/OMModel.php';

class RefreshCache extends CronJob
{
    public static function getName()
    {
        return _('OpenMensa - "refresh Cache"');
    }

    public static function getDescription()
    {
        return _('OpenMensa: Aktualisiert den Mensa Cache');
    }

    public function execute($last_result, $parameters = [])
    {
        $OMModel = new OMModel;
        $OMModel->forceUpdateCanteens();
        return true;
    }
}
