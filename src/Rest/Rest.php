<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia\Rest;

use MultisiteGlobalMedia\Helper;
use MultisiteGlobalMedia\Site;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Posts_Controller;
use WP_REST_Request;

/**
 * Class Rest
 */
class Rest
{
    use Helper;
    const META_KEY_THUMBNAIL_ID = '_thumbnail_id';
    const REST_FIELD_THUMBNAIL_ID = 'featured_media';

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
     * @param string $postType
     *
     * @return array
     *
     * @wp-hook register_post_type_args
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
     *
     * @return WP_HTTP_Response|WP_Error
     *
     * @wp-hook rest_request_after_callbacks
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    public function restRequestAfterCallbacks($response, array $handler, WP_REST_Request $request)
    {
        // phpcs:enable
        if (!is_array($handler['callback']) || !isset($handler['callback'][0]) || !($handler['callback'][0] instanceof WP_REST_Posts_Controller)) {
            return $response;
        }

        $idPrefix = $this->site->idSitePrefix();
        $attachmentId = (int)$request[self::REST_FIELD_THUMBNAIL_ID];
        $postId = (int)$request['id'];
        if ($attachmentId && $this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
            update_post_meta($postId, self::META_KEY_THUMBNAIL_ID, $attachmentId);

            $data = $response->get_data();
            $data[self::REST_FIELD_THUMBNAIL_ID] = $attachmentId;
            $response->set_data($data);
        }

        return $response;
    }
}
