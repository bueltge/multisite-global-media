<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

use WP_REST_Posts_Controller;
use WP_REST_Request;

/**
 * Class Rest
 */
class Rest
{
    use Helper;

    const META_KEY_THUMBNAIL_ID = '_thumbnail_id';

    /**
     * @var Site
     */
    private $site;

    /**
     * Rest constructor
     *
     * @param Site $site
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Filter the attachment post type registration arguments to use our own
     * REST controller.
     *
     * @param array $args
     * @param string $post_type
     * @return array
     */
    public function registerPostTypeArgs(array $args, string $postType): array
    {
        if ($postType === 'attachment') {
            $args['rest_controller_class'] = RestController::class;
        }
        return $args;
    }

    /**
     * Filter REST API responses for post saves which include featured_media
     * field and force the post meta update even if the id doesn't exist on the
     * current site.
     *
     * @param WP_HTTP_Response|WP_Error $response
     * @param array $handler
     * @param WP_REST_Request $request
     * @return WP_HTTP_Response|WP_Error
     */
    public function restRequestAfterCallbacks($response, array $handler, WP_REST_Request $request) // phpcs:ignore
    {
        if (!isset($handler['callback'][0]) || !($handler['callback'][0] instanceof WP_REST_Posts_Controller)) {
            return $response;
        }

        $idPrefix = $this->site->idSitePrefix();
        $attachmentId = (int) $request['featured_media'];
        $postId = (int) $request['id'];
        if ($attachmentId && $this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            update_post_meta($postId, self::META_KEY_THUMBNAIL_ID, $attachmentId);

            $data = $response->get_data();
            $data['featured_media'] = $attachmentId;
            $response->set_data($data);
        }

        return $response;
    }
}
