<?php
namespace Package\Raxon\Task\Trait;

use Package\Raxon\Account\Service\Jwt;
use Raxon\App;

use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Node\Model\Node;

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
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     */
    public function task_create($flags, $options): void
    {
        $object = $this->object();

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
            $record = $node->record($class, $node->role_system(), ['where' => $where_list]);
            $jwt_options['user'] = $record['node'];
            $configuration = Jwt::configuration($object);
            $token = Jwt::get($object, $configuration, $jwt_options);
            $token = $token->toString();
            breakpoint($token);
            breakpoint($record);
        }


        d($options);
        echo 'node';
        breakpoint($flags);
    }

}

