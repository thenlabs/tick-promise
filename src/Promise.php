<?php
declare(strict_types=1);

namespace ThenLabs\TickPromise;

use Exception;
use function ThenLabs\TickAsync\async;
use ThenLabs\TickAsync\AsyncTask;
use function ThenLabs\TickAsync\await;

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

    /**
     * @var array<callable>
     */
    protected $onFulfilledList = [];

    /**
     * @var array<callable>
     */
    protected $onRejectedList = [];

    public function __construct(callable $callable)
    {
        $this->task = async(function () use ($callable) {
            try {
                $callable([$this, 'resolve'], [$this, 'reject'], $this);
            } catch (Exception $exception) {
                $this->reject($exception);
            }
        });
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getTask(): AsyncTask
    {
        return $this->task;
    }

    public function getValue()
    {
        return self::STATE_FULFILLED == $this->state ? $this->task->getResult() : null;
    }

    public function getReason()
    {
        return self::STATE_REJECTED == $this->state ? $this->task->getResult() : null;
    }

    public function await()
    {
        await($this->task);
    }

    public function resolve($value = null): void
    {
        $this->state = self::STATE_FULFILLED;
        $this->task->end($value);

        foreach ($this->onFulfilledList as $key => $onFulfilled) {
            unset($this->onFulfilledList[$key]);

            $onFulfilled($value);
        }
    }

    public function reject($reason = null): void
    {
        $this->state = self::STATE_REJECTED;
        $this->task->end($reason);

        foreach ($this->onRejectedList as $key => $onRejected) {
            unset($this->onRejectedList[$key]);

            $onRejected($reason);
        }
    }

    public function cancel(): void
    {
        $this->task->unregister();
    }

    public function then(?callable $onFulfilled, ?callable $onRejected = null): self
    {
        $onFulfilledResult = null;

        if ($onFulfilled) {
            if ($this->state == self::STATE_PENDING) {
                $this->onFulfilledList[] = $onFulfilled;
            } elseif ($this->state == self::STATE_FULFILLED) {
                $onFulfilledResult = $onFulfilled($this->getValue());
            }
        }

        if ($onRejected) {
            if ($this->state == self::STATE_PENDING) {
                $this->onRejectedList[] = $onRejected;
            } elseif ($this->state == self::STATE_REJECTED) {
                $onRejected($this->getReason());
            }
        }

        return new static(function ($resolve, $reject) use ($onFulfilledResult) {
            if ($this->state == self::STATE_FULFILLED) {
                $resolve($onFulfilledResult);
            } elseif ($this->state == self::STATE_REJECTED) {
                $reject($this->getReason());
            }
        });
    }

    public function catch(callable $onRejected): self
    {
        return $this->then(null, $onRejected);
    }
}
