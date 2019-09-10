<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class Attachment
 */
class Attachment
{
    use Helper;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var SiteSwitcher
     */
    private $siteSwitcher;

    /**
     * Attachment constructor.
     * @param Site $site
     * @param SiteSwitcher $siteSwitcher
     */
    public function __construct(Site $site, SiteSwitcher $siteSwitcher)
    {
        $this->site = $site;
        $this->siteSwitcher = $siteSwitcher;
    }

    /**
     * Prepare media for javascript
     *
     * @since   2015-01-26
     * @version 2018-08-29
     *
     * @param array $response Array of prepared attachment data.
     *
     * @return array Array of prepared attachment data.
     */
    public function prepareAttachmentForJs(array $response): array
    {
        $idPrefix = $this->site->idSitePrefix();

        $response['id'] = $idPrefix . $response['id']; // Unique ID, must be a number.
        $response['nonces']['update'] = false;
        $response['nonces']['edit'] = false;
        $response['nonces']['delete'] = false;
        $response['editLink'] = false;

        return $response;
    }

    /**
     * Same as wp_ajax_query_attachments() but with switch_to_blog support.
     *
     * @since   2015-01-26
     * @return void
     */
    public function ajaxQueryAttachments()
    {
        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
        // phpcs:disable
        $query = isset($_REQUEST['query'])
            ? (array)wp_unslash($_REQUEST['query'])
            : [];
        // phpcs:enable

        if (!empty($query['global_media'])) {
            switch_to_blog($this->site->id());
            add_filter('wp_prepare_attachment_for_js', [$this, 'prepareAttachmentForJs'], 0);
        }

        wp_ajax_query_attachments();
    }

    /**
     * Get attachment
     *
     * @since   2015-01-26
     * @return  void
     */
    public function ajaxGetAttachment()
    {
        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
        // phpcs:disable WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
        // phpcs:disable WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
        $attachmentId = (int)wp_unslash($_REQUEST['id']);
        // phpcs:enable
        $idPrefix = $this->site->idSitePrefix();

        if ($this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            $attachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);
            $_REQUEST['id'] = $attachmentId;

            $this->siteSwitcher->switchToBlog($this->site->id());
            add_filter('wp_prepare_attachment_for_js', [$this, 'prepareAttachmentForJs'], 0);
            $this->siteSwitcher->restoreBlog();
        }

        wp_ajax_get_attachment();
    }

    /**
     * Send media via AJAX call to editor
     *
     * @since   2015-01-26
     * @return  void
     */
    public function ajaxSendAttachmentToEditor()
    {
        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
        // phpcs:disable WordPress.VIP.ValidatedSanitizedInput.InputNotSanitized
        // phpcs:disable WordPress.VIP.ValidatedSanitizedInput.InputNotValidated
        $attachment = wp_unslash($_POST['attachment']);
        $attachmentId = (int)$attachment['id'];
        $idPrefix = $this->site->idSitePrefix();

        if ($this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            $attachment['id'] = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);
            $_POST['attachment'] = wp_slash($attachment);

            // TODO Which is the reason why we don't restore the blog?
            switch_to_blog($this->site->id());

            add_filter('mediaSendToEditor', [$this, 'mediaSendToEditor'], 10, 2);
        }

        wp_ajax_send_attachment_to_editor();
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
    public function mediaSendToEditor(string $html, int $id): string
    {
        $idPrefix = $this->site->idSitePrefix();
        $newId = $idPrefix . $id; // Unique ID, must be a number.

        $search = 'wp-image-' . $id;
        $replace = 'wp-image-' . $newId;
        $html = str_replace($search, $replace, $html);

        return $html;
    }

    /**
     * @param array|false $image
     * @param int|string $attachmentId
     * @param array|string $size
     * @param bool $icon
     * @return array|false
     *
     * @wp-hook wp_get_attachment_image_src
     */
    public function attachmentImageSrc($image, $attachmentId, $size, bool $icon)
    {
        // phpcs:enable

        $attachmentId = (int)$attachmentId;
        $idPrefix = $this->site->idSitePrefix();

        if (!$this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            return $image;
        }

        $attachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);
        $this->siteSwitcher->switchToBlog($this->site->id());
        $image = wp_get_attachment_image_src($attachmentId, $size, $icon);
        $this->siteSwitcher->restoreBlog();

        return $image;
    }

    /**
     * Define Strings for translation
     *
     * @since   2015-01-26
     *
     * @param array $strings
     *
     * @return array
     */
    public function mediaStrings(array $strings): array
    {
        $strings['globalMediaTitle'] = esc_html__('Global Media', 'global_media');

        return $strings;
    }

    /**
     * Add srcset to images in content.
     *
     * @see wp_make_content_images_responsive
     *
     * @param string $content
     * @return string
     *
     * @wp-hook the_content
     */
    public function makeContentImagesResponsive(string $content): string
    {
        if (!preg_match_all('/<img [^>]+>/', $content, $matches)) {
            return $content;
        }

        $selectedImages = $attachmentIds = [];

        foreach ($matches[0] as $image) {
            $hasSrcset = strpos($image, ' srcset=') !== false;
            $hasClassId = preg_match('/wp-image-([0-9]+)/i', $image, $classId);
            $attachmentId = !$hasSrcset && $hasClassId ? absint($classId[1]) : null;
            if ($attachmentId) {
                // If exactly the same image tag is used more than once, overwrite it.
                // All identical tags will be replaced later with 'str_replace()'.
                $selectedImages[$image] = $attachmentId;
                // Overwrite the ID when the same image is included more than once.
                $attachmentIds[$attachmentId] = true;
            }
        }

        if (count($attachmentIds) > 1) {
            // Warm the object cache with post and meta information for all found
            // images to avoid making individual database calls.
            _prime_post_caches(array_keys($attachmentIds), false, true);
        }

        $idPrefix = $this->site->idSitePrefix();

        foreach ($selectedImages as $image => $attachmentId) {
            if (!$this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
                $imageMeta = wp_get_attachment_metadata($attachmentId);
                $content = str_replace($image, wp_image_add_srcset_and_sizes($image, $imageMeta, $attachmentId), $content);
                continue;
            }

            $globalAttachmentId = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);

            $this->siteSwitcher->switchToBlog($this->site->id());
            $imageMeta = wp_get_attachment_metadata($globalAttachmentId);
            $content = str_replace($image, wp_image_add_srcset_and_sizes($image, $imageMeta, $attachmentId), $content);
            $this->siteSwitcher->restoreBlog();
        }

        return $content;
    }
}
