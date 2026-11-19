<?php

/**
 * Plugin Name: Multisite Global Media
 * Description: Multisite Global Media is a WordPress plugin which shares media across the Multisite network.
 * Network:     true
 * Plugin URI:  https://github.com/bueltge/multisite-global-media
 * Version:     0.4.0
 * Author:      Dominik Schilling, Frank Bültge, Guido Scialfa
 * License:     GPLv2+
 * License URI: ./LICENSE
 * Text Domain: multisite-global-media
 * Domain Path: /languages
 * Requires PHP: 8.3
 *
 * @package WordPress
 * @license https://opensource.org/licenses/GPL-2.0
 * @version 2026-11-19
 */

declare(strict_types=1);

namespace MultisiteGlobalMedia;

// phpcs:disable

$bootstrap = \Closure::bind(
    static function () {
    /**
     * @param array<string, array<string, bool>> $allowedMarkup
     */
    function adminNotice(string $message, string $noticeType, array $allowedMarkup = []): void
    {
            add_action(
            'admin_notices',
            function () use ($message, $noticeType, $allowedMarkup) {
                ?>
                <div class="notice notice-<?= esc_attr($noticeType) ?>">
                    <p><?= wp_kses($message, $allowedMarkup) ?></p>
                </div>
                <?php
                }
                );
    }

    /**
     * @return bool
     */
    function autoload(): bool
    {
            if (\class_exists(PluginProperties::class)) {
                return true;
                }

            $autoloader = plugin_dir_path(__FILE__) . '/vendor/autoload.php';

            if (!\file_exists($autoloader)) {
                return false;
                }

            /** @noinspection PhpIncludeInspection */
            require_once $autoloader;

            return true;
    }

    /**
     * Compare PHP Version with our minimum.
     *
     * @return bool
     */
    function isPhpVersionCompatible(): bool
    {
            return version_compare(PHP_VERSION, '8.3', '>=');
    }

    if (!isPhpVersionCompatible()) {
            adminNotice(
            sprintf(
            // Translators: %s is the PHP version of the current installation, where is the plugin is active.
                __(
                    'Multisite Global Media require php version 8.3 at least. Your\'s is %s',
                    'multisite-global-media'
                ),
                PHP_VERSION
            ),
            'error'
                );

                return;
    }
    if (!autoload()) {
            adminNotice(
            __(
                'No suitable autoloader found. Multisite Global Media cannot be loaded correctly.',
                'multisite-global-media'
            ),
            'error'
                );

                return;
    }

    $plugin = new Plugin(__FILE__);
    $plugin->onLoad();
}, null);
$bootstrap();
