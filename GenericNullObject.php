<?php

namespace SimpleWrapper;

abstract class GenericNullObject
{
    /**
     * get the wrapee class name
     *
     * @return string
     */
    protected abstract function getClassName();

    /**
     * get the return types for each method f the wrapee
     *
     * @return array - associative array [method => type]
     */
    protected function getReturnTypes()
    {
        return [];
    }

    /**
     * default returning type
     *
     * @return mixed
     */
    protected function getDefaultReturnType()
    {
        return null;
    }

    /**
     * magic method for accessing wrapee methods
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

        if (\method_exists($this->getClassName(), $method)) {

            $arr = $this->getReturnTypes();
            if (\array_key_exists($method, $arr)) {
                return $arr[$method];
            }

            return $this->getDefaultReturnType();
        }

        throw new \BadMethodCallException("Method {$method} doesn't exists on class {$this->getClassName()}", 1);
    }
}