<?php

namespace Eslider;


use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 *
 * @author Andriy Oblivantsev <eslider@gmail.com>
 */
class ScssAsset implements AssetInterface
{
    protected $content;
    protected $filters = array();

    protected $sourceRoute;
    protected $sourcePath;
    protected $sourceDirectory;
    protected $targetPath;

    protected $vars = array();
    protected $values = array();

    /**
     * ScssAsset constructor.
     *
     * @param null $content
     */
    public function __construct($content = null)
    {
        $this->sourceRoute     = realpath(__DIR__ . "/../../../../..");
        $this->sourcePath      = './';
        $this->sourceDirectory = './';
        $this->targetPath      = './';
        $content && $this->setContent($content);
    }

    /**
     * Ensures the current asset includes the supplied filter.
     *
     * @param FilterInterface $filter A filter
     */
    public function ensureFilter(FilterInterface $filter)
    {
        if (!in_array($filter, $this->filters)) {
            $this->load($filter);
        }
    }

    /**
     * Returns an array of filters currently applied.
     *
     * @return FilterInterface[] An array of filters
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Clears all filters from the current asset.
     */
    public function clearFilters()
    {
        $this->filters = array();
    }

    /**
     * Loads the asset into memory and applies load filters.
     *
     * You may provide an additional filter to apply during load.
     *
     * @param FilterInterface $additionalFilter An additional filter
     */
    public function load(FilterInterface $additionalFilter = null)
    {
        $this->filters[] = $additionalFilter;
    }

    /**
     * Applies dump filters and returns the asset as a string.
     *
     * You may provide an additional filter to apply during dump.
     *
     * Dumping an asset should not change its state.
     *
     * If the current asset has not been loaded yet, it should be
     * automatically loaded at this time.
     *
     * @param FilterInterface $additionalFilter An additional filter
     *
     * @return string The filtered content of the current asset
     */
    public function dump(FilterInterface $additionalFilter = null)
    {
        $this->ensureFilter($additionalFilter);
        $filters = $this->getFilters();
        $content = count($filters) ? null : $this->getContent();
        foreach ($filters as $filter) {
            $content = $filter->filterDump($this);
        }
        return $content;
    }

    /**
     * Returns the loaded content of the current asset.
     *
     * @return string The content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the content of the current asset.
     *
     * Filters can use this method to change the content of the asset.
     *
     * @param string $content The asset content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns an absolute path or URL to the source asset's root directory.
     *
     * This value should be an absolute path to a directory in the filesystem,
     * an absolute URL with no path, or null.
     *
     * For example:
     *
     *  * '/path/to/web'
     *  * 'http://example.com'
     *  * null
     *
     * @return string|null The asset's root
     */
    public function getSourceRoot()
    {
        return $this->sourceRoute;
    }

    /**
     * Returns the relative path for the source asset.
     *
     * This value can be combined with the asset's source root (if both are
     * non-null) to get something compatible with file_get_contents().
     *
     * For example:
     *
     *  * 'js/main.js'
     *  * 'main.js'
     *  * null
     *
     * @return string|null The source asset path
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * Returns the asset's source directory.
     *
     * The source directory is the directory the asset was located in
     * and can be used to resolve references relative to an asset.
     *
     * @return string|null The asset's source directory
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }

    /**
     * Returns the URL for the current asset.
     *
     * @return string|null A web URL where the asset will be dumped
     */
    public function getTargetPath()
    {
        return $this->targetPath;
    }

    /**
     * Sets the URL for the current asset.
     *
     * @param string $targetPath A web URL where the asset will be dumped
     */
    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;
    }

    /**
     * Returns the time the current asset was last modified.
     *
     * @return integer|null A UNIX timestamp
     */
    public function getLastModified()
    {
        return time();
    }

    /**
     * Returns an array of variable names for this asset.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Sets the values for the asset's variables.
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = array_merge($this->values, $values);
    }

    /**
     * Returns the current values for this asset.
     *
     * @return array an array of strings
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param string $sourceRoute
     */
    public function setSourceRoute($sourceRoute)
    {
        $this->sourceRoute = $sourceRoute;
    }

    /**
     * @param string $sourcePath
     */
    public function setSourcePath($sourcePath)
    {
        $this->sourcePath = $sourcePath;
    }

    /**
     * @param string $sourceDirectory
     */
    public function setSourceDirectory($sourceDirectory)
    {
        $this->sourceDirectory = $sourceDirectory;
    }

    /**
     * @return string
     */
    public function getSourceRoute()
    {
        return $this->sourceRoute;
    }
}