<?php
declare(ticks=1);

use function ThenLabs\TickAsync\delay;
use ThenLabs\TickPromise\Promise;

function ticks(int $number): void
{
    for ($i = 1; $i <= $number; $i++) {
        $a = $i;
    }
}

test(function () {
    $promise = new Promise(function ($resolve, $reject, $promise) {
        $this->executed = true;
        $this->assertEquals('pending', $promise->getState());

        $resolve();
    });

    $promise->await();

    $this->assertTrue($this->executed);
    $this->assertEquals('fulfilled', $promise->getState());
});

test(function () {
    $promise = new Promise(function ($resolve, $reject, $promise) {
        $this->executed = true;
        $this->assertEquals('pending', $promise->getState());

        $reject();
    });

    $promise->await();

    $this->assertTrue($this->executed);
    $this->assertEquals('rejected', $promise->getState());
});

test(function () {
    $promise = new Promise(function ($resolve) {
        $resolve(100);
    });

    $promise->then(function ($value) {
        $this->executed1 = true;
        $this->assertEquals(100, $value);
    });

    $promise->then(function ($value) {
        $this->executed2 = true;
        $this->assertEquals(100, $value);
    });

    $promise->then(function ($value) {
        $this->executed3 = true;
        $this->assertEquals(100, $value);
    });

    $this->assertEquals(100, $promise->getValue());

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
    $this->assertTrue($this->executed3);
});

test(function () {
    $promise = new Promise(function ($resolve) {
        delay('+100 microseconds');
        $resolve(200);
    });

    $promise->await();

    $promise->then(function ($value) {
        $this->executed1 = true;
        $this->assertEquals(200, $value);
    });

    $promise->then(function ($value) {
        $this->executed2 = true;
        $this->assertEquals(200, $value);
    });

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
});

test(function () {
    $promise = new Promise(function ($resolve, $reject) {
        $reject(100);
    });

    $promise->catch(function ($value) {
        $this->executed1 = true;
        $this->assertEquals(100, $value);
    });

    $promise->catch(function ($value) {
        $this->executed2 = true;
        $this->assertEquals(100, $value);
    });

    $promise->catch(function ($value) {
        $this->executed3 = true;
        $this->assertEquals(100, $value);
    });

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
    $this->assertTrue($this->executed3);
});

test(function () {
    $promise = new Promise(function () {
        throw new Exception('my error');
    });

    $promise->catch(function ($exception) {
        $this->executed1 = true;
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('my error', $exception->getMessage());
    });

    $this->assertTrue($this->executed1);
});

test(function () {
    $promise = new Promise(function ($resolve, $reject) {
        usleep(100000);
        $reject(200);
    });

    $promise->await();

    $promise->catch(function ($value) {
        $this->executed1 = true;
        $this->assertEquals(200, $value);
    });

    $promise->catch(function ($value) {
        $this->executed2 = true;
        $this->assertEquals(200, $value);
    });

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
});

test(function () {
    $this->executedOnResolved = false;
    $this->executedOnRejected = false;

    $promise = new Promise(function ($resolve) {
        $resolve(100);
    });

    $onResolved = function ($value) {
        $this->executedOnResolved = true;
        $this->assertEquals(100, $value);
    };

    $onReject = function ($value) {
        $this->executedOnRejected = true;
        $this->assertEquals(100, $value);
    };

    $promise->then($onResolved, $onReject);

    $this->assertTrue($this->executedOnResolved);
    $this->assertFalse($this->executedOnRejected);
});

testCase(function () {
    setUp(function () {
        $this->executedOnResolved = false;
        $this->executedOnRejected = false;

        $this->onResolved = function ($value) {
            $this->executedOnResolved = true;
            $this->assertEquals(100, $value);
        };

        $this->onReject = function ($value) {
            $this->executedOnRejected = true;
            $this->assertEquals(100, $value);
        };
    });

    test(function () {
        $promise = new Promise(function ($resolve) {
            $resolve(100);
        });

        $promise->then($this->onResolved, $this->onReject);

        $this->assertTrue($this->executedOnResolved);
        $this->assertFalse($this->executedOnRejected);
    });

    test(function () {
        $promise = new Promise(function ($resolve, $reject) {
            $reject(100);
        });

        $promise->then($this->onResolved, $this->onReject);

        $this->assertFalse($this->executedOnResolved);
        $this->assertTrue($this->executedOnRejected);
    });
});

test(function () {
    $promise = new Promise(function ($resolve) {
        delay('+100 microseconds');
        $resolve(100);
    });

    $promise
        ->then(function ($value) {
            $this->assertEquals(100, $value);
            $this->executed1 = true;
        })
        ->then(function ($value) {
            $this->assertNull($value);
            $this->executed2 = true;
        })
        ->then(function ($value) {
            $this->assertNull($value);
            $this->executed3 = true;
        })
    ;

    $promise->await();

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
    $this->assertTrue($this->executed3);
});

test(function () {
    $promise = new Promise(function ($resolve) {
        delay('+100 microseconds');
        $resolve(100);
    });

    $promise->await();

    $promise
        ->then(function ($value) {
            $this->assertEquals(100, $value);
            $this->executed1 = true;
        })
        ->then(function ($value) {
            $this->assertNull($value);
            $this->executed2 = true;
        })
        ->then(function ($value) {
            $this->assertNull($value);
            $this->executed3 = true;
        })
    ;

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
    $this->assertTrue($this->executed3);
});

test(function () {
    $promise = new Promise(function ($resolve) {
        $resolve(100);
    });

    $promise
        ->then(function ($value) {
            $this->assertEquals(100, $value);
            $this->executed1 = true;
            return $value;
        })
        ->then(function ($value) {
            $this->assertEquals(100, $value);
            $this->executed2 = true;
        })
        ->then(function ($value) {
            $this->assertNull($value);
            $this->executed3 = true;
        })
        ->then(function () {
            $this->executed4 = true;
        })
    ;

    ticks(5);

    $this->assertTrue($this->executed1);
    $this->assertTrue($this->executed2);
    $this->assertTrue($this->executed3);
    $this->assertTrue($this->executed4);
});

test(function () {
    $this->executed1 = false;
    $this->executed2 = false;
    $this->executed3 = false;

    $promise = new Promise(function ($resolve, $reject) {
        $reject(100);
    });

    $promise
        ->then(function () {
            $this->executed1 = true;
        })
        ->then(function () {
            $this->executed2 = true;
        })
        ->catch(function ($error) {
            $this->assertEquals(100, $error);
            $this->executed3 = true;
        })
    ;

    ticks(100);

    $this->assertFalse($this->executed1);
    $this->assertFalse($this->executed2);
    $this->assertTrue($this->executed3);
});

test(function () {
    $this->executed1 = false;
    $this->executed2 = false;
    $this->executed3 = false;

    $promise = new Promise(function () {
        throw new Exception();
    });

    $promise
        ->then(function () {
            $this->executed1 = true;
        })
        ->then(function () {
            $this->executed2 = true;
        })
        ->catch(function ($error) {
            $this->assertInstanceOf(Exception::class, $error);
            $this->executed3 = true;
        })
    ;

    ticks(100);

    $this->assertFalse($this->executed1);
    $this->assertFalse($this->executed2);
    $this->assertTrue($this->executed3);
});