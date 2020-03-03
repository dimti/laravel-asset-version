<?php

namespace Tooleks\LaravelAssetVersion\Contracts;

/**
 * Interface AssetServiceContract.
 *
 * @package Tooleks\LaravelAssetVersion\Contracts
 * @author Oleksandr Tolochko <tooleks@gmail.com>
 */
interface AssetServiceContract
{
    /**
     * AssetServiceContract constructor.
     *
     * @param string $version
     * @param bool|null $secure
     * @param bool $automatic_versioning
     * @param array $paths
     */
    public function __construct(string $version, ?bool $secure = null, bool $automatic_versioning = false, array $paths = []);

    /**
     * Get assets version number.
     *
     * @return string
     */
    public function getVersion() : string;

    /**
     * Set assets version number.
     *
     * @param string $version
     */
    public function setVersion(string $version);

    /**
     * Get assets version number.
     *
     * @return string
     */
    public function getVersioning() : bool;

    /**
     * Set assets version number.
     *
     * @param string $autoversioning
     */
    public function setVersioning(bool $autoversioning);

    /**
     * Get secure option.
     *
     * @return bool|null
     */
    public function getSecure();

    /**
     * Set secure option.
     *
     * @param bool $secure
     */
    public function setSecure(bool $secure);

    /**
     * Generate an asset path with version parameter for the application.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function get(string $path, bool $secure = null) : string;
}
