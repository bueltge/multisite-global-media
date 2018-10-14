<?php // -*- coding: utf-8 -*-

/**
 * Plugin Name: Multisite Global Media
 * Description: Multisite Global Media is a WordPress plugin which shares media across the Multisite network.
 * Network:     true
 * Plugin URI:  https://github.com/bueltge/multisite-global-media
 * Version:     0.0.7
 * Author:      Dominik Schilling, Frank Bültge
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
 * @version 2018-09-27
 */

declare(strict_types=1);

namespace MultisiteGlobalMedia;

use MultisiteGlobalMedia\WooCommerce;

// Don't call directly.
defined('ABSPATH') || die();

function autoload()
{
    $autoloader = plugin_dir_path(__FILE__) . '/vendor/autoload.php';

    if (!file_exists($autoloader)) {
        add_action('admin_notices', function () {
            $message = esc_html__('Multisite Global Media:', 'multisite-global-media');
            ?>
            <div class="notice notice-error">
                <p>
                    <?= wp_kses(sprintf(
                    // translators: %s Is the name of the plugin.
                        __(
                            '%s: No autoloader found, plugin cannot load properly.',
                            'multisite-global-media'
                        ),
                        '<strong>' . $message . '</strong>'
                    ), ['strong' => true]) ?>
                </p>
            </div>
            <?php
        });

        return false;
    }

    require_once $autoloader;

    return true;
}

// TODO Check php version
function checkPhpVersion()
{
    return true;
}

function bootstrap()
{
    if (!checkPhpVersion()) {
        return;
    }

    if (!autoload()) {
        return;
    }

    $pluginProperties = new PluginProperties(__FILE__);
    $site = new Site();
    $assets = new Assets($pluginProperties);
    $wooCommerceGallery = new WooCommerce\Gallery($site);

    add_action('admin_enqueue_scripts', [$assets, 'enqueueScripts']);
    add_action('admin_enqueue_scripts', [$assets, 'enqueueStyles']);

    add_action('wp_ajax_query-attachments', __NAMESPACE__ . '\ajaxQueryAttachments', 0);
    add_action('wp_ajax_get-attachment', __NAMESPACE__ . '\ajaxGetAttachment', 0);
    add_action(
        'wp_ajax_send-attachment-to-editor',
        __NAMESPACE__ . '\ajaxSendAttachmentToEditor',
        0
    );
    add_filter('media_view_strings', __NAMESPACE__ . '\mediaStrings');

    add_action('save_post', __NAMESPACE__ . '\saveThumbnailMeta', 99);
    add_action('wp_ajax_get-post-thumbnail-html', __NAMESPACE__ . '\ajaxGetPostThumbnailHtml', 99);
    add_filter('admin_post_thumbnail_html', __NAMESPACE__ . '\adminPostThumbnailHtml', 99, 3);
    add_filter('post_thumbnail_html', __NAMESPACE__ . '\postThumbnailHtml', 99, 5);

    add_action('woocommerce_new_product', [$wooCommerceGallery, 'saveGalleryIds']);
    add_action('woocommerce_update_product', [$wooCommerceGallery, 'saveGalleryIds']);
    add_action(
        'add_meta_boxes',
        [$wooCommerceGallery, 'overrideMetaboxCallback'],
        PHP_INT_MAX
    );
}

add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap');
