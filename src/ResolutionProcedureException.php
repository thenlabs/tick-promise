<?php
declare(strict_types=1);

namespace ThenLabs\TickPromise;

use Exception;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class ResolutionProcedureException extends Exception
{
    /**
     * @var Promise
     */
    protected $promise;

    /**
     * @var mixed
     */
    protected $x;

    /**
     * @param Promise $promise
     * @param mixed $x
     */
    public function __construct(Promise $promise, $x)
    {
        $this->promise = $promise;
        $this->x = $x;
    }

    public function getPromise(): Promise
    {
        return $this->promise;
    }

    public function getX()
    {
        return $this->x;
    }
}