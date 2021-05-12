<?php# -*- coding: utf-8 -*-
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
    }
}
