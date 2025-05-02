<?php
namespace Package\Raxon\Task\Trait;

use Package\Raxon\Account\Service\Jwt;
use Raxon\App;

use Raxon\Exception\ErrorException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Node\Module\Node;
use Package\Raxon\Account\Service\User;

use Exception;
trait Main {

    /**
     * @throws Exception
     */
    public function task_install(): void
    {
        Core::interactive();
        $object = $this->object();
        echo 'Install ' . $object->request('package') . '...' . PHP_EOL;
        $schema_url = $object->config('project.dir.package') . 'Raxon/Task/Schema/Task.json';
        $schema_connection = $object->config('doctrine.environment.system.*.uuid');
        ddd($schema_connection);
        $command = Core::binary($object) . ' raxon/doctrine schema import -url=' . $schema_url;
        echo $command . PHP_EOL;
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws ErrorException
     */
    public function task_create($flags, $options): void
    {
        $object = $this->object();
        $user_uuid = false;
        $host_uuid = false;
        $channel_uuid = false;
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
            d('test');
            echo 'node' . PHP_EOL;
            d($channel_uuid);
            d($user_uuid);
            d($host_uuid);
            d($options);
            breakpoint('test2');
        }
    }

}

