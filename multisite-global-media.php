<?php // -*- coding: utf-8 -*-
declare(strict_types = 1);

/**
 * Plugin Name: Multisite Global Media
 * Description: Multisite Global Media is a WordPress plugin which shares media across the Multisite network.
 * Network:     true
 * Plugin URI:  https://github.com/bueltge/multisite-global-media
 * Version:     0.0.6
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
 * @version 2018-08-27
 */

namespace Multisite_Global_Media;

/**
 * Don't call this file directly.
 */
defined('ABSPATH') || die();

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
function get_site_id(): int
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
function is_media_site() : bool
{
    return ( get_site_id() === (int) $GLOBALS['current_blog']->blog_id );
}

add_action('admin_enqueue_scripts', __NAMESPACE__.'\enqueue_scripts');
/**
 * Enqueue script for media modal
 *
 * @since  2015-01-26
 */
function enqueue_scripts()
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

add_action('admin_enqueue_scripts', __NAMESPACE__.'\enqueue_styles');
/**
 * Enqueue script for media modal
 *
 * @since   2015-02-27
 */
function enqueue_styles()
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

add_filter('media_view_strings', __NAMESPACE__.'\get_media_strings');
/**
 * Define Strings for translation
 *
 * @since   2015-01-26
 *
 * @param array $strings
 *
 * @return array
 */
function get_media_strings(array $strings): array
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
 * @param WP_Post    $attachment Attachment ID or object.
 * @param array|bool $meta       Array of attachment meta data, or boolean false if there is none.
 *
 * @return array Array of prepared attachment data.
 */
function prepare_attachment_for_js(array $response, \WP_Post $attachment, $meta): array
{

    if (is_media_site()) {
       return $response;
    }

    $idPrefix = get_site_id().'00000';

    $response['id'] = $idPrefix.$response['id']; // Unique ID, must be a number.
    $response['nonces']['update'] = false;
    $response['nonces']['edit'] = false;
    $response['nonces']['delete'] = false;
    $response['editLink'] = false;

    return $response;
}

add_action('wp_ajax_query-attachments', __NAMESPACE__.'\ajax_query_attachments', 0);
/**
 * Same as wp_ajax_query_attachments() but with switch_to_blog support.
 *
 * @since   2015-01-26
 * @return void
 */
