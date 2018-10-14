<?php # -*- coding: utf-8 -*-

namespace MultisiteGlobalMedia\WooCommerce;

use function MultisiteGlobalMedia\Functions\getSideId;
use MultisiteGlobalMedia\Site;

/**
 * Class Gallery
 *
 * @package MultisiteGlobalMedia\WooCommerce
 */
class Gallery
{
    const FILTER_ATTACHMENT_IMAGE_SRC = 'wp_get_attachment_image_src';

    const DEFAULT_PRODUCT_TYPE = 'simple';
    const POST_PRODUCT_TYPE = 'product-type';
    const POST_PRODUCT_IMAGE_GALLERY = 'product_image_gallery';

    const META_KEY_PRODUCT_GALLERY = '_product_image_gallery';

    private $site;

    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Save Gallery Ids into product meta
     *
     * @param int $productId
     */
    public function saveGalleryIds(int $productId)
    {
        $productType = \WC_Product_Factory::get_product_type($productId);
        $requestProductType = filter_input(
            INPUT_POST,
            self::POST_PRODUCT_TYPE,
            FILTER_SANITIZE_STRING
        );

        $requestProductType and $productType = sanitize_title(stripslashes($requestProductType));

        $productType = $productType ?: self::DEFAULT_PRODUCT_TYPE;
        $classname = \WC_Product_Factory::get_product_classname($productId, $productType);
        /** @var \WC_Product $product */
        $product = new $classname($productId);
        $attachmentIds = filter_input(
            INPUT_POST,
            self::POST_PRODUCT_IMAGE_GALLERY,
            FILTER_SANITIZE_STRING
        );

        update_post_meta($product->get_id(), self::META_KEY_PRODUCT_GALLERY, $attachmentIds);
        update_post_meta($product->get_id(), Site::META_KEY_SITE_ID, $this->site->id());
    }

    /**
     * Override Metabox Callback for product gallery metabox
     */
    public function overrideMetaboxCallback()
    {
        global $wp_meta_boxes;

        $metaboxCallback =& $wp_meta_boxes['product']['side']['low']['woocommerce-product-images'] ?? null;

        if (!$metaboxCallback) {
            return;
        }

        $metaboxCallback = function (\WP_Post $post) {
            add_filter(
                self::FILTER_ATTACHMENT_IMAGE_SRC,
                [$this, 'retrieveTheImages'],
                PHP_INT_MAX,
                4
            );

            \WC_Meta_Box_Product_Images::output($post);

            remove_filter(
                self::FILTER_ATTACHMENT_IMAGE_SRC,
                [$this, 'retrieveTheImages'],
                PHP_INT_MAX,
                4
            );
        };
    }

    /**
     * Retrieve the images from the global site
     *
     * @param string $image
     * @param int $thumbnailId
     * @param $size
     * @param bool $icon
     * @return string
     */
    public function retrieveTheImages(
        string $image,
        int $thumbnailId,
        $size,
        bool $icon
    ): string {

        remove_filter(
            self::FILTER_ATTACHMENT_IMAGE_SRC,
            [$this, 'retrieveTheImages'],
            PHP_INT_MAX,
            4
        );

        $thumbnailId = (string)$thumbnailId;
        $post = get_post((int)filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT));
        $siteId = (int)get_post_meta($post->ID, Site::META_KEY_SITE_ID, true);
        $idPrefix = $siteId . '00000';

        if (false !== strpos($thumbnailId, $idPrefix)) {
            $thumbnailId = (int)str_replace($idPrefix, '', $thumbnailId);
            switch_to_blog($siteId);
            $image = array_filter((array)wp_get_attachment_image_src($thumbnailId, $size, $icon));
            restore_current_blog();
        }

        add_filter(
            self::FILTER_ATTACHMENT_IMAGE_SRC,
            [$this, 'retrieveTheImages'],
            PHP_INT_MAX,
            4
        );

        return $image;
    }
}
