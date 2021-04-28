<?php

class cronjob extends Migration
{
    const FILENAME = 'public/plugins_packages/tu-braunschweig/OpenMensa/cronjobs/refresh_cache.php';

    public function description()
    {
        return 'adds a cronjob';
    }

    public function up()
    {
        $task_id = CronjobScheduler::registerTask(self::FILENAME, true);

        if ($task_id) {
            CronjobScheduler::schedulePeriodic($task_id, -30);
        }
    }

    public function down()
    {
        if ($task_id = CronjobTask::findByFilename(self::FILENAME)->task_id) {
            CronjobScheduler::unregisterTask($task_id);
        }
    }
}
