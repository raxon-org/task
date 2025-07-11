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
use Raxon\Exception\AuthorizationException;
use Raxon\Exception\ErrorException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;
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
        $config = Database::config($object);            
        $connection = $object->config('doctrine.environment.' . $options->connection . '.' . $options->environment);
        if($connection === null){
            $connection = $object->config('doctrine.environment.' . $options->connection . '.' . '*');
        }
        $connection->manager = Database::entity_manager($object, $config, $connection);
        if(App::is_cli()){
            if(property_exists($options, 'user')){
                $repository = $connection->manager->getRepository('\\Entity\\User');
                foreach($options->user as $property => $value){
                    break;
                }
                $record = $repository->findOneBy([
                    $property => $value                    
                ]);
                if(empty($record->getIsActive())){                                        
                    throw new AuthorizationException('Account is not active.');
                }
                if(!empty($record->getIsDeleted())){                    
                    throw new AuthorizationException('Account is deleted.');
                }
                if(empty($record->getRole())){                    
                    throw new AuthorizationException('Account has no roles.');
                }
                $user_uuid = $record->getUuid();                
                /* through node
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
                if(is_array($record) && array_key_exists('node', $record)){
                    if(property_exists($record['node'], 'uuid')){
                        $user_uuid = $record['node']->uuid;
                    }
                }
                */
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
        $object->request('entity', $entity);
        $list = Entity::list($object,$connection->manager, $role, $options);
        return $list;
    }

    /**
     * @throws Exception
     * @throws ORMException
     */
    public function execute(object $flags, object $options): void
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
        $time_start = time();
        while(true){
            $is_busy = false;
            $record = Entity::record($object,$connection->manager, $role, $options);
            if(array_key_exists('node', $record)){
                if(
                    $record['node'] !== null &&
                    array_key_exists('id', $record['node'])
                ){
                    $patch = [
                        'id' => $record['node']['id'],
                        'status' => Status::IN_PROGRESS,
                    ];
                    //status IN_PROGRESS after 120 mins it should be set to ERROR
                    $response = Entity::patch($object, $connection, $role, (object) $patch, $error);
                    $is_busy = true;
                }
            }
            if($is_busy === false){
                sleep(5);
            } else {
                $dir_package = $object->config('ramdisk.url') .
                    '0' .
                    $object->config('ds') .
                    'Package' .
                    $object->config('ds') .
                    'Raxon' .
                    $object->config('ds') .
                    'Task' .
                    $object->config('ds')
                ;
                $dir_stdout = $dir_package .
                    'stdout' .
                    $object->config('ds')
                ;
                $dir_stderr = $dir_package .
                    'stderr' .
                    $object->config('ds')
                ;
                Dir::create($dir_stdout, Dir::CHMOD);
                Dir::create($dir_stderr, Dir::CHMOD);
                $process_list = [];
                if(array_key_exists('node', $record)){
                    if(
                        $record['node'] !== null &&
                        array_key_exists('command', $record['node'])
                    ){
                        $url_stdout = $dir_stdout . $record['node']['uuid'];
                        $url_stderr = $dir_stderr . $record['node']['uuid'];
                        foreach($record['node']['command'] as $nr => $command){
                            $command = 'nohup '. $command . ' >> ' . $url_stdout . ' 2>> ' . $url_stderr . ' &  echo $!';
                            exec($command, $output, $code);
                            $proc_id = trim($output[0]);
                            $process_list[] = $proc_id;
                        }
                        $command = 'nohup ' . Core::binary($object) . ' raxon/task service monitor -task.uuid=' . $record['node']['uuid'];
                        foreach($process_list as $proc_id){
                            $command .= ' -process[]=' . $proc_id;
                        }
                        $command .= ' -connection=' . $options->connection;
                        $command .= ' -environment=' . $options->environment;
                        exec($command, $output, $code);
                    }
                }
            }
            $time_current = time();
            if($time_current - $time_start > 60){ // 1 minute time-out
                //timeout cron every minute
                break;
            }
        }
    }

    /**
     * @throws QueryException
     * @throws ObjectException
     * @throws Exception
     * @throws ORMException
     */
    public function monitor(object $flags, object $options): void
    {
        $object = $this->object();
        $config = Database::config($object);
        if(!property_exists($options, 'environment')){
            throw new Exception('Environment not found');
        }
        if(!property_exists($options, 'connection')){
            throw new Exception('Connection not found');
        }
        if(!property_exists($options, 'task')){
            throw new Exception('Task UUID not found');
        }
        if(!property_exists($options->task, 'uuid')){
            throw new Exception('Task UUID not found');
        }
        if(!property_exists($options, 'process')){
            throw new Exception('Process ID not found');
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
        $object->request('filter.uuid', $options->task->uuid);
//        $object->request('page', 2); //test
        $record = Entity::record($object,$connection->manager, $role, $options);
        $dir_package = $object->config('ramdisk.url') .
            '0' .
            $object->config('ds') .
            'Package' .
            $object->config('ds') .
            'Raxon' .
            $object->config('ds') .
            'Task' .
            $object->config('ds')
        ;
        $dir_stdout = $dir_package .
            'stdout' .
            $object->config('ds')
        ;

        $dir_stderr = $dir_package .
            'stderr' .
            $object->config('ds')
        ;
        $url_stdout = $dir_stdout . $record['node']['uuid'];
        $url_stderr = $dir_stderr . $record['node']['uuid'];
        $time_start = time();
        while(true){
            $process_active = [];
            foreach($options->process as $proc_id) {
                $command = 'ps -p ' . $proc_id;
                exec($command, $output, $code);
                $process_active[] = $code;
            }
            if(!in_array(0, $process_active)) {
                //task completed
                $patch = [
                    'id' => $record['node']['id'],
                    'status' => Status::COMPLETED,
                ];
                if(File::exist($url_stdout)){
                    $stdout = File::read($url_stdout, ['return' => File::ARRAY]);
                    $patch['output'] = $stdout;
                    File::delete($url_stdout);
                }
                if(File::exist($url_stderr)){
                    $stderr = File::read($url_stderr, ['return' => File::ARRAY]);
                    $patch['notification'] = $stderr;
                    File::delete($url_stderr);
                }
                $response = Entity::patch($object, $connection, $role, (object) $patch, $error);
                break;
            }
            //task is running
            usleep(0.5 * 1000 * 1000);
            $time_current = time();
            if($time_current - $time_start > 120 * 60 * 60){ // 2 hours time-out
                //timeout
                $patch = [
                    'id' => $record['node']['id'],
                    'status' => Status::ERROR,
                ];
                if(File::exist($url_stdout)){
                    $stdout = File::read($url_stdout, ['return' => File::ARRAY]);
                    $patch['output'] = $stdout;
                    File::delete($url_stdout);
                }
                if(File::exist($url_stderr)){
                    $stderr = File::read($url_stderr, ['return' => File::ARRAY]);
                    $patch['notification'] = $stderr;
                    File::delete($url_stderr);
                }
                $response = Entity::patch($object, $connection, $role, (object) $patch, $error);
                break;
            } else {
                //updates the task output / notification every half a second.
                $patch = [
                    'id' => $record['node']['id'],
                ];
                if(File::exist($url_stdout)){
                    $stdout = File::read($url_stdout, ['return' => File::ARRAY]);
                    $patch['output'] = $stdout;
                }
                if(File::exist($url_stderr)){
                    $stderr = File::read($url_stderr, ['return' => File::ARRAY]);
                    $patch['notification'] = $stderr;
                }
                $response = Entity::patch($object, $connection, $role, (object) $patch, $error);
            }
        }
    }
}

