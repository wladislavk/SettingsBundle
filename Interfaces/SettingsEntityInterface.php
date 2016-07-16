<?php
namespace VKR\SettingsBundle\Interfaces;

interface SettingsEntityInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getValue();
}
