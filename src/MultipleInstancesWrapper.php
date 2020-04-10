<?php

namespace SimpleWrapper;

class MultipleInstancesWrapper
{
    /**
     * wrapee instances
     *
     * @var array
     */
    protected $instances;

    /**
     * instance construction
     */
    public function __construct()
    {
        $this->validateWrapees(\func_get_args());
        $this->instances = \func_get_args();
    }

    /**
     * basic wrapees validation
     *
     * @param array $objects
     * @return void
     */
    protected function validateWrapees(array $objects)
    {
        foreach ($objects as $index => $object) {
            if (!\is_object($object)) {
                throw new \InvalidArgumentException("Argument {$index} is not an object", 1);
            }
        }
        if (empty($objects)) {
            throw new \InvalidArgumentException("At least one object must be passed in as argument", 1);
        }
    }

    /**
     * wrapee class name that must be used for this method
     *
     * @param string $method
     * @return null|string
     */
    protected function getProviderClassForMethod($method)
    {
        return null;
    }

    /**
     * magic method for calling wrapees methods
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

        $providers = [];
        foreach ($this->instances as $instance) {
            if (\method_exists($instance, $method)) {
                $providers[] = $instance;
            }
        }

        if ($providers) {
            $class = $this->getProviderClassForMethod($method);
            if ($class && count($providers) > 1) {
                foreach ($providers as $obj) {
                    if (\get_class($obj) === $class) {
                        return \call_user_func_array([$obj, $method], $args);
                    }
                }
            }
            return \call_user_func_array([$providers[0], $method], $args);
        }

        throw new \BadMethodCallException("Method {$method} doesn't exists on objects", 1);
    }

}