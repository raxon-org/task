<?php
namespace Package\Raxon\Task\Trait;

use Package\Raxon\Account\Service\Jwt;
use Raxon\App;

use Raxon\Exception\ErrorException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Node\Model\Node;
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
    }

    /**
     * @throws ObjectException
     * @throws FileWriteException
     * @throws ErrorException
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
            $where_list[] = [
                'value' => 1,
                'attribute' => 'is.active',
                'operator' => '>='
            ];
            $record = $node->record($class, $node->role_system(), ['where' => $where_list]);
            if(array_key_exists('node', $record)){
                if(property_exists('email', $record['node'])){
                    $token = User::token($object, $record['node']->email);
                    breakpoint($token);
                }
            }

            breakpoint($record);
        }


        d($options);
        echo 'node';
        breakpoint($flags);
    }

}

