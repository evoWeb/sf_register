<?php

namespace Evoweb\SfRegister\Tests\Functional;

trait SettableCallable
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->{$name};
    }

    public function set(string $name, $value)
    {
        $this->{$name} = $value;
    }

    public function call(string $name)
    {
        $this->{$name}();
    }
}
