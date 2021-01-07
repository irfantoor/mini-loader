<?php

# todo -- add tests to ensure at least an 80% of the code coverage

use IrfanTOOR\Test;
use IrfanTOOR\MiniLoader;

class MiniLoaderTest extends Test
{
    protected $loader;

    function testInstance()
    {
        # so that the autoload is from this dir in the testLoadClass
        # its not so unit, but well, for the time being.
        $d = __DIR__ . "/classes/vendor/alfa/beta";
        $l = $this->loader = new MiniLoader($d);

        $this->assertInstanceOf(MiniLoader::class, $l);
    }

    function testMiniLoaderCanWriteln()
    {
        $l = $this->loader;

        ob_start();
        $l->writeln('Hello World!');
        $output = ob_get_clean();

        $this->assertEquals("\033[2mHello World!\033[0m\n", $output);

        ob_start();
        $l->writeln('Hello World!', 'red');
        $output = ob_get_clean();

        $this->assertNotEquals("\033[41mHello World!\033[0m\n", $output);
        $p = strpos($output, 'Hello World!');
        $this->assertNotZero($p);
        $this->assertInt($p);
    }

    function testVendorPath()
    {
        $d = __DIR__;
        $vd = dirname($d) . "/vendor/";
        $l = $this->loader;
        
        $this->assertEquals($vd, $l->vendorPath($d));

        $d = $vd . "irfantoor/test/src";
        $this->assertEquals($vd, $l->vendorPath($d));
    }

    /**
     * throws: Exception::class
     * message: vendor dir not found!, do a composer update
     */
    function testVendorPathNotFound()
    {
        $l = $this->loader;
        $d = dirname(dirname(__DIR__));
        $l->vendorPath($d);
    }

    function testLoadClass()
    {
        $d = __DIR__ . "/classes/vendor/alfa/beta";
        $l = new MiniLoader($d);
        $this->assertTrue($l->loadClass(Alfa\Beta::class));

        $class = new Alfa\Beta();
        $this->assertInstanceOf(Alfa\Beta::class, $class);
        
        $class = new Alfa\Beta\Gamma();
        $this->assertInstanceOf(Alfa\Beta\Gamma::class, $class);

        $this->assertFalse($l->loadClass(Alfa\Beta\Zeta::class));
    }

    /**
     * throws: Exception::class
     * message: class: RequireFile, must contain namespace and package part
     */
    function testFileWithNoNamespaceCanNotBeLoaded()
    {
        $class = new RequireFile();
    }

    function testRequireFile()
    {
        $l = $this->loader;
        $file = __DIR__ . "/classes/RequireFile.php";
        $result = $l->requireFile($file);
        $this->assertTrue($result);

        $class = new RequireFile();
        $this->assertInstanceOf(RequireFile::class, $class);
        $text = "Hello World!";
        $this->assertEquals($text, $class->echo($text));
    }
}
