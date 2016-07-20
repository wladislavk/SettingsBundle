<?php
namespace VKR\SettingsBundle\Tests\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use VKR\SettingsBundle\Services\SettingsRetriever;
use VKR\SettingsBundle\TestHelpers\SettingsEntity;

class SettingsRetrieverTest extends \PHPUnit_Framework_TestCase
{
    protected $mockedEntity = 'VKR\SettingsBundle\TestHelpers\SettingsEntity';
    protected $notFoundException = 'VKR\SettingsBundle\Exception\SettingNotFoundException';

    /**
     * @var array
     */
    protected $paramSettings = [
        'setting1' => 'value1',
        'setting2' => 'value2_from_param',
    ];

    /**
     * @var array
     */
    protected $dbSettings = [
        'setting2' => 'value2_from_db',
        'setting3' => 'value3',
        'setting4' => 'value4',
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $settingsRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parameterBag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var SettingsRetriever
     */
    protected $settingsRetriever;

    public function setUp()
    {
        $this->paramSettings['settings_entity'] = $this->mockedEntity;
        $this->mockParameterBag();
        $this->mockContainer();
        $this->mockSettingsRepository();
        $this->mockEntityManager();
        $this->settingsRetriever = new SettingsRetriever($this->container, $this->entityManager);
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
        $this->setExpectedException($this->notFoundException);
        $value = $this->settingsRetriever->get('setting125');
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

    protected function mockParameterBag()
    {
        $this->parameterBag = $this
            ->getMock(ParameterBagInterface::class);
        $this->parameterBag->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'getMockedParamSettingCallback']));
    }

    protected function mockContainer()
    {
        $this->container = $this
            ->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->container->expects($this->once())
            ->method('getParameterBag')
            ->will($this->returnValue($this->parameterBag));
    }

    protected function mockSettingsRepository()
    {
        $this->settingsRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnCallback([$this, 'getMockedDBSettingCallback']));
        $this->settingsRepository->expects($this->any())
            ->method('findAll')
            ->will($this->returnCallback([$this, 'getAllMockedSettingsCallback']));
    }

    protected function mockEntityManager()
    {
        $this->entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->settingsRepository));
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
            /** @var SettingsEntity $setting */
            $setting = new $this->mockedEntity();
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
            /** @var SettingsEntity $setting */
            $setting = new $this->mockedEntity();
            $setting->setName($name);
            $setting->setValue($value);
            $allSettings[] = $setting;
        }
        return $allSettings;
    }
}
