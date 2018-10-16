<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class SiteSwitcher
 */
interface SiteSwitcher
{
    public function switchToBlog(int $siteId);

    public function restoreBlog();
}
