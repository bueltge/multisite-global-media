<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

use WP_REST_Attachments_Controller;

/**
 * Class Rest
 */
class RestController extends WP_REST_Attachments_Controller
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
     * {@inheritDoc}
     */
    public function __construct($post_type)
    {
        $this->site = new Site();
        $this->siteSwitcher = new SingleSwitcher();

        parent::__construct($post_type);
    }

    /**
     * {@inheritDoc}
     */
    public function get_item_permissions_check($request)
    {
        $idPrefix = $this->site->idSitePrefix();

        if (!$this->idPrefixIncludedInAttachmentId($request['id'], $idPrefix)) {
            return parent::get_item_permissions_check($request);
        }

        // clone so the original id is avilable in other methods.
        $requestClone = clone $request;
        $requestClone['id'] = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $request['id']);

        $this->siteSwitcher->switchToBlog($this->site->id());
        $response = parent::get_item_permissions_check($requestClone);
        $this->siteSwitcher->restoreBlog();

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function get_item($request)
    {
        $idPrefix = $this->site->idSitePrefix();

        if (!$this->idPrefixIncludedInAttachmentId((int) $request['id'], $idPrefix)) {
            return parent::get_item($request);
        }

        $attachmentId = $request['id'];
        $request['id'] = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, (int) $attachmentId);
        $this->siteSwitcher->switchToBlog($this->site->id());
        $response = parent::get_item($request);
        $data = $response->get_data();

        if (isset($data['id'])) {
            $data['id'] = $attachmentId;
            $data['source_url'] = $this->site->replaceGlobalUrlPath($data['source_url']);
            $response->set_data($data);
        }

        return $response;
    }
}
