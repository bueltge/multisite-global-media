<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class Thumbnail
 */
class Thumbnail
{
    const META_KEY_THUMBNAIL_ID = '_thumbnail_id';

    /**
     * @var Site
     */
    private $site;

    /**
     * @var bool
     */
    private $switched = false;

    /**
     * Thumbnail constructor
     *
     * @param Site $site
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Fires once a post has been saved.
     *
     * @since 1.5.0
     *
     * @param int $postId Post ID.
     */
    public function saveThumbnailMeta(int $postId)
    {
        $idPrefix = $this->site->id() . '00000';

        $thumbnailId = (int)filter_input(
            INPUT_POST,
            self::META_KEY_THUMBNAIL_ID,
            FILTER_SANITIZE_NUMBER_INT
        );

        if (!$thumbnailId) {
            return;
        }

        if ($thumbnailId && false !== strpos($thumbnailId, $idPrefix)) {
            update_post_meta($postId, self::META_KEY_THUMBNAIL_ID, intval($thumbnailId));
            update_post_meta($postId, Site::META_KEY_SITE_ID, $this->site->id());
        }
    }

    /**
     * Ajax handler for retrieving HTML for the featured image.
     *
     * @since 4.6.0
     *
     * @param int $postId
     * @param int $thumbnailId
     */
    public function ajaxGetPostThumbnailHtml(int $postId, int $thumbnailId)
    {
        $idPrefix = $this->site->id() . '00000';

        $return = _wp_post_thumbnail_html($thumbnailId, $postId);

        if (false === strpos($thumbnailId, $idPrefix)) {
            wp_send_json_success($return);
        }

        $thumbnailId = str_replace($idPrefix, '', $thumbnailId); // Unique ID, must be a number.

        $this->switchToBlog($this->site->id());
        $return = _wp_post_thumbnail_html($thumbnailId, $postId);
        $this->restoreBlog();

        $post = get_post($postId);
        $postTypeObject = get_post_type_object($post->post_type);

        $search = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail"></a></p>';
        $replace = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">'
            . esc_html($postTypeObject->labels->remove_featured_image)
            . '</a></p>';
        $return = str_replace($search, $replace, $return);

        wp_send_json_success($return);
    }

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
    public function adminPostThumbnailHtml(string $content, int $postId, $thumbnailId): string
    {
        // phpcs:enable

        $siteId = get_post_meta($postId, Site::META_KEY_SITE_ID, true);
        if (empty($siteId)) {
            $siteId = $this->site->id();
        }

        $idPrefix = $this->site->id() . '00000';

        if (false === strpos((string)$thumbnailId, $idPrefix)) {
            return $content;
        }

        $post = get_post($postId);
        // Unique ID, must be a number.
        $thumbnailId = (int)str_replace($idPrefix, '', $thumbnailId);

        $this->switchToBlog($siteId);
        // $thumbnailId is passed instead of postId to avoid warning messages of nonexistent post object.
        $content = _wp_post_thumbnail_html($thumbnailId, $post);
        $this->restoreBlog();

        $search = 'value="' . $thumbnailId . '"';
        $replace = 'value="' . $idPrefix . $thumbnailId . '"';
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
            . esc_html($removeImageLabel)
            . '</a></p>';

        return str_replace($search, $replace, $content);
    }

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
    public function postThumbnailHtml(
        string $html,
        int $postId,
        string $postThumbnailId,
        $size,
        $attr
    ): string {

        // phpcs:enable

        $siteId = get_post_meta($postId, Site::META_KEY_SITE_ID, true);
        $thumbnailId = get_post_meta($postId, '_thumbnail_id', true);
        $idPrefix = $siteId . '00000';

        if (false !== strpos($thumbnailId, $idPrefix)) {
            $thumbnailId = str_replace($idPrefix, '', $thumbnailId); // Unique ID, must be a number.

            if (intval($siteId) && intval($thumbnailId)) {
                $this->switchToBlog($siteId);
                $html = wp_get_attachment_image($thumbnailId, $size, false, $attr);
                $this->restoreBlog();
            }
        }

        return $html;
    }

    /**
     * @param int $siteId
     */
    private function switchToBlog(int $siteId)
    {
        if ($this->site->id() === $siteId) {
            return;
        }

        switch_to_blog($siteId);

        $this->switched = true;
    }

    /**
     *
     */
    private function restoreBlog()
    {
        if (!$this->switched) {
            return;
        }

        restore_current_blog();

        $this->switched = false;
    }
}