function ajax_query_attachments()
{
    // phpcs:disable
    $query = isset($_REQUEST['query'])
        ? (array) $_REQUEST['query']
        : array();

    if (!empty($query['global_media'])) {
        switch_to_blog(get_site_id());

        add_filter('wp_prepare_attachment_for_js', __NAMESPACE__.'\prepare_attachment_for_js', 0, 3);
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
function media_send_to_editor(string $html, int $id): string
{
    $idPrefix = get_site_id().'00000';
    $newId = $idPrefix.$id; // Unique ID, must be a number.

    $search = 'wp-image-'.$id;
    $replace = 'wp-image-'.$newId;
    $html = str_replace($search, $replace, $html);

    return $html;
}

add_action(
    'wp_ajax_send-attachment-to-editor',
    __NAMESPACE__.'\ajax_send_attachment_to_editor',
    0
);
/**
 * Send media via AJAX call to editor
 *
 * @since   2015-01-26
 * @return  void
 */
function ajax_send_attachment_to_editor()
{
    // phpcs:disable
    $attachment = wp_unslash($_POST['attachment']);
    $id = $attachment['id'];
    $idPrefix = get_site_id().'00000';

    if (false !== strpos($id, $idPrefix)) {
        $attachment['id'] = str_replace($idPrefix, '', $id); // Unique ID, must be a number.
        $_POST['attachment'] = wp_slash($attachment);

        switch_to_blog(get_site_id());

        add_filter('media_send_to_editor', __NAMESPACE__.'\media_send_to_editor', 10, 2);
    }

    wp_ajax_send_attachment_to_editor();
    exit();
}

add_action('wp_ajax_get-attachment', __NAMESPACE__.'\ajax_get_attachment', 0);
/**
 * Get attachment
 *
 * @since   2015-01-26
 * @return  void
 */
function ajax_get_attachment()
{
    // phpcs:disable
    $id = $_REQUEST['id'];
    $idPrefix = get_site_id().'00000';

    if (false !== strpos($id, $idPrefix)) {
        $id = str_replace($idPrefix, '', $id); // Unique ID, must be a number.
        $_REQUEST['id'] = $id;

        switch_to_blog(get_site_id());
        add_filter('wp_prepare_attachment_for_js', __NAMESPACE__.'\prepare_attachment_for_js', 0, 3);
        restore_current_blog();
    }

    wp_ajax_get_attachment();
    exit();
}

add_action('save_post', __NAMESPACE__.'\save_thumbnail_meta', 99);
/**
 * Fires once a post has been saved.
 *
 * @since 1.5.0
 *
 * @param int $postId Post ID.
 */
function save_thumbnail_meta(int $postId)
{
    $idPrefix = get_site_id().'00000';
    // phpcs:disable
    if (!empty($_POST['_thumbnail_id']) && false !== strpos($_POST['_thumbnail_id'], $idPrefix)) {
        update_post_meta($postId, '_thumbnail_id', intval($_POST['_thumbnail_id']));
        update_post_meta($postId, 'global_media_site_id', get_site_id());
    }
}

add_action('wp_ajax_get-post-thumbnail-html', __NAMESPACE__.'\ajax_get_post_thumbnail_html', 99);
/**
 * Ajax handler for retrieving HTML for the featured image.
 *
 * @since 4.6.0
 *
 * @param int $postId
 * @param int $thumbnailId
 */
function ajax_get_post_thumbnail_html(int $postId, int $thumbnailId)
{
    $id_prefix = get_site_id().'00000';

    if (false !== strpos($thumbnailId, $id_prefix)) {
        $thumbnailId = str_replace($id_prefix, '', $thumbnailId); // Unique ID, must be a number.

        switch_to_blog(get_site_id());

        $return = _wp_post_thumbnail_html($thumbnailId, $postId);
        restore_current_blog();

        $post = get_post($postId);
        $post_type_object = get_post_type_object($post->post_type);

        $search = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail"></a></p>';
        $replace = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">'.esc_html(
                $post_type_object->labels->remove_featured_image
            ).'</a></p>';
        $return = str_replace($search, $replace, $return);
    } else {
        $return = _wp_post_thumbnail_html($thumbnailId, $postId);
    }

    wp_send_json_success($return);
}

add_filter('admin_post_thumbnail_html', __NAMESPACE__.'\admin_post_thumbnail_html', 99, 3);
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
function admin_post_thumbnail_html(string $content, int $postId, $thumbnailId): string
{
    // phpcs:enable

    $siteId = get_post_meta($postId, 'global_media_site_id', true);
    if (empty($siteId)) {
        $siteId = get_site_id();
    }

    $id_prefix = get_site_id().'00000';

    if (false === strpos((string)$thumbnailId, $id_prefix)) {
        return $content;
    }

    $thumbnailId = (int)str_replace($id_prefix, '', $thumbnailId); // Unique ID, must be a number.

    switch_to_blog($siteId);

    // $thumbnailId is passed instead of postId to avoid warning messages of nonexistent post object.
    $content = _wp_post_thumbnail_html($thumbnailId, $postId);

    restore_current_blog();

    $search = 'value="'.$thumbnailId.'"';
    $replace = 'value="'.$id_prefix.$thumbnailId.'"';
    $content = str_replace($search, $replace, $content);

    $post = get_post($postId);
    $post_type_object = null;

    $remove_image_label = _x('Remove featured image', 'post');
    if ($post !== null) {
        $post_type_object = get_post_type_object($post->post_type);
    }
    if ($post_type_object !== null) {
        $remove_image_label = $post_type_object->labels->remove_featured_image;
    }

    $search = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail"></a></p>';
    $replace = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">'
        .esc_html($remove_image_label)
        .'</a></p>';
    $content = str_replace($search, $replace, $content);

    return $content;
}

add_filter('post_thumbnail_html', __NAMESPACE__.'\post_thumbnail_html', 99, 5);

/**
 * Filters the post thumbnail HTML.
 *
 * @since 2.9.0
 *
 * @param string $html The post thumbnail HTML.
 * @param int $postId The post ID.
 * @param string $post_thumbnail_id The post thumbnail ID.
 * @param string|array $size The post thumbnail size. Image size or array of width and height
 *                                        values (in that order). Default 'post-thumbnail'.
 * @param string $attr Query string of attributes.
 *
 * @return string
 */

function post_thumbnail_html(string $html, int $postId, string $post_thumbnail_id, string $size, string $attr): string
{
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
