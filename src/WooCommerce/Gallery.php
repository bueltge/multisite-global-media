<?php

declare(strict_types=1);

namespace MultisiteGlobalMedia\WooCommerce;

/**
 * Class Gallery
 *
 * TODO May be we want to split admin from the frontend?
 */
class Gallery
{
    public const FILTER_ATTACHMENT_IMAGE_SRC = 'wp_get_attachment_image_src';

    public const DEFAULT_PRODUCT_TYPE = 'simple';
    public const POST_PRODUCT_TYPE = 'product-type';
    public const POST_PRODUCT_IMAGE_GALLERY = 'product_image_gallery';

    public const META_KEY_PRODUCT_GALLERY = '_product_image_gallery';

    /**
     * Save Gallery Ids into product meta
     *
     * @param int $productId
     */
    public function saveGalleryIds(int $productId): void
    {
        $productType = \WC_Product_Factory::get_product_type($productId);
        $requestProductType = filter_input(
            INPUT_POST,
            self::POST_PRODUCT_TYPE,
            // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter -- sanitized below.
            FILTER_UNSAFE_RAW
        );

        $requestProductType and $productType = sanitize_title(stripslashes($requestProductType));

        $productType = $productType ?: self::DEFAULT_PRODUCT_TYPE;
        $classname = \WC_Product_Factory::get_product_classname($productId, $productType);
        /** @var \WC_Product $product */
        $product = new $classname($productId);
        $attachmentIds = filter_input(
            INPUT_POST,
            self::POST_PRODUCT_IMAGE_GALLERY,
            // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter -- sanitized below.
            FILTER_UNSAFE_RAW
        );
        $attachmentIds = sanitize_text_field((string) $attachmentIds);

        update_post_meta($product->get_id(), self::META_KEY_PRODUCT_GALLERY, $attachmentIds);
    }
}
