<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class Attachment
 */
class Attachment
{
    /**
     * @var Site
     */
    private $site;

    /**
     * Attachment constructor
     *
     * @param Site $site
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Prepare media for javascript
     *
     * @since   2015-01-26
     * @version 2018-08-29
     *
     * @param array $response Array of prepared attachment data.
     * @param \WP_Post $attachment Attachment ID or object.
     * @param array|bool $meta Array of attachment meta data, or boolean false if there is none.
     *
     * @return array Array of prepared attachment data.
     */
    public function prepareAttachmentForJs(array $response, \WP_Post $attachment, $meta): array
    {
        if ($this->site->isMediaSite()) {
            return $response;
        }

        $idPrefix = $this->site->id() . '00000';

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
        $query = isset($_REQUEST['query'])
            ? (array)wp_unslash($_REQUEST['query'])
            : [];
        // phpcs:enable

        if (!empty($query['global_media'])) {
            switch_to_blog($this->site->id());

            add_filter(
                'wp_prepare_attachment_for_js',
                __NAMESPACE__ . '\prepareAttachmentForJs',
                0,
                3
            );
        }

        wp_ajax_query_attachments();
        exit;
    }

    /**
     * Get attachment
     *
     * @since   2015-01-26
     * @return  void
     */
    public function ajaxGetAttachment()
    {
        // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
        $id = wp_unslash($_REQUEST['id']);
        // phpcs:enable
        $idPrefix = $this->site->id() . '00000';

        if (false !== strpos($id, $idPrefix)) {
            $id = str_replace($idPrefix, '', $id); // Unique ID, must be a number.
            $_REQUEST['id'] = $id;

            switch_to_blog($this->site->id());
            add_filter(
                'wp_prepare_attachment_for_js',
                __NAMESPACE__ . '\prepareAttachmentForJs',
                0,
                3
            );
            restore_current_blog();
        }

        wp_ajax_get_attachment();
        exit();
    }

    /**
     * Send media via AJAX call to editor
     *
     * @since   2015-01-26
     * @return  void
     */
    public function ajaxSendAttachmentToEditor()
    {
        // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
        $attachment = wp_unslash($_POST['attachment']);
        $id = $attachment['id'];
        $idPrefix = $this->site->id() . '00000';
        // phpcs:enable

        if (false !== strpos($id, $idPrefix)) {
            $attachment['id'] = str_replace($idPrefix, '', $id); // Unique ID, must be a number.
            $_POST['attachment'] = wp_slash($attachment);

            switch_to_blog($this->site->id());

            add_filter('mediaSendToEditor', __NAMESPACE__ . '\mediaSendToEditor', 10, 2);
        }

        wp_ajax_send_attachment_to_editor();
        exit();
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
        $idPrefix = $this->site->id() . '00000';
        $newId = $idPrefix . $id; // Unique ID, must be a number.

        $search = 'wp-image-' . $id;
        $replace = 'wp-image-' . $newId;
        $html = str_replace($search, $replace, $html);

        return $html;
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
}
