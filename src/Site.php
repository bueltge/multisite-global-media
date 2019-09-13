<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class Site
 */
class Site
{
    const SITE_ID = 'global_media.site_id';
    const META_KEY_SITE_ID = 'global_media_site_id';

    const SITE_ID_PREFIX_RIGHT_PAD = 00000;

    /**
     * Return the ID of site that store the media files.
     *
     * @since  2017-12-01
     * @return integer The site ID.
     */
    public function id(): int
    {
        return (int)apply_filters(self::SITE_ID, 1);
    }

    /**
     * Return the site id prefix for attachments
     *
     * @return string
     */
    public function idSitePrefix(): string
    {
        return $this->id() . self::SITE_ID_PREFIX_RIGHT_PAD;
    }

    /**
     * Returns whether or not we're currently on the network media library site,
     * regardless of any switching that's occurred.
     *
     * `$current_blog` can be used to determine the "actual" site as it doesn't
     * change when switching sites.
     *
     * @return bool Whether we're on the network media library site.
     */
    public function isMediaSite(): bool
    {
        return ($this->id() === (int)$GLOBALS['current_blog']->blog_id);
    }
}
