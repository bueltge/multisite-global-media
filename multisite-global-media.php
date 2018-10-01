<?php // -*- coding: utf-8 -*-
declare(strict_types = 1);

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

namespace MultisiteGlobalMedia;

/**
 * Don't call this file directly.
 */
\defined('ABSPATH') || die();

/**
 * Id of side inside the network, there store the global media.
 * Select the ID of the site/blog to where you want media
 *  that will be shared across the network to be stored.
 * Alternative change this value with the help
 *  of the filter hook 'global_media.site_id'.
 *
 * @var    integer
 * @since  2015-01-22
 */
const SITE_ID = 1;

/**
 * Return the ID of site that store the media files.
 *
 * @since  2017-12-01
 * @return integer The site ID.
 */
function getSideId(): int
{
    return (int) apply_filters('global_media.site_id', SITE_ID);
}

/**
 * Returns whether or not we're currently on the network media library site, regardless of any switching that's occurred.
 *
 * `$current_blog` can be used to determine the "actual" site as it doesn't change when switching sites.
 *
 * @return bool Whether we're on the network media library site.
 */
function isMediaSite(): bool
{
    return ( getSideId() === (int) $GLOBALS['current_blog']->blog_id );
}

add_action('admin_enqueue_scripts', __NAMESPACE__.'\enqueueScripts');
/**
 * Enqueue script for media modal
 *
 * @since  2015-01-26
 */
function enqueueScripts()
{
    if ('post' !== get_current_screen()->base) {
        return;
    }

    wp_enqueue_script(
        'global_media',
        plugins_url('assets/js/global-media.js', __FILE__),
        ['media-views'],
        '0.1',
        true
    );

    wp_enqueue_script('global_media');
}

add_action('admin_enqueue_scripts', __NAMESPACE__.'\enqueueStyles');
/**
 * Enqueue script for media modal
 *
 * @since   2015-02-27
 */
function enqueueStyles()
{
    if ('post' !== get_current_screen()->base) {
        return;
    }

    wp_register_style(
        'global_media',
        plugins_url('assets/css/global-media.css', __FILE__),
        [],
        '0.1'
    );
    wp_enqueue_style('global_media');
}

add_filter('media_view_strings', __NAMESPACE__.'\getMediaStrings');
/**
 * Define Strings for translation
 *
 * @since   2015-01-26
 *
 * @param array $strings
 *
 * @return array
 */
function getMediaStrings(array $strings): array
{
    $strings['globalMediaTitle'] = esc_html__('Global Media', 'global_media');

    return $strings;
}

/**
 * Prepare media for javascript
 *
 * @since   2015-01-26
 * @version 2018-08-29
 *
 * @param array      $response   Array of prepared attachment data.
 * @param \WP_Post   $attachment Attachment ID or object.
 * @param array|bool $meta       Array of attachment meta data, or boolean false if there is none.
 *
 * @return array Array of prepared attachment data.
 */
function prepareAttachmentForJs(array $response, \WP_Post $attachment, $meta): array
{

    if (isMediaSite()) {
        return $response;
    }

    $idPrefix = getSideId().'00000';

    $response['id'] = $idPrefix.$response['id']; // Unique ID, must be a number.
    $response['nonces']['update'] = false;
    $response['nonces']['edit'] = false;
    $response['nonces']['delete'] = false;
    $response['editLink'] = false;

    return $response;
}

add_action('wp_ajax_query-attachments', __NAMESPACE__.'\ajaxQueryAttachments', 0);
/**
 * Same as wp_ajax_query_attachments() but with switch_to_blog support.
 *
 * @since   2015-01-26
 * @return void
 */
function ajaxQueryAttachments()
{
    // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
    $query = isset($_REQUEST['query'])
        ? (array) wp_unslash($_REQUEST['query'])
        : [];
    // phpcs:enable

    if (!empty($query['global_media'])) {
        switch_to_blog(getSideId());

        add_filter('wp_prepare_attachment_for_js', __NAMESPACE__.'\prepareAttachmentForJs', 0, 3);
    }

    wp_ajax_query_attachments();
    exit;
}

/**
 * Send media to editor
 *
 * @since   2015-01-26
 *
 * @param string $html
 * @param int $id
 *
 * @return string $html
 */
function mediaSendToEditor(string $html, int $id): string
{
    $idPrefix = getSideId().'00000';
    $newId = $idPrefix.$id; // Unique ID, must be a number.

    $search = 'wp-image-'.$id;
    $replace = 'wp-image-'.$newId;
    $html = str_replace($search, $replace, $html);

    return $html;
}

add_action(
    'wp_ajax_send-attachment-to-editor',
    __NAMESPACE__.'\ajaxSendAttachmentToEditor',
    0
);
/**
 * Send media via AJAX call to editor
 *
 * @since   2015-01-26
 * @return  void
 */
function ajaxSendAttachmentToEditor()
{
    // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
    $attachment = wp_unslash($_POST['attachment']);
    $id = $attachment['id'];
    $idPrefix = getSideId().'00000';
    // phpcs:enable

    if (false !== strpos($id, $idPrefix)) {
        $attachment['id'] = str_replace($idPrefix, '', $id); // Unique ID, must be a number.
        $_POST['attachment'] = wp_slash($attachment);

        switch_to_blog(getSideId());

        add_filter('mediaSendToEditor', __NAMESPACE__.'\mediaSendToEditor', 10, 2);
    }

    wp_ajax_send_attachment_to_editor();
    exit();
}

add_action('wp_ajax_get-attachment', __NAMESPACE__.'\ajaxGetAttachment', 0);
/**
 * Get attachment
 *
 * @since   2015-01-26
 * @return  void
 */
