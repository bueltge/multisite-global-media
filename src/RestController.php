<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

use WP_REST_Attachments_Controller;

/**
 * Class RestController
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
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    // phpcs:disable Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
    public function __construct($post_type)
    {
        // phpcs:enable
        $this->site = new Site();
        $this->siteSwitcher = new SingleSwitcher();

        parent::__construct($post_type); // phpcs:ignore Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
    }

    /**
     * {@inheritDoc}
     */
    // phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    // phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_item_permissions_check($request)
    {
        // phpcs:enable
        $idPrefix = $this->site->idSitePrefix();

        if (!$this->idPrefixIncludedInAttachmentId($request['id'], $idPrefix)) {
            return parent::get_item_permissions_check($request);
        }

        // clone so the original id is available in other methods.
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
    // phpcs:disable Inpsyde.CodeQuality.NoAccessors.NoGetter
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    // phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_item($request)
    {
        // phpcs:enable
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
            $response->set_data($data);
        }

        return $response;
    }
}
