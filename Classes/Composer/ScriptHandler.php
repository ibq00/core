<?php
declare(strict_types=1);

namespace PhpList\PhpList4\Composer;

use Composer\Script\Event;
use PhpList\PhpList4\Core\ApplicationStructure;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class provides Composer-related functionality for setting up and managing phpList modules.
 *
 * @author Oliver Klee <oliver@phplist.com>
 */
class ScriptHandler
{
    /**
     * @var string
     */
    const CORE_PACKAGE_NAME = 'phplist/phplist4-core';

    /**
     * @return string absolute application root directory without the trailing slash
     *
     * @throws \RuntimeException if there is no composer.json in the application root
     */
    private static function getApplicationRoot(): string
    {
        $applicationStructure = new ApplicationStructure();
        return $applicationStructure->getApplicationRoot();
    }

    /**
     * @return string absolute directory without the trailing slash
     */
    private static function getCoreDirectory(): string
    {
        return self::getApplicationRoot() . '/vendor/' . self::CORE_PACKAGE_NAME;
    }

    /**
     * Creates the "bin/" directory and its contents, copying it from the phplist4-core package.
     *
     * This method must not be called for the phplist4-core package itself.
     *
     * @param Event $event
     *
     * @return void
     *
     * @throws \DomainException if this method is called for the phplist4-core package
     */
    public static function createBinaries(Event $event)
    {
        self::preventScriptFromCorePackage($event);
        self::mirrorDirectoryFromCore('bin');
    }

    /**
     * Creates the "web/" directory and its contents, copying it from the phplist4-core package.
     *
     * This method must not be called for the phplist4-core package itself.
     *
     * @param Event $event
     *
     * @return void
     *
     * @throws \DomainException if this method is called for the phplist4-core package
     */
    public static function createPublicWebDirectory(Event $event)
    {
        self::preventScriptFromCorePackage($event);
        self::mirrorDirectoryFromCore('web');
    }

    /**
     * @param Event $event
     *
     * @return void
     *
     * @throws \DomainException if this method is called for the phplist4-core package
     */
    private static function preventScriptFromCorePackage(Event $event)
    {
        $composer = $event->getComposer();
        $packageName = $composer->getPackage()->getName();
        if ($packageName === self::CORE_PACKAGE_NAME) {
            throw new \DomainException(
                'This Composer script must not be called for the phplist4-core package itself.',
                1501240572934
            );
        }
        echo 'packacge name: ' . $packageName . chr(10);
    }

    /**
     * Copies a directory from the core package.
     *
     * This method overwrites existing files, but will not delete any files.
     *
     * This method must not be called for the phplist4-core package itself.
     *
     * @param string $directoryWithoutSlashes directory name (without any slashes) relative to the core package
     *
     * @return void
     */
    private static function mirrorDirectoryFromCore(string $directoryWithoutSlashes)
    {
        $directoryWithSlashes = '/' . $directoryWithoutSlashes . '/';

        $fileSystem = new Filesystem();
        $fileSystem->mirror(
            self::getCoreDirectory() . $directoryWithSlashes,
            self::getApplicationRoot() . $directoryWithSlashes,
            null,
            ['override' => true, 'delete' => false]
        );
    }
}