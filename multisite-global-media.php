<?php // -*- coding: utf-8 -*-
declare(strict_types=1);

/**
 * Plugin Name: Multisite Global Media
 * Description: Multisite Global Media is a WordPress plugin which shares media across the Multisite network.
 * Network:     true
 * Plugin URI:  https://github.com/bueltge/multisite-global-media
 * Version:     0.1.0-dev-2
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
 * @version 2018-11-05
 */

namespace MultisiteGlobalMedia;

use MultisiteGlobalMedia\WooCommerce;

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
    $autoloader = plugin_dir_path(__FILE__) . '/vendor/autoload.php';

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

/**
 * Bootstrap the plugin
 */
function bootstrap()
{
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

    $pluginProperties = new PluginProperties(__FILE__);
    $site = new Site();
    $singleSwitcher = new SingleSwitcher();

    $assets = new Assets($pluginProperties);
    $attachment = new Attachment($site, $singleSwitcher);
    $thumbnail = new Thumbnail($site, $singleSwitcher);

    add_action('admin_enqueue_scripts', [$assets, 'enqueueScripts']);
    add_action('admin_enqueue_scripts', [$assets, 'enqueueStyles']);
    add_action('wp_ajax_query-attachments', [$attachment, 'ajaxQueryAttachments'], 0);
    add_action('wp_ajax_get-attachment', [$attachment, 'ajaxGetAttachment'], 0);
    add_action('wp_ajax_send-attachment-to-editor', [$attachment, 'ajaxSendAttachmentToEditor'], 0);
    add_action('wp_get_attachment_image_src', [$attachment, 'attachmentImageSrc'], 99, 4);
    add_filter('media_view_strings', [$attachment, 'mediaStrings']);

    add_action('save_post', [$thumbnail, 'saveThumbnailMeta'], 99);
    add_action('wp_ajax_get-post-thumbnail-html', [$thumbnail, 'ajaxGetPostThumbnailHtml'], 99);
    add_filter('admin_post_thumbnail_html', [$thumbnail, 'adminPostThumbnailHtml'], 99, 3);
    add_filter('post_thumbnail_html', [$thumbnail, 'postThumbnailHtml'], 99, 5);

    if (\function_exists('wc')) {
        wcBootstrap($site, $singleSwitcher);
    }
}

/**
 * @param Site $site
 * @param SingleSwitcher $siteSwitcher
 */
function wcBootstrap(Site $site, SingleSwitcher $siteSwitcher)
{
    $wooCommerceGallery = new WooCommerce\Gallery($site, $siteSwitcher);

    add_action('woocommerce_new_product', [$wooCommerceGallery, 'saveGalleryIds']);
    add_action('woocommerce_update_product', [$wooCommerceGallery, 'saveGalleryIds']);
    add_action('add_meta_boxes', [$wooCommerceGallery, 'overrideMetaboxCallback'], PHP_INT_MAX);
    add_action(
        'woocommerce_single_product_image_thumbnail_html',
        [$wooCommerceGallery, 'singleProductImageThumbnailHtml'],
        PHP_INT_MAX,
        2
    );
}

add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap');
