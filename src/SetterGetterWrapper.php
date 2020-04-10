<?php

namespace SimpleWrapper;

abstract class SetterGetterWrapper extends Wrapper
{
    /**
     * magic method for defining getters
     *
     * @param string $property
     * @return void
     */
    public function __get($property)
    {
        $getterMethod = \strtolower("get{$property}");
        
        if (\method_exists($this->wrapee, $getterMethod)) {
            return \call_user_func_array([$this->wrapee, $getterMethod], []);
        }

        if (\property_exists($this->wrapee, $property)) {
            $ref = new \ReflectionProperty(\get_class($this->wrapee), $property);
            if ($ref->isPublic()) return $ref->getValue($this->wrapee);
        }

        throw new \RuntimeException("Property {$property} doesn't have a defined getter", 1);
    }

    /**
     * magic method for defining setters
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $setterMethod = \strtolower("set{$property}");
        
        if (\method_exists($this->wrapee, $setterMethod)) {
            return \call_user_func_array([$this->wrapee, $setterMethod], [$value]);
        }

        if (\property_exists($this->wrapee, $property)) {
            $ref = new \ReflectionProperty(\get_class($this->wrapee), $property);
            if ($ref->isPublic()) {
                $ref->setValue($this->wrapee, $value);
            }
        }

        throw new \RuntimeException("Property {$property} doesn't have a defined setter", 1);
    }
}