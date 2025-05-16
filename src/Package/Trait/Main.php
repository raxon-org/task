<?php
namespace Package\Raxon\Task\Trait;

use Package\Raxon\Account\Service\Jwt;
use Raxon\App;

use Raxon\Exception\ErrorException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\Database;
use Raxon\Module\File;

use Raxon\Node\Module\Node;
use Package\Raxon\Account\Service\User;
use Entity\Task;

use Exception;
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

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws ErrorException
     * @throws Exception
     */
    public function task_create($flags, $options): void
    {
        $object = $this->object();
        $user_uuid = false;
//        $host_uuid = false;
//        $channel_uuid = false;
        if(!property_exists($options, 'environment')){
            $options->environment = $object->config('framework.environment');
        }
        if(App::is_cli()){
            if(property_exists($options, 'user')){
                $class = 'Account.User';
                $node = new Node($object);
                $where_list = [];
                foreach($options->user as $property => $value){
                    $where = [
                        'value' => $value,
                        'attribute' => $property,
                        'operator' => '==='
                    ];
                    $where_list[] = $where;
                }
                $where_list[] = [
                    'value' => 1,
                    'attribute' => 'is.active',
                    'operator' => '>='
                ];
                $record = $node->record($class, $node->role_system(), ['where' => $where_list]);
                if(array_key_exists('node', $record)){
                    if(property_exists($record['node'], 'uuid')){
                        $user_uuid = $record['node']->uuid;
                    }
                }
            }
            /*
            if(property_exists($options, 'host')){
                $class = 'System.Host';
                $node = new Node($object);
                $where_list = [];
                foreach($options->host as $property => $value){
                    $where = [
                        'value' => $value,
                        'attribute' => $property,
                        'operator' => '==='
                    ];
                    $where_list[] = $where;
                }
                $record = $node->record($class, $node->role_system(), ['where' => $where_list]);
                if(array_key_exists('node', $record)){
                    if(property_exists($record['node'], 'uuid')){
                        $host_uuid = $record['node']->uuid;
                    }
                }
            }
            */
            /*
            if(property_exists($options, 'channel')){
                $class = 'System.Channel';
                $node = new Node($object);
                $where_list = [];
                foreach($options->channel as $property => $value){
                    $where = [
                        'value' => $value,
                        'attribute' => $property,
                        'operator' => '==='
                    ];
                    $where_list[] = $where;
                }
                $record = $node->record($class, $node->role_system(), ['where' => $where_list]);
                if(array_key_exists('node', $record)){
                    if(property_exists($record['node'], 'uuid')){
                        $channel_uuid = $record['node']->uuid;
                    }
                }
            }
            */

            $description = $options->description ?? 'Task created by CLI';
            $command =  $options->command ?? [];
            $controller = $options->controller ?? [];
            $task = new Task();
            $task->setUser($user_uuid);
            $task->setDescription($description);
            $task->setCommand($command);
            $task->setController($controller);
            $task->setStatus('Pending');
            $config = Database::config($object);
            $connection = $object->config('doctrine.environment.' . $options->connection . '.' . $options->environment);
            if($connection === null){
                $connection = $object->config('doctrine.environment.' . $options->connection . '.' . '*');
            }
            $em = Database::entity_manager($object, $config, $connection);
            $connection->manager->persist($task);
            $connection->manager->flush();
        }
    }

}

