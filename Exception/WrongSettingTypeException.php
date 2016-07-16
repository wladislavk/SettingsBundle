<?php
namespace VKR\SettingsBundle\Exception;

class WrongSettingTypeException extends \Exception
{
    /**
     * @param string $settingName
     * @param string $desiredType
     */
    public function __construct($settingName, $desiredType)
    {
        $message = "Setting $settingName should be of type $desiredType";
        parent::__construct($message);
    }
}
