<?php
namespace VKR\SettingsBundle\Exception;

class SettingNotFoundException extends \Exception
{
    /**
     * @param string $settingName
     */
    public function __construct($settingName)
    {
        $message = "Setting $settingName not found";
        parent::__construct($message);
    }
}
