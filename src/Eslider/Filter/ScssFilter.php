<?php

namespace Eslider\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Filter\Sass\SassFilter;

/**
 *
 * @author Andriy Oblivantsev <eslider@gmail.com>
 */
class ScssFilter extends SassFilter
{
    private $sassPath;
    private $unixNewlines;
    private $scss;
    private $style;
    private $precision;
    private $quiet;
    private $debugInfo;
    private $lineNumbers;
    private $sourceMap;
    private $cacheLocation;
    private $noCache;

    /**
     * @var string Operation system short name
     */
    protected $osName;

    /**
     * ScssFilter constructor.
     *
     * @param string $binPath Bin path.
     */
    public function __construct($binPath = null)
    {
        $binName = "sassc";
        $is32bit = PHP_INT_SIZE <= 4;
        $binPath || $binPath = realpath(__DIR__ . "/../../../dist");
        $this->osName = strtoupper(substr(PHP_OS, 0, 3));

        switch ($this->osName) {
            // WINNT, CYGWIN_NT-5.1 Windows, Windows Server, etc.
            case 'CYG':
            case 'WIN':
                $binName .= ".exe";
                break;

            // Darwin
            case "DAR":
            case "MAC":
                $binName .= ".macosx";
                break;

            // FreeBSD
            //case 'FRE':
            //case 'LIN':
            default:
                $is32bit && $binName .= ".x86";
                break;
        }
        $this->sassPath = $binPath . '/' . $binName;
        parent::__construct($this->sassPath);
        $this->setScss(true);
    }

    /**
     * @param AssetInterface $asset
     */
    public function filterLoad(AssetInterface $asset)
    {
        if ($this->isWindows()) {
            $asset->setContent($this->generateCssOnWindows($asset));
        } else {
            $asset->setContent($this->generateCssOnLinux($asset));
        }
    }

    /**
     * @param AssetInterface $asset
     * @return string CSS
     */
    public function generateCssOnWindows(AssetInterface $asset)
    {
        $css             = "";
        $sassProcessArgs = array(escapeshellarg($this->sassPath));
        foreach ($this->loadPaths as $loadPath) {
            $sassProcessArgs[] = '--load-path';
            $sassProcessArgs[] = escapeshellarg($loadPath);
        }

        $scss    = $asset->getContent();
        $tempDir = sys_get_temp_dir();
        $temp    = tempnam($tempDir, "scss");
        file_put_contents($temp, $scss);
        $sassProcessArgs[] = escapeshellarg($temp);
        $cmd               = implode(" ", $sassProcessArgs);
        $css               = `$cmd`;
        unlink($temp);
        return $css;
    }

    /**
     * @param AssetInterface $asset
     * @return string CSS
     */
    public function generateCssOnLinux($asset)
    {
        $sassProcessArgs = array($this->sassPath);
        $pb              = $this->createProcessBuilder($sassProcessArgs);

        if ($dir = $asset->getSourceDirectory()) {
            $pb->add('--load-path')->add($dir);
        }

        if ($this->unixNewlines) {
            $pb->add('--unix-newlines');
        }

        if ($this->style) {
            $pb->add('--style')->add($this->style);
        }

        if ($this->precision) {
            $pb->add('--precision')->add($this->precision);
        }

        if ($this->quiet) {
            $pb->add('--quiet');
        }

        if ($this->debugInfo) {
            $pb->add('--debug-info');
        }

        if ($this->lineNumbers) {
            $pb->add('--line-numbers');
        }

        if ($this->sourceMap) {
            $pb->add('--sourcemap');
        }

        foreach ($this->loadPaths as $loadPath) {
            $pb->add('--load-path')->add($loadPath);
        }

        if ($this->cacheLocation) {
            $pb->add('--cache-location')->add($this->cacheLocation);
        }

        if ($this->noCache) {
            $pb->add('--no-cache');
        }

        //  Read input from standard input instead of an input file.
        $pb->add('-s');
        $pb->setInput($asset->getContent());

        $proc = $pb->getProcess();
        $code = $proc->run();

        if ($code !== 0) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        return $proc->getOutput();
    }

    /**
     * @return bool
     */
    protected function isWindows()
    {
        return $this->osName == 'CYG' || $this->osName == 'WIN';
    }
}
