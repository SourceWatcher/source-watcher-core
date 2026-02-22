<?php declare( strict_types=1 );

namespace Coco\SourceWatcher\Tests\Utils;

use Coco\SourceWatcher\Utils\FileUtils;
use PHPUnit\Framework\TestCase;

class FileUtilsTest extends TestCase
{
    public function testFileBuildPath () : void
    {
        $path = FileUtils::file_build_path( "a", "b", "c" );
        $this->assertSame( "a" . DIRECTORY_SEPARATOR . "b" . DIRECTORY_SEPARATOR . "c", $path );
    }

    public function testFileBuildPathSingleSegment () : void
    {
        $path = FileUtils::file_build_path( "only" );
        $this->assertSame( "only", $path );
    }

    public function testGetUserHomePath () : void
    {
        $home = FileUtils::getUserHomePath();
        $this->assertIsString( $home );
    }

    public function testGetUserHomePathWithHomeSet () : void
    {
        $original = getenv( "HOME" );
        putenv( "HOME=/fake/home" );
        try {
            $home = FileUtils::getUserHomePath();
            $this->assertSame( "/fake/home", $home );
        } finally {
            putenv( $original !== false ? "HOME=" . $original : "HOME" );
        }
    }

    public function testGetUserHomePathReturnsEmptyWhenNoHome () : void
    {
        $original = getenv( "HOME" );
        putenv( "HOME=" );
        unset( $_SERVER["HOMEDRIVE"], $_SERVER["HOMEPATH"] );
        try {
            $home = FileUtils::getUserHomePath();
            $this->assertSame( "", $home );
        } finally {
            putenv( $original !== false ? "HOME=" . $original : "HOME" );
        }
    }

    public function testGetUserHomePathUsesWindowsDriveAndPathWhenHomeEmpty () : void
    {
        $originalHome = getenv( "HOME" );
        putenv( "HOME=" );
        $_SERVER["HOMEDRIVE"] = "C:";
        $_SERVER["HOMEPATH"] = "\\Users\\test";
        try {
            $home = FileUtils::getUserHomePath();
            $this->assertSame( "C:\\Users\\test", $home );
        } finally {
            putenv( $originalHome !== false ? "HOME=" . $originalHome : "HOME" );
            unset( $_SERVER["HOMEDRIVE"], $_SERVER["HOMEPATH"] );
        }
    }
}
