<?php

namespace Tooleks\LaravelAssetVersion\Services;

use Illuminate\Support\Arr;
use Tooleks\LaravelAssetVersion\Contracts\AssetServiceContract;

/**
 * Class AssetService.
 *
 * @package Tooleks\LaravelAssetVersion\Services
 * @author Oleksandr Tolochko <tooleks@gmail.com>
 */
class AssetService implements AssetServiceContract
{
    /**
     * Asset version number.
     *
     * @var string
     */
    protected $version;

    /**
     * Asset secure option.
     *
     * @var bool|null
     */
    protected $secure;

    /**
     * Auto generate versioning number from files instead of using version number
     *
     * @var bool|null
     */
    protected $autoversioning;

    /**
     * Asset paths option.
     *
     * @var bool|null
     */
    protected $paths = [];

    /**
     * @inheritdoc
     */
    public function __construct(?string $version, ?bool $secure = null, bool $automatic_versioning = false, array $paths = [])
    {
        $this->version = $version;
        $this->secure = $secure;
        $this->autoversioning = $automatic_versioning;
        $this->paths = $paths;
    }

    /**
     * @inheritdoc
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
    }

    /**
     * @inheritdoc
     */
    public function getVersioning() : bool
    {
        return $this->autoversioning;
    }

    /**
     * @inheritdoc
     */
    public function setVersioning(bool $autoversioning)
    {
        $this->autoversioning = $autoversioning;
    }

    /**
     * @inheritdoc
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @inheritdoc
     */
    public function setSecure(bool $secure)
    {
        $this->secure = $secure;
    }

    /**
     * @inheritdoc
     */
    public function get(string $path, bool $secure = null) : string
    {
        if ($secure === null) {
            $secure = $this->getSecure();
        }

        return asset($this->appendVersionToPath($path), $secure);
    }

    /**
     * Append version parameter to the asset path.
     *
     * @param string $path
     * @return string
     */
    protected function appendVersionToPath(string $path) : string
    {
        if (!$this->autoversioning) {
            return ($this->version) ? ($path . '?v=' . $this->version) : ($path);
        }

        $path_url = parse_url($path);
        $version = $this->getFileVersion($path, $path_url);

        if ($version) {
            if (!isset($path_url['query']) || empty($path_url['query'])) {
                $path = sprintf('%s?v=%s', $path_url['path'], $version);
            } else {
                $path = sprintf('%s?%s&v=%s', $path_url['path'], $path_url['query'], $version);
            }
        }

        return $path;
    }

    protected function getFileVersion(string $path, array $path_url) : string
    {
        //Anything directly linking to a http or // path should use default version
        //If auto versioning is off, just return version.
        if (preg_match('#^(//|http)#i', $path)) {
            return ($this->version ? $this->version : '');
        }

        //Just in case the / is missing from the start of the path
        if (!preg_match('#^/#', $path_url['path'])) {
            $path_url['path'] = '/' . $path_url['path'];
        }

        //Grab the real system path and get the modified time of the file
        //TODO: Generate a cache on live so that filemtime doesn't have to be ran each page load
        $rel_path = $this->findRealPath($path_url['path']);

        /** @var $cache \Illuminate\Cache\MemcachedStore */
        //$cache = \Cache::store(config('assets.cachestore', 'memcached'));
        //$cache->setPrefix('assets');

        if ($rel_path) {
            return filemtime($rel_path);
        } elseif ($this->version) {
            return $this->version;
        }
        return '';
    }

    /**
     * @param string $path
     * @return null|string
     */
    public function findRealPath(string $path): ?string
    {
        $rel_path = public_path($path);
        if (is_readable($rel_path)) {
            return $rel_path;
        }
        foreach($this->paths as $_path) {
            $check = app()->basePath() . '/' . $_path;
            if (is_readable($check)) {
                return $check;
            }
        }
        return null;
    }
}
