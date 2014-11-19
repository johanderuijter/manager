<?php

/*
 * This file is part of the Puli PackageManager package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\PackageManager\Config;

use Puli\PackageManager\InvalidConfigException;

/**
 * The system-wide Puli configuration.
 *
 * Root packages inherit their settings from this configuration.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GlobalConfig
{
    /**
     * @var string|null
     */
    private $packageRepositoryConfig;

    /**
     * @var string|null
     */
    private $generatedResourceRepository;

    /**
     * @var string|null
     */
    private $resourceRepositoryCache;

    /**
     * @var string[]
     */
    private $pluginClasses = array();

    /**
     * Creates a global configuration with predefined default values.
     *
     * @return GlobalConfig The default configuration.
     */
    public static function createDefault()
    {
        $config = new self();
        $config->setPackageRepositoryConfig('.puli/packages.json');
        $config->setGeneratedResourceRepository('.puli/resource-repository.php');
        $config->setResourceRepositoryCache('.puli/cache');

        return $config;
    }

    /**
     * Returns the path to the package repository configuration file.
     *
     * If the path is relative, it is calculated relative to the install path
     * of the root package.
     *
     * @return string|null The path to the configuration file of `null` if none
     *                     is set.
     */
    public function getPackageRepositoryConfig()
    {
        return $this->packageRepositoryConfig;
    }

    /**
     * Sets the path to the package repository configuration file.
     *
     * If the path is relative, it is calculated relative to the install path
     * of the root package.
     *
     * @param string|null $configPath The path to the configuration file or
     *                                `null` to unset.
     *
     * @throws InvalidConfigException If the path is empty or not a string/`null`.
     */
    public function setPackageRepositoryConfig($configPath)
    {
        if (!is_string($configPath) && null !== $configPath) {
            throw new InvalidConfigException(sprintf(
                'The path to the repository configuration should be a string '.
                'or null. Got: %s',
                is_object($configPath) ? get_class($configPath) : gettype($configPath)
            ));
        }

        if ('' === $configPath) {
            throw new InvalidConfigException('The path to the repository configuration should not be empty.');
        }

        $this->packageRepositoryConfig = $configPath;
    }

    /**
     * Returns the path where generated resource repository is placed.
     *
     * If the path is relative, it is calculated relative to the install path
     * of the root package.
     *
     * @return string|null The path to the generated resource repository or
     *                     `null` if none is set.
     */
    public function getGeneratedResourceRepository()
    {
        return $this->generatedResourceRepository;
    }

    /**
     * Sets the path where generated resource repository is placed.
     *
     * If the path is relative, it is calculated relative to the install path
     * of the root package.
     *
     * @param string|null $repoPath The path to the generated resource
     *                              repository or `null` to unset.
     *
     * @throws InvalidConfigException If the path is empty or not a string/`null`.
     */
    public function setGeneratedResourceRepository($repoPath)
    {
        if (!is_string($repoPath) && null !== $repoPath) {
            throw new InvalidConfigException(sprintf(
                'The path to the generated resource repository should be a '.
                'string or null. Got: %s',
                is_object($repoPath) ? get_class($repoPath) : gettype($repoPath)
            ));
        }

        if ('' === $repoPath) {
            throw new InvalidConfigException('The path to the generated resource repository should not be empty.');
        }

        $this->generatedResourceRepository = $repoPath;
    }

    /**
     * Returns the path where the generated resource repository caches its files.
     *
     * If the path is relative, it is calculated relative to the install path
     * of the root package.
     *
     * @return string|null The path to the resource repository cache or `null`
     *                     if none is set.
     */
    public function getResourceRepositoryCache()
    {
        return $this->resourceRepositoryCache;
    }

    /**
     * Sets the path where the generated resource repository caches its files.
     *
     * If the path is relative, it is calculated relative to the install path
     * of the root package.
     *
     * @param string|null $cachePath The path to the resource repository cache
     *                               or `null` to unset.
     *
     * @throws InvalidConfigException If the path is empty or not a string/`null`.
     */
    public function setResourceRepositoryCache($cachePath)
    {
        if (!is_string($cachePath) && null !== $cachePath) {
            throw new InvalidConfigException(sprintf(
                'The path to the resource repository cache should be a string '.
                'or null. Got: %s',
                is_object($cachePath) ? get_class($cachePath) : gettype($cachePath)
            ));
        }

        if ('' === $cachePath) {
            throw new InvalidConfigException('The path to the resource repository cache should not be empty.');
        }

        $this->resourceRepositoryCache = $cachePath;
    }

    /**
     * Returns the plugin classes.
     *
     * @return string[] The fully qualified plugin class names.
     *
     * @see setPluginClasses()
     */
    public function getPluginClasses()
    {
        return array_keys($this->pluginClasses);
    }

    /**
     * Sets the plugin classes.
     *
     * The plugin classes must be fully-qualified class names that implement
     * {@link \Puli\PackageManager\Plugin\PluginInterface}. If a class is not
     * found or does not implement that interface, an exception is thrown.
     *
     * The plugin classes must not have required parameters in their constructor
     * so that the package manager can successfully instantiate them. If a
     * constructor has required parameters, an exception is thrown.
     *
     * Leading backslashes are removed from the fully-qualified class names.
     *
     * @param string[] $pluginClasses The fully qualified plugin class names.
     *
     * @throws InvalidConfigException If a class is not found, is not a class,
     *                                does not implement
     *                                {@link \Puli\PackageManager\Plugin\PluginInterface}
     *                                or has required constructor parameters.
     */
    public function setPluginClasses(array $pluginClasses)
    {
        $this->pluginClasses = array();

        foreach ($pluginClasses as $pluginClass) {
            $this->addPluginClass($pluginClass);
        }
    }

    /**
     * Adds a plugin class.
     *
     * The plugin class must be a fully-qualified class name that implements
     * {@link \Puli\PackageManager\Plugin\PluginInterface}. If the class is not
     * found or does not implement that interface, an exception is thrown.
     *
     * The plugin class must not have required parameters in its constructor
     * so that the package manager can successfully instantiate it. If the
     * constructor has required parameters, an exception is thrown.
     *
     * Leading backslashes are removed from the fully-qualified class name.
     *
     * @param string $pluginClass The fully qualified plugin class name.
     *
     * @throws InvalidConfigException If the class is not found, is not a class,
     *                                does not implement
     *                                {@link \Puli\PackageManager\Plugin\PluginInterface}
     *                                or has required constructor parameters.
     */
    public function addPluginClass($pluginClass)
    {
        try {
            $reflClass = new \ReflectionClass($pluginClass);
        } catch (\ReflectionException $e) {
            throw new InvalidConfigException(sprintf(
                'The plugin class %s does not exist.',
                $pluginClass
            ), 0, $e);
        }

        if ($reflClass->isInterface()) {
            throw new InvalidConfigException(sprintf(
                'The plugin class %s should be a class, but is an interface.',
                $pluginClass
            ));
        }

        if ($reflClass->isTrait()) {
            throw new InvalidConfigException(sprintf(
                'The plugin class %s should be a class, but is a trait.',
                $pluginClass
            ));
        }

        if (!$reflClass->implementsInterface('\Puli\PackageManager\Plugin\PluginInterface')) {
            throw new InvalidConfigException(sprintf(
                'The plugin class %s must implement \Puli\PackageManager\Plugin\PluginInterface.',
                $pluginClass
            ));
        }

        $constructor = $reflClass->getConstructor();

        if (null !== $constructor && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new InvalidConfigException(sprintf(
                'The constructor of the plugin class %s must not have required '.
                'parameters.',
                $pluginClass
            ));
        }

        $this->pluginClasses[ltrim($pluginClass, '\\')] = true;
    }

    /**
     * Removes a plugin class.
     *
     * If the plugin class has not been added, this method does nothing. This
     * method also does not validate whether the passed value is actually a
     * plugin class.
     *
     * Leading backslashes are removed from the fully-qualified class name.
     *
     * @param string $pluginClass The fully qualified plugin class name.
     */
    public function removePluginClass($pluginClass)
    {
        unset($this->pluginClasses[ltrim($pluginClass, '\\')]);
    }
}
