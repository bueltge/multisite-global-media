<?php

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class SiteSwitcher
 */
interface SiteSwitcher
{
    public function switchToBlog(int $siteId): void;

    public function restoreBlog(): void;
}
