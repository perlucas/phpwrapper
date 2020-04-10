<?php

namespace SimpleWrapper;

abstract class GhostWrapper extends SetterGetterWrapper
{

    /**
     * returns true is the method is loaded on the wrapee
     *
     * @param string $method
     * @param array $args
     * @return boolean
     */
    protected abstract function wrapeeMethodLoaded($method, array $args);

    /**
     * loads the wrapee method
     *
     * @param string $method
     * @param array $args
     * @return void
     */
    protected abstract function loadWrapeeMethod($method, array $args);

    /**
     * magic method for forwarding a call
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        if (!$this->wrapeeMethodLoaded($method, $args)) {
            $this->loadWrapeeMethod($method, $args);
        }
        return parent::__call($method, $args);
    }

    /**
     * returns true if the getter for the property is loaded
     *
     * @param string $property
     * @return boolean
     */
    protected abstract function wrapeeGetterLoaded($property);
    
    /**
     * loads the wrapee getter for that property
     *
     * @param string $property
     * @return void
     */
    protected abstract function loadWrapeeGetter($property);
    
    /**
     * magic method for defining getter
     *
     * @param string $property
     * @return void
     */
    public function __get($property)
    {
        if (!$this->wrapeeGetterLoaded($property)) {
            $this->loadWrapeeGetter($property);
        }
        return parent::__get($property);
    }
    
    /**
     * returns true if the setter is loaded
     *
     * @param string $property
     * @param mixed $value
     * @return boolean
     */
    protected function wrapeeSetterLoaded($property, $value)
    {
        return true;
    }
    
    /**
     * loads the method setter
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    protected function loadWrapeeSetter($property, $value)
    {
        // do nothing by default
    }
    
    /**
     * magic method for defining setters
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        if (!$this->wrapeeSetterLoaded($property, $value)) {
            $this->loadWrapeeSetter($property, $value);
        }
        return parent::__set($property, $value);
    }
}