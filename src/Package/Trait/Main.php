<?php
namespace Package\Raxon\Task\Trait;

use Exception;
use Raxon\Module\Core;
use Raxon\Module\File;

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
        $command = Core::binary($object) . ' raxon/basic cron backup';
        exec($command . ' 2>&1', $output, $code);
        echo implode(PHP_EOL, $output) . PHP_EOL;

        $url = $object->config('project.dir.data') . 'Cron' . $object->config('ds') . 'Cron.development';
        if(File::exist($url)){
            $read = File::read($url);
            $read = explode(PHP_EOL, $read);
            $is_found = false;
            foreach($read as $nr => $line){
                $line = trim($line);
                if(stristr($line, 'raxon/task service execute') !== false){
                    $is_found = $nr;
                    break;
                }
            }
            if($is_found === false){
                $read[] = '*/1 * * * *   root    /usr/bin/app raxon/task service execute >> /dev/null 2>&1';
                File::write($url, implode(PHP_EOL, $read));
            }
            $command = Core::binary($object) . ' raxon/basic cron restore';
            exec($command . ' 2>&1', $output, $code);
            echo implode(PHP_EOL, $output) . PHP_EOL;
            $command = Core::binary($object) . ' raxon/basic cron restart';
            exec($command . ' 2>&1', $output, $code);
            echo implode(PHP_EOL, $output) . PHP_EOL;
        }
//
//        $command = '*/1 * * * *   root    /usr/bin/app raxon/task service execute >> /dev/null 2>&1'
    }

}

