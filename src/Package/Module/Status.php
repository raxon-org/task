<?php
namespace Package\Raxon\Task\Module;

class Status {
    const PENDING = 'Pending';
    const IN_PROGRESS = 'In Progress';
    const COMPLETED = 'Completed';
    const FAILED = 'Failed';
    const CANCELLED = 'Cancelled';
    const ERROR = 'Error';
    const LIST = [
        self::PENDING,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::FAILED,
        self::CANCELLED,
        self::ERROR
    ];
}