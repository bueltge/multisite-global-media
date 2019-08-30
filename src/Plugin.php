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
     * @var PluginProperties
     */
    private $pluginProperties;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var SingleSwitcher
     */
    private $singleSwitcher;

    /**
     * @var Assets
     */
    private $assets;

    /**
     * @var Attachment
     */
    private $attachment;

    /**
     * @var Thumbnail
     */
    private $thumbnail;

    /**
     * Plugin constructor.
     *
     * @param $file
     */
    public function __construct(string $file)
    {
        //init
        $this->rootFile = $file;
        $this->pluginProperties = new PluginProperties($this->rootFile);
        $this->site = new Site();
        $this->singleSwitcher = new SingleSwitcher();
        $this->assets = new Assets($this->pluginProperties);
        $this->attachment = new Attachment($this->site, $this->singleSwitcher);
        $this->thumbnail = new Thumbnail($this->site, $this->singleSwitcher);
    }

    /**
     * Integration the WordPress environment.
     */
    public function onLoad()
    {


        add_action('admin_enqueue_scripts', [$this->assets, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this->assets, 'enqueueStyles']);
        add_action('wp_ajax_query-attachments', [$this->attachment, 'ajaxQueryAttachments'], 0);
        add_action('wp_ajax_get-attachment', [$this->attachment, 'ajaxGetAttachment'], 0);
        add_action('wp_ajax_send-attachment-to-editor', [$this->attachment, 'ajaxSendAttachmentToEditor'], 0);
        add_action('wp_get_attachment_image_src', [$this->attachment, 'attachmentImageSrc'], PHP_INT_MAX, 4);
        add_filter('media_view_strings', [$this->attachment, 'mediaStrings']);

        add_action('save_post', [$this->thumbnail, 'saveThumbnailMeta'], PHP_INT_MAX);
        add_action('wp_ajax_get-post-thumbnail-html', [$this->thumbnail, 'ajaxGetPostThumbnailHtml'], PHP_INT_MAX);
        add_filter('admin_post_thumbnail_html', [$this->thumbnail, 'adminPostThumbnailHtml'], PHP_INT_MAX, 3);
        add_filter('post_thumbnail_html', [$this->thumbnail, 'postThumbnailHtml'], PHP_INT_MAX, 5);


        // todo for now we dont support gutenberg editor :D
        //disable gutenberg for posts
        add_filter('use_block_editor_for_post', '__return_false', 10);
        //disable gutenberg for post types
        add_filter('use_block_editor_for_post_type', '__return_false', 10);


        add_action('plugins_loaded', [$this, 'checkPluginsActive']);


    }

    /**
     * When WP has loaded all plugins, check wooCommerce is active.
     */
    public function checkPluginsActive()
    {
        if (defined('WC_VERSION')) {
            $this->wcBootstrap($this->site, $this->singleSwitcher);
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
