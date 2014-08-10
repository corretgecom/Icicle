<?php
namespace Icicle\Timer;

use SplObjectStorage;
use SplQueue;

class ImmediateQueue implements \Countable
{
    /**
     * @var     SplQueue
     */
    private $queue;
    
    /**
     * @var     SplObjectStorage
     */
    private $immediates;
    
    /**
     */
    public function __construct()
    {
        $this->queue = new SplQueue();
        $this->immediates = new SplObjectStorage();
    }
    
    /**
     * Adds the timer to the queue.
     * @param   ImmediateInterface $immediate
     */
    public function add(ImmediateInterface $immediate)
    {
        if (!$this->immediates->contains($immediate)) {
            $this->queue->push($immediate);
            $this->immediates->attach($immediate);
        }
    }
    
    /**
     * Determines if the timer is in the queue.
     * @param   ImmediateInterface $immediate
     * @return  bool
     */
    public function contains(ImmediateInterface $immediate)
    {
        return $this->immediates->contains($immediate);
    }
    
    /**
     * Removes the immediate from the queue.
     * @param   ImmediateInterface $immediate
     */
    public function remove(ImmediateInterface $immediate)
    {
        if ($this->immediates->contains($immediate)) {
            foreach ($this->queue as $key => $value) {
                if ($value === $immediate) {
                    unset($this->queue[$key]);
                    break;
                }
            }
            
            $this->immediates->detach($immediate);
        }
    }
    
    /**
     * Returns the number of immediates in the queue.
     * @return  int
     */
    public function count()
    {
        return $this->queue->count();
    }
    
    /**
     * Determines if the queue is empty.
     * @return  bool
     */
    public function isEmpty()
    {
        return 0 === $this->count();
    }
    
    /**
     * Removes all immediates in the queue. Safe to call during call to tick().
     */
    public function clear()
    {
        $this->queue = new SplQueue();
        $this->immediates = new SplObjectStorage();
    }
    
    /**
     * Executes the next immediate. Returns true if one was executed, false if there were no immediates
     * in the queue.
     *
     * @return  bool
     */
    public function tick()
    {
        if ($this->queue->isEmpty()) {
            return false;
        }
        
        $immediate = $this->queue->shift();
        $this->immediates->detach($immediate);
        
        $immediate->call(); // Execute the immediate.
        
        return true;
    }
}
