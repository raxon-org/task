<?php
namespace Package\Raxon\Task\Trait;

use Raxon\App;

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
            breakpoint($record);
        }


        d($options);
        echo 'node';
        breakpoint($flags);
    }

}

