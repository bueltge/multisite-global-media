<?php

# -*- coding: utf-8 -*-

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
}
