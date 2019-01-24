<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

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
            $this->wcBootstrap($site, $singleSwitcher);
        }
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
}
