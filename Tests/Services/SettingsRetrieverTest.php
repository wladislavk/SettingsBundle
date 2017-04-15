<?php
namespace VKR\SettingsBundle\Tests\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use VKR\SettingsBundle\Exception\SettingNotFoundException;
use VKR\SettingsBundle\Services\SettingsRetriever;
use VKR\SettingsBundle\TestHelpers\SettingsEntity;

class SettingsRetrieverTest extends TestCase
{
    /**
     * @var array
     */
    private $paramSettings = [
        'setting1' => 'value1',
        'setting2' => 'value2_from_param',
    ];

    /**
     * @var array
     */
    private $dbSettings = [
        'setting2' => 'value2_from_db',
        'setting3' => 'value3',
        'setting4' => 'value4',
    ];

    /**
     * @var SettingsRetriever
     */
    private $settingsRetriever;

    public function setUp()
    {
        $this->paramSettings['vkr_settings.settings_entity'] = SettingsEntity::class;
        $container = $this->mockContainer();
        $entityManager = $this->mockEntityManager();
        $this->settingsRetriever = new SettingsRetriever($container, $entityManager);
    }

    public function testGetSettingFromParam()
    {
        $value = $this->settingsRetriever->get('setting1');
        $this->assertEquals('value1', $value);
    }

    public function testGetSettingFromDB()
    {
        $value = $this->settingsRetriever->get('setting3');
        $this->assertEquals('value3', $value);
    }

    public function testGetOverlappingSetting()
    {
        $value = $this->settingsRetriever->get('setting2');
        $this->assertEquals('value2_from_param', $value);
    }

    public function testGetNonExistentSetting()
    {
        $this->expectException(SettingNotFoundException::class);
        $this->settingsRetriever->get('setting125');
    }

    public function testGetNonExistentSettingForgiving() {
        $value = $this->settingsRetriever->get('setting125', true);
        $this->assertFalse(false, $value);
    }

    public function testGetAllSettings()
    {
        $values = $this->settingsRetriever->getAllFromDB();
        $this->assertTrue(is_array($values));
        $this->assertEquals(3, sizeof($values));
        /**
         * @var string $key
         * @var string $value
         */
        foreach ($values as $key => $value) {
            if ($key == 'setting2') {
                $this->assertEquals('value2_from_db', $value);
                break;
            }
        }
    }

    private function mockParameterBag()
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturnCallback([$this, 'getMockedParamSettingCallback']);
        return $parameterBag;
    }

    private function mockContainer()
    {
        $container = $this->createMock(Container::class);
        $container->method('getParameterBag')->willReturn($this->mockParameterBag());
        return $container;
    }

    private function mockSettingsRepository()
    {
        $settingsRepository = $this->createMock(EntityRepository::class);
        $settingsRepository->method('findOneBy')
            ->will($this->returnCallback([$this, 'getMockedDBSettingCallback']));
        $settingsRepository->method('findAll')
            ->will($this->returnCallback([$this, 'getAllMockedSettingsCallback']));
        return $settingsRepository;
    }

    private function mockEntityManager()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($this->mockSettingsRepository());
        return $entityManager;
    }

    public function getMockedParamSettingCallback($settingName)
    {
        if (array_key_exists($settingName, $this->paramSettings)) {
            return $this->paramSettings[$settingName];
        }
        throw new ParameterNotFoundException($settingName);
    }

    public function getMockedDBSettingCallback(array $settingArray)
    {
        $settingName = $settingArray['name'];
        if (array_key_exists($settingName, $this->dbSettings)) {
            $setting = new SettingsEntity();
            $setting->setName($settingName);
            $setting->setValue($this->dbSettings[$settingName]);
            return $setting;
        }
        return null;
    }

    public function getAllMockedSettingsCallback()
    {
        $allSettings = [];
        foreach ($this->dbSettings as $name => $value) {
            $setting = new SettingsEntity();
            $setting->setName($name);
            $setting->setValue($value);
            $allSettings[] = $setting;
        }
        return $allSettings;
    }
}