function ajaxGetAttachment()
{
    // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
    $id = wp_unslash($_REQUEST['id']);
    // phpcs:enable
    $idPrefix = getSideId().'00000';

    if (false !== strpos($id, $idPrefix)) {
        $id = str_replace($idPrefix, '', $id); // Unique ID, must be a number.
        $_REQUEST['id'] = $id;

        switch_to_blog(getSideId());
        add_filter('wp_prepare_attachment_for_js', __NAMESPACE__.'\prepareAttachmentForJs', 0, 3);
        restore_current_blog();
    }

    wp_ajax_get_attachment();
    exit();
}

add_action('save_post', __NAMESPACE__.'\saveThumbnailMeta', 99);
/**
 * Fires once a post has been saved.
 *
 * @since 1.5.0
 *
 * @param int $postId Post ID.
 */
function saveThumbnailMeta(int $postId)
{
    $idPrefix = getSideId().'00000';

    // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
    if(!isset($_POST['_thumbnail_id'])) {
        return;
    }

    $thumbnailId = wp_unslash($_POST['_thumbnail_id']);
    // phpcs:enable


    if ($thumbnailId && false !== strpos($thumbnailId, $idPrefix)) {
        update_post_meta($postId, '_thumbnail_id', intval($thumbnailId));
        update_post_meta($postId, 'global_media_site_id', getSideId());
    }
}

add_action('wp_ajax_get-post-thumbnail-html', __NAMESPACE__.'\ajaxGetPostThumbnailHtml', 99);
/**
 * Ajax handler for retrieving HTML for the featured image.
 *
 * @since 4.6.0
 *
 * @param int $postId
 * @param int $thumbnailId
 */
function ajaxGetPostThumbnailHtml(int $postId, int $thumbnailId)
{
    $idPrefix = getSideId().'00000';

    $return = _wp_post_thumbnail_html($thumbnailId, $postId);

    if (false !== strpos($thumbnailId, $idPrefix)) {
        $thumbnailId = str_replace($idPrefix, '', $thumbnailId); // Unique ID, must be a number.

        switch_to_blog(getSideId());

        $return = _wp_post_thumbnail_html($thumbnailId, $postId);
        restore_current_blog();

        $post = get_post($postId);
        $postTypeObject = get_post_type_object($post->post_type);

        $search = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail"></a></p>';
        $replace = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">'.esc_html(
            $postTypeObject->labels->remove_featured_image
        ).'</a></p>';
        $return = str_replace($search, $replace, $return);
    }

    wp_send_json_success($return);
}

add_filter('admin_post_thumbnail_html', __NAMESPACE__.'\adminPostThumbnailHtml', 99, 3);
/**
 * Filters the admin post thumbnail HTML markup to return.
 *
 * @param string $content Admin post thumbnail HTML markup.
 * @param int $postId Post ID.
 * @param string|int $thumbnailId Thumbnail ID.
 *
 * @return string
 *
 * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
 */
function adminPostThumbnailHtml(string $content, $postId = null, $thumbnailId): string
{
    // phpcs:enable

    $siteId = get_post_meta($postId, 'global_media_site_id', true);
    if (empty($siteId)) {
        $siteId = getSideId();
    }

    $idPrefix = getSideId().'00000';

    if (false === strpos((string)$thumbnailId, $idPrefix)) {
        return $content;
    }

    $thumbnailId = (int)str_replace($idPrefix, '', $thumbnailId); // Unique ID, must be a number.

    switch_to_blog($siteId);

    // $thumbnailId is passed instead of postId to avoid warning messages of nonexistent post object.
    $content = _wp_post_thumbnail_html($thumbnailId, $postId);

    restore_current_blog();

    $search = 'value="'.$thumbnailId.'"';
    $replace = 'value="'.$idPrefix.$thumbnailId.'"';
    $content = str_replace($search, $replace, $content);

    $post = get_post($postId);
    $postTypeObject = null;

    $removeImageLabel = _x('Remove featured image', 'post');
    if ($post !== null) {
        $postTypeObject = get_post_type_object($post->post_type);
    }
    if ($postTypeObject !== null) {
        $removeImageLabel = $postTypeObject->labels->remove_featured_image;
    }

    $search = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail"></a></p>';
    $replace = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">'
        .esc_html($removeImageLabel)
        .'</a></p>';

    return str_replace($search, $replace, $content);
}

add_filter('post_thumbnail_html', __NAMESPACE__.'\postThumbnailHtml', 99, 5);

/**
 * Filters the post thumbnail HTML.
 *
 * @since 2.9.0
 *
 * @param string $html The post thumbnail HTML.
 * @param int $postId The post ID.
 * @param string $postThumbnailId The post thumbnail ID.
 * @param string|array $size The post thumbnail size. Image size or array of width and height
 *                                        values (in that order). Default 'post-thumbnail'.
 * @param string $attr Query string of attributes.
 *
 * @return string
 *
 * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
 */
function postThumbnailHtml(string $html, int $postId, string $postThumbnailId, $size, $attr): string
{
    // phpcs:enable

    $siteId = get_post_meta($postId, 'global_media_site_id', true);
    $thumbnailId = get_post_meta($postId, '_thumbnail_id', true);
    $idPrefix = $siteId.'00000';

    if (false !== strpos($thumbnailId, $idPrefix)) {
        $thumbnailId = str_replace($idPrefix, '', $thumbnailId); // Unique ID, must be a number.

        if (intval($siteId) && intval($thumbnailId)) {
            switch_to_blog($siteId);

            $html = wp_get_attachment_image($thumbnailId, $size, false, $attr);

            restore_current_blog();
        }
    }

    return $html;
}
