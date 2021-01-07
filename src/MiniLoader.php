<?php

namespace IrfanTOOR;

use Exception;

/**
 * Loads the current irfantoor/test package and its dependencies,
 * specially for the classes required for irfantoor/test
 */
class MiniLoader
{
    const NAME        = "MiniLoader";
    const DESCRIPTION = "Loads the current irfantoor/test avoiding the cyclic dependency";
    const VERSION     = "0.1.1";

    protected $root;
    protected $vendor;

    protected $styles = [
        'normal' => 0,
        'dark'   => 2,
        'info'   => 36,
        'error'  => 41,
    ];

    public function __construct(string $root)
    {
        // $this->writeln($root);

        set_exception_handler(function ($e) {
            $this->writeln("Error: " . $e->getMessage(), "error");
            $this->writeln(
                "line: " . ($e->getLine() ?? '') . 
                ", file: " . ($e->getFile() ?? '')
            );
        });

        $this->vendor = $this->vendorPath($root);
        $this->root = dirname($this->vendor) . "/";

        # load composer's autoload to load the classes in the composer.json
        $this->requireFile($this->vendor . "autoload.php");

        # to load current irfantoor/test and the dependencies
        # avoiding the cyclic dependency ;-)
        spl_autoload_register([$this, 'loadClass']);
    }

    public function writeln(string $text, ?string $style = null)
    {
        echo 
            "\033[" . ($this->styles[$style] ?? "2") . "m" . 
            $text . 
            "\033[0m" . PHP_EOL
        ;
    }

    public function vendorPath(string $root)
    {
        for ($i = 5; $i > 0; $i--) {
            $dir = $root . '/vendor/';

            if (is_dir($dir))
                return $dir;

            $root = dirname($root);
        }

        throw new Exception("vendor dir not found!, do a composer update");
    }

    public function loadClass(string $class)
    {
        $relative_class = explode('\\', $class);
        $vendor = strtolower(array_shift($relative_class));
        
        if (!isset($relative_class[0]))
            throw new Exception("class: $class, must contain namespace and package part");
        
        $repo           = $relative_class[0];
        $relative_class = implode("/", $relative_class);

        if (!$relative_class)
            $relative_class = $repo;
        
        $repo = strtolower($repo);
        $package = $vendor . "/" . $repo;
        $file = $this->vendor . $package . "/src/" . $relative_class . ".php";

        if (file_exists($file))
            return $this->requireFile($file);

        $vendor_dir = $this->vendor . $vendor;
        $package_dir = $this->vendor . $package;

        # create vendor dir if it does not exist
        if (!is_dir($vendor_dir))
            if (!mkdir($vendor_dir))
                throw new Exception("failed making dir");

        # copy package if it does not exist
        if (!is_dir($package_dir)) {
            chdir($vendor_dir);
            $this->writeln("Installing: " . $package, "info");
            system('composer create-project --no-install ' . $package);

            if (!is_dir($package_dir))
                throw new Exception(
                    "failed installing: " . $package . 
                    ", check your internet connection"
                );
        }

        # if file does not exist
        return $this->requireFile($file);
    }

    # If a file exists, require it from the file system.
    public function requireFile($file)
    {
        if (file_exists($file)) {
            $f = function () use($file) {
                require $file;
            };

            $f();
            return true;
        }

        return false;
    }
}
