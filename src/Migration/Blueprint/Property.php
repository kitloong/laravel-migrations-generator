<?php

namespace KitLoong\MigrationsGenerator\Migration\Blueprint;

class Property
{
    /** @var string */
    private $name;

    /** @var mixed */
    private $value;

    /**
     * Property constructor.
     *
     * @param  mixed  $value
     */
    public function __construct(string $name, $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
