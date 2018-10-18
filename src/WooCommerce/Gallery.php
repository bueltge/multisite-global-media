<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia\WooCommerce;

use MultisiteGlobalMedia\Helper;
use MultisiteGlobalMedia\SingleSwitcher;
use MultisiteGlobalMedia\Site;

/**
 * Class Gallery
 *
 * TODO May be we want to split admin from the frontend?
 */
class Gallery
{
    use Helper;

    const FILTER_ATTACHMENT_IMAGE_SRC = 'wp_get_attachment_image_src';

    const DEFAULT_PRODUCT_TYPE = 'simple';
    const POST_PRODUCT_TYPE = 'product-type';
    const POST_PRODUCT_IMAGE_GALLERY = 'product_image_gallery';

    const META_KEY_PRODUCT_GALLERY = '_product_image_gallery';

    /**
     * @var Site
     */
    private $site;

    /**
     * @var SingleSwitcher
     */
    private $siteSwitcher;

    /**
     * Gallery constructor
     *
     * @param Site $site
     * @param SingleSwitcher $siteSwitcher
     */
    public function __construct(Site $site, SingleSwitcher $siteSwitcher)
    {
        $this->site = $site;
        $this->siteSwitcher = $siteSwitcher;
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

        $metaboxCallback =& $wp_meta_boxes['product']['side']['low']['woocommerce-product-images']['callback'] ?? null;

        if (!$metaboxCallback) {
            return;
        }

        $metaboxCallback = function (\WP_Post $post) {
            $this->activateRetrieveImageFilter();
            \WC_Meta_Box_Product_Images::output($post);
            $this->deactivateRetrieveImageFilter();
        };
    }

    /**
     * Retrieve the images from the global site
     *
     * @param mixed $image
     * @param int $attachmentId
     * @param $size
     * @param bool $icon
     * @return array
     */
    public function retrieveTheImages(
        $image,
        int $attachmentId,
        $size,
        bool $icon
    ): array {

        // We expect a boolean false because the image (siteID.00000.ID) doesn't exists.
        if (\is_bool($image)) {
            $image = [];
        }

        $this->deactivateRetrieveImageFilter();

        $siteId = $this->siteIdByPostId($attachmentId, $this->site->id());
        $idPrefix = $siteId . Site::SITE_ID_PREFIX_RIGHT_PAD;

        if ($this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            $attachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);
            // TODO Could be improved if we allow multiple switches.
            $this->siteSwitcher->switchToBlog($siteId);
            $image = array_filter((array)wp_get_attachment_image_src($attachmentId, $size, $icon));
            $this->siteSwitcher->restoreBlog();
        }

        $this->activateRetrieveImageFilter();

        return $image;
    }

    /**
     * Show the gallery image on frontend
     *
     * @param string $html
     * @param int $attachmentId
     * @return string
     */
    public function singleProductImageThumbnailHtml(string $html, int $attachmentId): string
    {
        /** @var \WC_Product $product */
        global $product;

        $productId = $product->get_id();

        $siteId = $this->siteIdByPostId($productId, $this->site->id());
        $idPrefix = $siteId . Site::SITE_ID_PREFIX_RIGHT_PAD;

        if (!$this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            return $html;
        }

        $attachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);
        $this->siteSwitcher->switchToBlog($siteId);
        $html = wc_get_gallery_image_html($attachmentId);
        $this->siteSwitcher->restoreBlog();

        return $html;
    }

    /**
     * Activate the retrieve image filter
     */
    private function activateRetrieveImageFilter()
    {
        add_filter(
            self::FILTER_ATTACHMENT_IMAGE_SRC,
            [$this, 'retrieveTheImages'],
            PHP_INT_MAX,
            4
        );
    }

    /**
     * Deactivate the retrieve image filter
     */
    private function deactivateRetrieveImageFilter()
    {
        remove_filter(
            self::FILTER_ATTACHMENT_IMAGE_SRC,
            [$this, 'retrieveTheImages'],
            PHP_INT_MAX
        );
    }
}
