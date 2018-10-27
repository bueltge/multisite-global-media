<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class CompareHelper
 */
trait Helper
{
    /**
     * Check if the given site Id prefix exists into the give attachment id
     *
     * @param int $attachmentId
     * @param string $siteIdPrefix
     * @return bool
     */
    private function idPrefixIncludedInAttachmentId(
        int $attachmentId,
        string $siteIdPrefix
    ): bool {

        return false !== strpos((string)$attachmentId, $siteIdPrefix);
    }

    /**
     * Remove the site Id prefix from the give attachment id
     *
     * @param string $idPrefix
     * @param int $attachmentId
     * @return int
     */
    private function stripSiteIdPrefixFromAttachmentId(string $idPrefix, int $attachmentId): int
    {
        return (int)str_replace($idPrefix, '', (string)$attachmentId);
    }

    /**
     * Retrieve the site id from the give object id
     *
     * @param int $objectId
     * @param int $default
     * @return int
     */
    private function siteIdByMetaObject(int $objectId, int $default): int
    {
        list($storedObjectId, $siteId) = get_post_meta(
            $objectId,
            Site::META_KEY_SITE_ID,
            true
        );

        if ((int)$storedObjectId !== $objectId) {
            delete_post_meta($objectId, Site::META_KEY_SITE_ID);
            $siteId = 0;
        }

        return $siteId ?: $default;
    }

    /**
     * Store the site id into the object
     *
     * @param int $objectId
     * @param int $siteId
     * @param int $prevValue
     * @return bool
     */
    private function storeSiteIdIntoObjectMeta(int $objectId, int $siteId, int $prevValue = 0): bool
    {
        if (-1 === $objectId) {
            return true;
        }

        $value = [$objectId, $siteId];

        if (!metadata_exists('post', $objectId, Site::META_KEY_SITE_ID)) {
            return (bool)add_post_meta($objectId, Site::META_KEY_SITE_ID, $value, true);
        }

        return (bool)update_post_meta($objectId, Site::META_KEY_SITE_ID, $value, $prevValue);
    }

//    private function prefixAttachmentWithSiteIdFromObjectMeta(
//        int $attachmentId,
//        string $idPrefix
//    ): string {
//
//        if (!$this->idPrefixIncludedInAttachmentId($attachmentId, $idPrefix)) {
//            $attachmentId = $idPrefix . $attachmentId;
//        }
//
//        return (string)$attachmentId;
//    }
}
