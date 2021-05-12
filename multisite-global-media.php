<?php

// -*- coding: utf-8 -*-
declare(strict_types=1);

/**
 * Plugin Name: Multisite Global Media
 * Description: Multisite Global Media is a WordPress plugin which shares media across the Multisite network.
 * Network:     true
 * Plugin URI:  https://github.com/bueltge/multisite-global-media
 * Version:     0.1.1
 * Author:      Dominik Schilling, Frank Bültge, Guido Scialfa
 * License:     GPLv2+
 * License URI: ./LICENSE
 * Text Domain: multisite-global-media
 * Domain Path: /languages
 *
 * Php Version 7
 *
 * @package WordPress
 * @license https://opensource.org/licenses/GPL-2.0
 * @version 2020-04-22
 */

namespace MultisiteGlobalMedia;

// phpcs:disable

$bootstrap = \Closure::bind(
    static function () {
    /**
     * @param string $message
     * @param string $noticeType
     * @param array $allowedMarkup
     */
    function adminNotice($message, $noticeType, array $allowedMarkup = [])
    {
            \assert(\is_string($message) && \is_string($noticeType));

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
    function autoload()
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
    function isPhpVersionCompatible()
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
}, null);
$bootstrap();
