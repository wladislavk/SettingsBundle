<?php
namespace VKR\SettingsBundle\TestHelpers;

use VKR\SettingsBundle\Interfaces\SettingsEntityInterface;

class SettingsEntity implements SettingsEntityInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }
}
