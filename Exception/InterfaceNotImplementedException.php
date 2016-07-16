<?php
namespace VKR\SettingsBundle\Exception;

class InterfaceNotImplementedException extends \Exception
{
    /**
     * @param string $className
     * @param string $interfaceName
     */
    public function __construct($className, $interfaceName)
    {
        $message = "$className class must implement $interfaceName interface";
        parent::__construct($message);
    }
}
