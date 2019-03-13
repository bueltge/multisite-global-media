<?php // -*- coding: utf-8 -*-
declare(strict_types=1);

/**
 * Plugin Name: Multisite Global Media
 * Description: Multisite Global Media is a WordPress plugin which shares media across the Multisite network.
 * Network:     true
 * Plugin URI:  https://github.com/bueltge/multisite-global-media
 * Version:     0.1.0-dev-3
 * Author:      Dominik Schilling, Frank Bültge, Guido Scialfa
 * License:     MIT
 * License URI: ./LICENSE
 * Text Domain: global_media
 * Domain Path: /languages
 *
 * Php Version 7
 *
 * @package WordPress
 * @author  Dominik Schilling <d.schilling@inpsyde.com>, Frank Bültge <f.bueltge@inpsyde.com>
 * @license https://opensource.org/licenses/MIT
 * @version 2019-01-24
 */

namespace MultisiteGlobalMedia;

(function () {
    /**
     * @param string $message
     * @param string $noticeType
     * @param array $allowedMarkup
     */
    function adminNotice(string $message, string $noticeType, array $allowedMarkup = [])
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

        $autoloader = plugin_dir_path(__FILE__).'/vendor/autoload.php';

        if (!file_exists($autoloader)) {
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
        return PHP_VERSION_ID >= 70000;
    }

    if (!isPhpVersionCompatible()) {
        adminNotice(
            sprintf(
                // Translators: %s is the PHP version of the current installation, where is the plugin is active.
                __(
                    'Multisite Global Media require php version 7.0 at least. Your\'s is %s',
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
})();
