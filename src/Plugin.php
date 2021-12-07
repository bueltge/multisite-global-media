<?php

# -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia;

use MultisiteGlobalMedia\ACF\Image;
use MultisiteGlobalMedia\Rest\Rest;
use MultisiteGlobalMedia\WooCommerce;

class Plugin
{

    /**
     * @var string $rootFile
     */
    private $rootFile;

    /**
     * Plugin constructor.
     *
     * @param $file
     */
    public function __construct(string $file)
    {
        $this->rootFile = $file;
    }

    /**
     * Integration the WordPress environment.
     */
    public function onLoad()
    {
        $pluginProperties = new PluginProperties($this->rootFile);
        $site = new Site();
        $singleSwitcher = new SingleSwitcher();

        $assets = new Assets($pluginProperties);
        $attachment = new Attachment($site, $singleSwitcher);
        $thumbnail = new Thumbnail($site, $singleSwitcher);
        $rest = new Rest($site);

        add_action('admin_enqueue_scripts', [$assets, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$assets, 'enqueueStyles']);
        add_action('wp_ajax_query-attachments', [$attachment, 'ajaxQueryAttachments'], 0);
        add_action('wp_ajax_get-attachment', [$attachment, 'ajaxGetAttachment'], 0);
        add_action('wp_ajax_send-attachment-to-editor', [$attachment, 'ajaxSendAttachmentToEditor'], 0);
        add_filter('wp_get_attachment_image_src', [$attachment, 'attachmentImageSrc'], 99, 4);
        add_filter('media_view_strings', [$attachment, 'mediaStrings']);

        remove_filter('the_content', 'wp_make_content_images_responsive');
        add_filter('the_content', [$attachment, 'makeContentImagesResponsive']);

        add_action('save_post', [$thumbnail, 'saveThumbnailMeta'], 99);
        add_action('wp_ajax_get-post-thumbnail-html', [$thumbnail, 'ajaxGetPostThumbnailHtml'], 99);
        add_filter('admin_post_thumbnail_html', [$thumbnail, 'adminPostThumbnailHtml'], 99, 3);
        add_filter('post_thumbnail_html', [$thumbnail, 'postThumbnailHtml'], 99, 5);

        add_filter('register_post_type_args', [$rest, 'registerPostTypeArgs'], 10, 2);
        add_filter('rest_request_after_callbacks', [$rest, 'restRequestAfterCallbacks'], 10, 3);

        if (\function_exists('wc')) {
            $this->wcBootstrap($site, $singleSwitcher);
        }

        $this->acfBootstrap($site, $singleSwitcher);
    }

    /**
     * Integration for WooCommerce and his gallery support.
     *
     * @param Site $site
     * @param SingleSwitcher $siteSwitcher
     */
    public function wcBootstrap(Site $site, SingleSwitcher $siteSwitcher)
    {
        $wooCommerceGallery = new WooCommerce\Gallery($site, $siteSwitcher);

        add_action('woocommerce_new_product', [$wooCommerceGallery, 'saveGalleryIds']);
        add_action('woocommerce_update_product', [$wooCommerceGallery, 'saveGalleryIds']);
    }

    public function acfBootstrap(Site $site, SiteSwitcher $siteSwitcher)
    {
        if (!\function_exists('get_field')) {
            return;
        }

        // ACF can be included within a theme too - check in after_setup_theme action
        // https://www.advancedcustomfields.com/resources/including-acf-within-a-plugin-or-theme/
        \add_action('after_setup_theme', function() use($site, $siteSwitcher) {
            $store = acf_get_store('values');
            $this->acfAfterSetupTheme($site, $siteSwitcher, $store);
        });
    }

    public function acfAfterSetupTheme(Site $site, SiteSwitcher $siteSwitcher, \ACF_Data $store) {
        $image = new Image($site, $siteSwitcher, $store);
        \add_filter('acf/load_value/type=image', array($image, 'acfLoadValue'), 10, 3);
    }
}
