<?php
namespace Package\Raxon\Task\Trait;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Query\QueryException;
use Entity\Task;
use Exception;
use Package\Raxon\Task\Module\Status;
use Raxon\App;
use Raxon\Doctrine\Module\Database;
use Raxon\Doctrine\Module\Entity;
use Raxon\Exception\ErrorException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Node\Module\Node;


trait Service {

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws ErrorException
     * @throws Exception
     * @throws ORMException
     */
    public function create($flags, $options): void
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
            $task->setStatus(Status::PENDING);
            $config = Database::config($object);
            $connection = $object->config('doctrine.environment.' . $options->connection . '.' . $options->environment);
            if($connection === null){
                $connection = $object->config('doctrine.environment.' . $options->connection . '.' . '*');
            }
            $connection->manager = Database::entity_manager($object, $config, $connection);
            $connection->manager->persist($task);
            $connection->manager->flush();
        }
    }

    /**
     * @throws QueryException
     * @throws ObjectException
     * @throws Exception
     */
    public function list($flags, $options): array
    {
        $object = $this->object();
        $config = Database::config($object);
        if(!property_exists($options, 'environment')){
            $options->environment = $object->config('framework.environment');
        }
        if(!property_exists($options, 'connection')){
            $options->connection = 'system';
        }
        $connection = $object->config('doctrine.environment.' . $options->connection . '.' . $options->environment);
        if($connection === null){
            $connection = $object->config('doctrine.environment.' . $options->connection . '.' . '*');
        }
        $connection->manager = Database::entity_manager($object, $config, $connection);
        $entity = 'Task';
        $node = new Node($object);
        $role = $node->role_system();
        $list = Entity::list($object,$connection->manager, $role, $entity, $options);
        return $list;
    }

    /**
     * @throws Exception
     */
    public function execute(object $flags, object $options): array
    {
        $object = $this->object();
        $config = Database::config($object);
        if(!property_exists($options, 'environment')){
            $options->environment = $object->config('framework.environment');
        }
        if(!property_exists($options, 'connection')){
            $options->connection = 'system';
        }
        $connection = $object->config('doctrine.environment.' . $options->connection . '.' . $options->environment);
        if($connection === null){
            $connection = $object->config('doctrine.environment.' . $options->connection . '.' . '*');
        }
        $connection->manager = Database::entity_manager($object, $config, $connection);
        $entity = 'Task';
        $node = new Node($object);
        $role = $node->role_system();
        $object->request('entity', $entity);
        $object->request('filter.status', Status::PENDING);
        $object->request('order.isCreated', 'ASC');

        $options->page = 2;


        $record = Entity::record($object,$connection->manager, $role, $options);
        ddd($record);
        $object->request('limit', 1);
        $list = Entity::list($object,$connection->manager, $role, $options);
        return $list;
    }
}

