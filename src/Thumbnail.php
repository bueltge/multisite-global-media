<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class Thumbnail
 */
class Thumbnail
{
    use Helper;

    const META_KEY_THUMBNAIL_ID = '_thumbnail_id';

    /**
     * @var Site
     */
    private $site;

    /**
     * @var SingleSwitcher
     */
    private $siteSwitcher;

    /**
     * Thumbnail constructor
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
     * Fires once a post has been saved.
     *
     * @since 1.5.0
     *
     * @param int $postId Post ID.
     */
    public function saveThumbnailMeta(int $postId)
    {
        $idPrefix = $this->site->idSitePrefix();

        $attachmentId = (int)filter_input(
            INPUT_POST,
            self::META_KEY_THUMBNAIL_ID,
            FILTER_SANITIZE_NUMBER_INT
        );

        if (!$attachmentId) {
            return;
        }

        if ($attachmentId && $this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            update_post_meta($postId, self::META_KEY_THUMBNAIL_ID, $attachmentId);
            update_post_meta($attachmentId, Site::META_KEY_SITE_ID, $this->site->id());
        }
    }

    /**
     * Ajax handler for retrieving HTML for the featured image.
     *
     * @since 4.6.0
     *
     * @param int $postId
     * @param int $attachmentId
     */
    public function ajaxGetPostThumbnailHtml(int $postId, int $attachmentId)
    {
        $idPrefix = $this->site->idSitePrefix();

        $return = _wp_post_thumbnail_html($attachmentId, $postId);

        if (!$this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            wp_send_json_success($return);
        }

        $attachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);

        $this->siteSwitcher->switchToBlog($this->site->id());
        $return = _wp_post_thumbnail_html($attachmentId, $postId);
        $this->siteSwitcher->restoreBlog();

        $post = get_post($postId);
        $postTypeObject = get_post_type_object($post->post_type);

        $return = $this->replaceRemovePostThumbnailMarkup(
            esc_html($postTypeObject->labels->remove_featured_image),
            $return
        );

        wp_send_json_success($return);
    }

    /**
     * Filters the admin post thumbnail HTML markup to return.
     *
     * @param string $content Admin post thumbnail HTML markup.
     * @param int $postId Post ID.
     * @param string|int $attachmentId Thumbnail ID.
     *
     * @return string
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function adminPostThumbnailHtml(string $content, int $postId, $attachmentId): string
    {
        // phpcs:enable

        $attachmentId = (int)$attachmentId;
        $siteId = $this->siteIdByPostId($attachmentId, $this->site->id());
        $idPrefix = $this->site->idSitePrefix();

        if (false === $this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            return $content;
        }

        $post = get_post($postId);
        $attachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);

        $this->siteSwitcher->switchToBlog($siteId);
        // $thumbnailId is passed instead of postId to avoid warning messages of nonexistent post object.
        $content = _wp_post_thumbnail_html($attachmentId, $post);
        $this->siteSwitcher->restoreBlog();

        $search = 'value="' . $attachmentId . '"';
        $replace = 'value="' . $idPrefix . $attachmentId . '"';
        $content = str_replace($search, $replace, $content);

        $post = get_post($postId);
        $postTypeObject = null;

        $removeImageLabel = _x('Remove featured image', 'post', 'multisite-global-media');
        if ($post !== null) {
            $postTypeObject = get_post_type_object($post->post_type);
        }
        if ($postTypeObject !== null) {
            $removeImageLabel = $postTypeObject->labels->remove_featured_image;
        }

        return $this->replaceRemovePostThumbnailMarkup(
            $removeImageLabel,
            $content
        );
    }

    /**
     * Filters the post thumbnail HTML.
     *
     * @since 2.9.0
     *
     * @param string $html The post thumbnail HTML.
     * @param int $postId The post ID.
     * @param string $attachmentId The post thumbnail ID.
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
        string $attachmentId,
        $size,
        $attr
    ): string {

        // phpcs:enable
        // ToDo: int vs. string inside functions parameter - is that correct?
        $attachmentId = (int) $attachmentId;
        $siteId = $this->siteIdByPostId($attachmentId, $this->site->id());
        $idPrefix = $siteId . Site::SITE_ID_PREFIX_RIGHT_PAD;
        $thumbnailId = (int)get_post_meta($postId, '_thumbnail_id', true);

        if ($this->idPrefixIncludedInAttachmentId($thumbnailId, $idPrefix)) {
            $thumbnailId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $thumbnailId);

            if ($siteId && $thumbnailId) {
                $this->siteSwitcher->switchToBlog($siteId);
                $html = wp_get_attachment_image($thumbnailId, $size, false, $attr);
                $this->siteSwitcher->restoreBlog();
            }
        }

        return $html;
    }

    /**
     * Replace the remove post thumbnail markup with the image or without
     *
     * @param string $replace
     * @param string $subject
     * @return string
     */
    private function replaceRemovePostThumbnailMarkup(string $replace, string $subject): string
    {
        $search = '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail"></a></p>';
        $replace = sprintf(
            '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">%s</a></p>',
            $replace
        );

        return str_replace($search, $replace, $subject);
    }
}
