<?php
namespace Icicle\Tests\Promise;

use Exception;
use Icicle\Loop\Loop;
use Icicle\Promise\Promise;
use Icicle\Tests\TestCase;

/**
 * @requires PHP 5.4
 */
class PromiseJoinTest extends TestCase
{
    public function tearDown()
    {
        Loop::clear();
    }
    
    public function testEmptyArray()
    {
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->identicalTo([]));
        
        Promise::join([])->done($callback, $this->createCallback(0));
        
        Loop::run();
    }
    
    public function testValuesArray()
    {
        $values = [1, 2, 3];
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->equalTo($values));
        
        Promise::join($values)->done($callback, $this->createCallback(0));
        
        Loop::run();
    }
    
    public function testFulfilledPromisesArray()
    {
        $values = [1, 2, 3];
        $promises = [Promise::resolve(1), Promise::resolve(2), Promise::resolve(3)];
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->equalTo($values));
        
        Promise::join($promises)->done($callback, $this->createCallback(0));
        
        Loop::run();
    }
    
    public function testPendingPromisesArray()
    {
        $values = [1, 2, 3];
        $promises = [
            Promise::resolve(1)->delay(0.2),
            Promise::resolve(2)->delay(0.3),
            Promise::resolve(3)->delay(0.1)
        ];
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->equalTo($values));
        
        Promise::join($promises)->done($callback, $this->createCallback(0));
        
        Loop::run();
    }
    
    public function testArrayKeysPreserved()
    {
        $values = ['one' => 1, 'two' => 2, 'three' => 3];
        $promises = [
            'one' => Promise::resolve(1)->delay(0.2),
            'two' => Promise::resolve(2)->delay(0.3),
            'three' => Promise::resolve(3)->delay(0.1)
        ];
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->callback(function ($result) use ($promises) {
            ksort($result);
            ksort($promises);
            return array_keys($result) === array_keys($promises);
        }));
        
        Promise::join($promises)->done($callback, $this->createCallback(0));
        
        Loop::run();
    }
    
    public function testRejectIfInputPromiseIsRejected()
    {
        $exception = new Exception();
        $promises = [Promise::resolve(1), Promise::reject($exception), Promise::resolve(3)];
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->identicalTo($exception));
        
        Promise::join($promises)->done($this->createCallback(0), $callback);
        
        Loop::run();
    }
}
