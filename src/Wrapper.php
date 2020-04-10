<?php

namespace SimpleWrapper;

abstract class Wrapper
{
    /**
     * represents the wrapped object
     *
     * @var object
     */
    protected $wrapee;

    /**
     * constructs an instance of this wrapper
     *
     * @param object $object
     */
    public function __construct($object)
    {
        $this->validateWrappee($object);
        $this->wrapee = $object;
    }

    /**
     * validates the wrappee
     *
     * @param object $object
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateWrappee($object)
    {
        if (!\is_object($object)) {
            throw new \InvalidArgumentException("Argument passed must be an object", 1);
        }

        if (!\is_a($object, $this->getWrapeeClass())) {
            throw new \InvalidArgumentException("Argument passed is not of kind " . $this->getWrapeeClass(), 1);
        }
    }

    /**
     * returns the wrapee class or parent class
     *
     * @return string
     */
    protected abstract function getWrapeeClass();

    /**
     * magic method for defining the access to the wrappee or wrapper methods
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (\method_exists($this, $method)) {
            return \call_user_func_array([$this, $method], $args);
        }
        
        if (method_exists($this->wrapee, $method)) {
            return \call_user_func_array([$this->wrapee, $method], $args);
        }

        throw new \BadMethodCallException("Method {$method} doesn't exists on " . \get_class() . " or " . $this->getWrapeeClass(), 1);
    }
}