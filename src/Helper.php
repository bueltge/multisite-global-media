<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class CompareHelper
 */
trait Helper
{
    private function idPrefixIncludedInAttachmentId(
        int $attachmentId,
        string $siteIdPrefix
    ): bool {

        return false !== strpos((string)$attachmentId, $siteIdPrefix);
    }

    private function stripSiteIdPrefixFromAttachmentId(string $idPrefix, int $attachmentId): int
    {
        return (int)str_replace($idPrefix, '', (string)$attachmentId);
    }

    private function siteIdByPostId(int $objectId, int $default): int
    {
        $siteId = (int)get_post_meta($objectId, Site::META_KEY_SITE_ID, true);

        return $siteId ?: $default;
    }

    private function siteIdPrefixByPostId(int $objectId): string
    {
        $siteId = (int)get_post_meta($objectId, Site::META_KEY_SITE_ID, true);

        return $siteId . Site::SITE_ID_PREFIX_RIGHT_PAD;
    }

    private function storeSiteIdToMeta(int $objectId, int $value, int $prevValue): bool
    {
        return update_post_meta($objectId, Site::META_KEY_SITE_ID, $value, $prevValue);
    }
}
