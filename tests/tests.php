<?php

use ThenLabs\TickAsync\TickAsync;
use ThenLabs\TickPromise\Promise;

testCase(function () {
    setUp(function () {
        $this->callable = function ($task) {
            $task->end();
        };

        $this->promise = new Promise($this->callable);
    });

    test(function () {
        $tasks = TickAsync::getLoop()->getTasks();

        $this->assertCount(1, $tasks);

        $task = $tasks->current();

        $this->assertSame($this->callable, $task->getCallable());
        $this->assertSame($task, $this->promise->getTask());
    });

    test(function () {
        $this->assertEquals('pending', $this->promise->getState());
    });
});