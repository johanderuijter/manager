<?php

/*
 * This file is part of the puli/repository-manager package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\RepositoryManager\Package;

use Exception;
use Puli\RepositoryManager\Assert\Assert;
use Puli\RepositoryManager\Package\PackageFile\PackageFile;

/**
 * A configured package.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Package
{
    /**
     * The name given to packages by default.
     */
    const DEFAULT_NAME = null;

    /**
     * @var string
     */
    private $name;

    /**
     * @var PackageFile
     */
    private $packageFile;

    /**
     * @var string
     */
    private $installPath;

    /**
     * @var InstallInfo
     */
    private $installInfo;

    /**
     * @var int
     */
    private $state = PackageState::NOT_LOADED;

    /**
     * @var Exception|null
     */
    private $loadError;

    /**
     * Creates a new package.
     *
     * @param PackageFile|null $packageFile The package file or `null` if the
     *                                      package file could not be loaded.
     * @param string           $installPath The absolute install path.
     * @param InstallInfo      $installInfo The install info of this package.
     */
    public function __construct(PackageFile $packageFile = null, $installPath, InstallInfo $installInfo = null, Exception $loadError = null)
    {
        Assert::absoluteSystemPath($installPath);
        Assert::true($packageFile || $loadError, 'The load error must be passed if the package file is null.');

        // If a package name was set during installation, that name wins over
        // the predefined name in the puli.json file (if any)
        $this->name = $installInfo && null !== $installInfo->getPackageName()
            ? $installInfo->getPackageName()
            : $packageFile->getPackageName();

        if (null === $this->name) {
            $this->name = static::DEFAULT_NAME;
        }

        // The path is stored both here and in the install info. While the
        // install info contains the path as it is stored in the install file
        // (i.e. relative or absolute), the install path of the package is
        // always an absolute path.
        $this->installPath = $installPath;
        $this->installInfo = $installInfo;
        $this->packageFile = $packageFile;
        $this->loadError = $loadError;

        $this->state = PackageState::detect($this);
    }

    /**
     * Returns the name of the package.
     *
     * @return string The name of the package.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the absolute path at which the package is installed.
     *
     * @return string The absolute install path of the package.
     */
    public function getInstallPath()
    {
        return $this->installPath;
    }

    /**
     * Returns the package file of the package.
     *
     * @return PackageFile|null The package file or `null` if the file could not
     *                          be loaded.
     */
    public function getPackageFile()
    {
        return $this->packageFile;
    }

    /**
     * Returns the package's install info.
     *
     * @return InstallInfo The install info.
     */
    public function getInstallInfo()
    {
        return $this->installInfo;
    }

    /**
     * Returns the error that occurred during loading of the package.
     *
     * @return Exception|null The exception or `null` if the package was loaded
     *                        successfully.
     */
    public function getLoadError()
    {
        return $this->loadError;
    }

    /**
     * Returns the state of the package.
     *
     * @return int One of the {@link PackageState} constants.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Resets the state of the package to unloaded.
     */
    public function resetState()
    {
        $this->state = PackageState::NOT_LOADED;
    }

    /**
     * Refreshes the state of the package.
     */
    public function refreshState()
    {
        $this->state = PackageState::detect($this);
    }

    /**
     * Returns whether the package is loaded.
     *
     * @return bool Returns `true` if the state is not
     *              {@link PackageState::NOT_LOADED}.
     *
     * @see PackageState::NOT_LOADED
     */
    public function isLoaded()
    {
        return PackageState::NOT_LOADED !== $this->state;
    }

    /**
     * Returns whether the package is enabled.
     *
     * @return bool Returns `true` if the state is {@link PackageState::ENABLED}.
     *
     * @see PackageState::ENABLED
     */
    public function isEnabled()
    {
        return PackageState::ENABLED === $this->state;
    }

    /**
     * Returns whether the package was not found.
     *
     * @return bool Returns `true` if the state is {@link PackageState::NOT_FOUND}.
     *
     * @see PackageState::NOT_FOUND
     */
    public function isNotFound()
    {
        return PackageState::NOT_FOUND === $this->state;
    }

    /**
     * Returns whether the package was not loadable.
     *
     * @return bool Returns `true` if the state is {@link PackageState::NOT_LOADABLE}.
     *
     * @see PackageState::NOT_LOADABLE
     */
    public function isNotLoadable()
    {
        return PackageState::NOT_LOADABLE === $this->state;
    }
}
