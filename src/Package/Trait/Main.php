<?php
namespace Package\Raxon\Task\Trait;

use Raxon\App;

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

    public function task_create($flags, $options): void
    {
        $object = $this->object();
        d($options);
        echo 'node';
        breakpoint($flags);
    }

}

