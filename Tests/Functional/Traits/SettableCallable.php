<?php

namespace Evoweb\SfRegister\Tests\Functional\Traits;

trait SettableCallable
{
    public function get(string $name): mixed
    {
        return $this->{$name};
    }

    public function set(string $name, mixed $value): void
    {
        $this->{$name} = $value;
    }

    public function call(string $name): void
    {
        $this->{$name}();
    }
}
