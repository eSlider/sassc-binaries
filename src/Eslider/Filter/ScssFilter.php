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
     * ScssFilter constructor.
     *
     * @param string $binPath Bin path.
     */
    public function __construct($binPath = null)
    {
        $binName = "sassc";
        $is32bit = PHP_INT_SIZE <= 4;
        $binPath || $binPath = realpath(__DIR__ . "/../../../dist");

        switch (strtoupper(substr(PHP_OS, 0, 3))) {
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

        $asset->setContent($proc->getOutput());
    }
}