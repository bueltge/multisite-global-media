<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

use WP_REST_Posts_Controller;

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

    public function registerPostTypeArgs($args, $post_type)
    {
        if ($post_type === 'attachment') {
            $args['rest_controller_class'] = RestController::class;
        }
        return $args;
    }

    public function restRequestAfterCallbacks($response, $handler, $request)
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
