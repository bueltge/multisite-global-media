<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class SingleSwitcher
 */
class SingleSwitcher implements SiteSwitcher
{
    /**
     * @var bool
     */
    private $switched = false;

    /**
     * Switch to blog if needed
     *
     * @param int $siteId
     */
    public function switchToBlog(int $siteId)
    {
        if (get_current_blog_id() === $siteId) {
            return;
        }

        switch_to_blog($siteId);

        $this->switched = true;
    }

    /**
     * Restore the current blog if needed
     */
    public function restoreBlog()
    {
        if (!$this->switched) {
            return;
        }

        restore_current_blog();

        $this->switched = false;
    }
}
