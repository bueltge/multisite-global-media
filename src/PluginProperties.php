<?php

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * @method string basename()
 * @method string dirPath()
 * @method string dirUrl()
 * @method string filePath()
 * @method string name()
 * @method string website()
 * @method string version()
 * @method string textDomain()
 * @method string textDomainPath()
 *
 * @implements \ArrayAccess<string, string>
 */
class PluginProperties implements \ArrayAccess
{
    public const BASENAME = 'basename';
    public const DIR_PATH = 'dirPath';
    public const DIR_URL = 'dirUrl';
    public const FILE_PATH = 'filePath';
    public const NAME = 'name';
    public const WEBSITE = 'website';
    public const VERSION = 'version';
    public const TEXT_DOMAIN = 'textDomain';
    public const TEXT_DOMAIN_PATH = 'textDomainPath';

    /**
     * @var array<string, string>
     */
    private array $properties;

    /**
     * @param string $pluginFilePath
     */
    public function __construct(string $pluginFilePath)
    {
        $fileData = [
            self::BASENAME => plugin_basename($pluginFilePath),
            self::DIR_PATH => untrailingslashit(plugin_dir_path($pluginFilePath)),
            self::DIR_URL => untrailingslashit(plugins_url('/', $pluginFilePath)),
            self::FILE_PATH => $pluginFilePath,
        ];

        $headerData = get_file_data(
            $pluginFilePath,
            [
                self::NAME => 'Plugin Name',
                self::WEBSITE => 'Plugin URI',
                self::VERSION => 'Version',
                self::TEXT_DOMAIN => 'Text Domain',
                self::TEXT_DOMAIN_PATH => 'Domain Path',
            ]
        );

        $this->properties = array_map('\strval', array_merge($fileData, $headerData));
    }

    /**
     * @param string $name
     * @param array<mixed> $args
     * @return string
     */
    public function __call(string $name, array $args = []): string
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new \Error(
                sprintf(
                    'Call to undefined method %s::%s()',
                    __CLASS__,
                    esc_html($name)
                )
            );
        }

        return $this->properties[$name];
    }

    /**
     * Checks if a property with the given name exists.
     *
     * @param string $name
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Returns the value of the property with the given name.
     *
     * @param string $offset
     * @return mixed
     * @throws \OutOfRangeException If there is no property with the given name.
     */
    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException(esc_html("'{$offset}' is not a valid plugin property."));
        }

        return $this->properties[$offset];
    }

    /**
     * Disabled.
     *
     * @inheritdoc
     *
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(
            __METHOD__ . ' is not allowed. ' . __CLASS__ . ' is read only.'
        );
    }

    /**
     * Disabled.
     *
     * @inheritdoc
     *
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(
            __METHOD__ . ' is not allowed. ' . __CLASS__ . ' is read only.'
        );
    }
}
