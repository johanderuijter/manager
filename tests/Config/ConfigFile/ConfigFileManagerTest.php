<?php

/*
 * This file is part of the puli/repository-manager package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\RepositoryManager\Tests\Config\ConfigFile;

use Puli\RepositoryManager\Config\Config;
use Puli\RepositoryManager\Config\ConfigFile\ConfigFile;
use Puli\RepositoryManager\Config\ConfigFile\ConfigFileManager;
use Puli\RepositoryManager\Config\ConfigFile\ConfigFileStorage;
use Puli\RepositoryManager\Tests\Package\Fixtures\TestGlobalEnvironment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ConfigFileManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $homeDir;

    /**
     * @var ConfigFile
     */
    private $configFile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var TestGlobalEnvironment
     */
    private $environment;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConfigFileStorage
     */
    private $configFileStorage;

    /**
     * @var ConfigFileManager
     */
    private $manager;

    protected function setUp()
    {
        $this->homeDir = __DIR__.'/Fixtures/home';
        $this->configFile = new ConfigFile();
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->environment = new TestGlobalEnvironment(
            $this->homeDir,
            $this->configFile,
            $this->dispatcher
        );

        $this->configFileStorage = $this->getMockBuilder('Puli\RepositoryManager\Config\ConfigFile\ConfigFileStorage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new ConfigFileManager($this->environment, $this->configFileStorage);
    }

    public function testSetConfigKey()
    {
        $this->configFileStorage->expects($this->once())
            ->method('saveConfigFile')
            ->with($this->configFile)
            ->will($this->returnCallback(function (ConfigFile $configFile) {
                $config = $configFile->getConfig();

                \PHPUnit_Framework_Assert::assertSame('my-puli-dir', $config->get(Config::PULI_DIR));
            }));

        $this->manager->setConfigKey(Config::PULI_DIR, 'my-puli-dir');
    }

    public function testSetConfigKeys()
    {
        $this->configFileStorage->expects($this->once())
            ->method('saveConfigFile')
            ->with($this->configFile)
            ->will($this->returnCallback(function (ConfigFile $configFile) {
                $config = $configFile->getConfig();

                \PHPUnit_Framework_Assert::assertSame('my-puli-dir', $config->get(Config::PULI_DIR));
                \PHPUnit_Framework_Assert::assertSame('my-puli-dir/my-install-file.json', $config->get(Config::INSTALL_FILE));
            }));

        $this->manager->setConfigKeys(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::INSTALL_FILE => '{$puli-dir}/my-install-file.json',
        ));
    }

    public function testGetConfigKey()
    {
        $this->assertNull($this->manager->getConfigKey(Config::PULI_DIR));

        $this->configFile->getConfig()->set(Config::PULI_DIR, 'my-puli-dir');

        $this->assertSame('my-puli-dir', $this->manager->getConfigKey(Config::PULI_DIR));
    }

    public function testGetConfigKeyReturnsRawValue()
    {
        $this->configFile->getConfig()->set(Config::PULI_DIR, 'my-puli-dir');
        $this->configFile->getConfig()->set(Config::INSTALL_FILE, '{$puli-dir}/install-file.json');

        $this->assertSame('{$puli-dir}/install-file.json', $this->manager->getConfigKey(Config::INSTALL_FILE));
    }

    public function testGetConfigKeys()
    {
        $this->configFile->getConfig()->set(Config::PULI_DIR, 'my-puli-dir');
        $this->configFile->getConfig()->set(Config::INSTALL_FILE, '{$puli-dir}/install-file.json');

        $this->assertSame(array(
            Config::PULI_DIR => 'my-puli-dir',
            Config::INSTALL_FILE => '{$puli-dir}/install-file.json',
        ), $this->manager->getConfigKeys());
    }

    public function testRemoveConfigKey()
    {
        $this->configFile->getConfig()->set(Config::PULI_DIR, 'my-puli-dir');
        $this->configFile->getConfig()->set(Config::INSTALL_FILE, 'install-file.json');

        $this->configFileStorage->expects($this->once())
            ->method('saveConfigFile')
            ->with($this->configFile)
            ->will($this->returnCallback(function (ConfigFile $configFile) {
                $config = $configFile->getConfig();

                \PHPUnit_Framework_Assert::assertNull($config->get(Config::PULI_DIR, false));
                \PHPUnit_Framework_Assert::assertSame('install-file.json', $config->get(Config::INSTALL_FILE, false));
            }));

        $this->manager->removeConfigKey(Config::PULI_DIR);
    }

    public function testRemoveConfigKeys()
    {
        $this->configFile->getConfig()->set(Config::PULI_DIR, 'my-puli-dir');
        $this->configFile->getConfig()->set(Config::INSTALL_FILE, 'install-file.json');

        $this->configFileStorage->expects($this->once())
            ->method('saveConfigFile')
            ->with($this->configFile)
            ->will($this->returnCallback(function (ConfigFile $configFile) {
                $config = $configFile->getConfig();

                \PHPUnit_Framework_Assert::assertNull($config->get(Config::PULI_DIR, false));
                \PHPUnit_Framework_Assert::assertNull($config->get(Config::INSTALL_FILE, false));
            }));

        $this->manager->removeConfigKeys(array(Config::PULI_DIR, Config::INSTALL_FILE));
    }
}
