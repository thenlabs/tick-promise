<?php
declare(strict_types=1);

namespace ThenLabs\TickPromise;

use function ThenLabs\TickAsync\async;
use ThenLabs\TickAsync\AsyncTask;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Promise
{
    public const STATE_PENDING   = 'pending';
    public const STATE_FULFILLED = 'fulfilled';
    public const STATE_REJECTED  = 'rejected';

    /**
     * @var AsyncTask
     */
    protected $task;

    /**
     * @var string
     */
    protected $state = self::STATE_PENDING;

    public function __construct(callable $callable)
    {
        $this->task = async($callable);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getTask(): AsyncTask
    {
        return $this->task;
    }
}