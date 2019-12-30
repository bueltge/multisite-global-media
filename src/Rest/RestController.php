<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia\Rest;

use MultisiteGlobalMedia\Helper;
use MultisiteGlobalMedia\SingleSwitcher;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\SiteSwitcher;
use WP_REST_Attachments_Controller;

/**
 * Class RestController
 *
 * Disable our codestyle for several topics, because we extend a WP core class, method.
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
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * phpcs:disable Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
     */
    public function __construct($post_type)
    {
        // phpcs:enable
        $this->site = new Site();
        $this->siteSwitcher = new SingleSwitcher();

        // phpcs:ignore Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
        parent::__construct($post_type);
    }

    /**
     * {@inheritDoc}
     *
     * phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     */
    public function get_item_permissions_check($request)
    {
        // phpcs:enable
        $idPrefix = $this->site->idSitePrefix();

        if (!$this->idPrefixIncludedInAttachmentId((int)$request['id'], $idPrefix)) {
            return parent::get_item_permissions_check($request);
        }

        // Clone so the original id is available in other methods.
        $requestClone = clone $request;
        $requestClone['id'] = $this->stripSiteIdPrefixFromAttachmentId(
            $idPrefix,
            (int)$request['id']
        );

        $this->siteSwitcher->switchToBlog($this->site->id());
        $response = parent::get_item_permissions_check($requestClone);
        $this->siteSwitcher->restoreBlog();

        return $response;
    }

    /**
     * {@inheritDoc}
     *
     * phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     */
    public function get_item($request)
    {
        // phpcs:enable
        $idPrefix = $this->site->idSitePrefix();

        if (!$this->idPrefixIncludedInAttachmentId((int)$request['id'], $idPrefix)) {
            return parent::get_item($request);
        }

        $attachmentId = (int)$request['id'];
        $request['id'] = $this->stripSiteIdPrefixFromAttachmentId($idPrefix, $attachmentId);
        $this->siteSwitcher->switchToBlog($this->site->id());
        $response = parent::get_item($request);
        $data = $response->get_data();

        if (isset($data['id'])) {
            $data['id'] = $attachmentId;
            $response->set_data($data);
        }

        return $response;
    }
}
