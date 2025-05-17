<?php
namespace Package\Raxon\Task\Trait;

use Exception;
use Raxon\Module\Core;

trait Main {

    /**
     * @throws Exception
     */
    public function task_install(object $flags, object $options): void
    {
        Core::interactive();
        $object = $this->object();
        echo 'Install ' . $object->request('package') . '...' . PHP_EOL;
        $schema_url = $object->config('project.dir.package') . 'Raxon/Task/Schema/Task.json';
        if(property_exists($options, 'connection')){
            $schema_connection = $options->connection;
        } else {
            $schema_connection = $object->config('doctrine.environment.system.*.uuid');
        }
        $command = Core::binary($object) . ' raxon/doctrine schema import -url=' . $schema_url . ' -connection=' . $schema_connection;
        if(property_exists($options, 'patch')){
            $command .= ' -patch';
        }
        if(property_exists($options, 'force')){
            $command .= ' -force';
        }
        exec($command . ' 2>&1', $output, $code);
        echo implode(PHP_EOL, $output) . PHP_EOL;
    }

}

