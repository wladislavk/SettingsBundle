<?php
namespace VKR\SettingsBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use VKR\SettingsBundle\Exception\InterfaceNotImplementedException;
use VKR\SettingsBundle\Exception\SettingNotFoundException;
use VKR\SettingsBundle\Interfaces\SettingsEntityInterface;

class SettingsRetriever
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $settingsEntity;

    /**
     * @param Container $container
     * @param EntityManager $entityManager
     * @throws InterfaceNotImplementedException
     */
    public function __construct(Container $container, EntityManager $entityManager)
    {
        $this->parameterBag = $container->getParameterBag();
        $this->entityManager = $entityManager;
        try {
            $this->settingsEntity = $this->parameterBag->get('vkr_settings.settings_entity');
        } catch (ParameterNotFoundException $e) {
            $this->settingsEntity = '';
        }
        if ($this->settingsEntity) {
            $reflection = new \ReflectionClass($this->settingsEntity);
            if ($reflection->implementsInterface(SettingsEntityInterface::class) !== true) {
                throw new InterfaceNotImplementedException($this->settingsEntity, SettingsEntityInterface::class);
            }
        }
    }

    /**
     * @param string $settingName
     * @param bool|false $suppressErrors
     * @return bool|string
     * @throws SettingNotFoundException
     */
    public function get($settingName, $suppressErrors = false)
    {
        $settingValue = $this->checkIfSettingExistsInParameters($settingName);
        if ($settingValue !== false) {
            return $settingValue;
        }
        $settingValue = $this->checkIfSettingExistsInDB($settingName);
        if ($settingValue !== false) {
            return $settingValue;
        }
        if ($suppressErrors) {
            return false;
        }
        throw new SettingNotFoundException($settingName);
    }

    /**
     * @return array
     */
    public function getAllFromDB()
    {
        if (!$this->settingsEntity) {
            return [];
        }
        $repo = $this->entityManager->getRepository($this->settingsEntity);
        $records = $repo->findAll();
        $allSettings = [];
        /** @var SettingsEntityInterface $record */
        foreach ($records as $record) {
            $allSettings[$record->getName()] = $record->getValue();
        }
        return $allSettings;
    }

    /**
     * @param string $settingName
     * @return bool|string
     */
    private function checkIfSettingExistsInParameters($settingName)
    {
        try {
            $settingValue = $this->parameterBag->get($settingName);
        } catch (ParameterNotFoundException $e) {
            return false;
        }
        return $settingValue;
    }

    /**
     * @param string $settingName
     * @return bool|string
     */
    private function checkIfSettingExistsInDB($settingName)
    {
        if (!$this->settingsEntity) {
            return false;
        }
        $repo = $this->entityManager->getRepository($this->settingsEntity);
        /** @var SettingsEntityInterface|null $result */
        $result = $repo->findOneBy(['name' => $settingName]);
        if (!$result) {
            return false;
        }
        return $result->getValue();
    }
}
