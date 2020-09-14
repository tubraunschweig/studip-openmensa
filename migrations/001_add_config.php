<?php

class AddConfig extends Migration
{
    public function up()
    {
        try {
            Config::get()->create('OM_canteens');
        } catch (InvalidArgumentException $ex) {
        }
    }

    public function down()
    {
        Config::get()->delete('OM_canteens');
    }
}
