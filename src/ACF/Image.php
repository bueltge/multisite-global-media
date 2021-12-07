<?php

# -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia\ACF;

use MultisiteGlobalMedia\Helper;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\SiteSwitcher;

class Image
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
     * @var \ACF_Data
     */
    private $store;

    /**
     * Integration for ACF, specifically Image fields
     *
     * @param Site $site
     * @param SiteSwitcher $siteSwitcher
     */
    public function __construct(Site $site, SiteSwitcher $siteSwitcher, \ACF_Data $store)
    {
        $this->site = $site;
        $this->siteSwitcher = $siteSwitcher;
        $this->store = $store;
    }

    /**
     * Fetch ACF file fields across sites when the global prefix is used.
     *
     * We hook into 'load_value' which usually runs just before 'format_value'.
     * Then get the formatted output of the field in the global media site's context,
     * and store it in ACF's cache. So when format_value tries to use this value,
     * it will find the formatted one already in the cache.
     * This works around acf_format_value requiring a valid att ID as input, but
     * returning a string/array as output, so it can't be easily filtered.
     *
     * @param $value
     * @param $post_id
     * @param $field
     * @return mixed
     */
    public function acfLoadValue($value, $post_id, $field)
    {
        if ($this->idPrefixIncludedInAttachmentId((int)$value, $this->site->idSitePrefix())) {
            $formatted = $this->stripSiteIdPrefixFromAttachmentId($this->site->idSitePrefix(), (int)$value);
            $this->siteSwitcher->switchToBlog($this->site->id());
            $formatted = acf_format_value($formatted, $post_id, $field);
            $this->siteSwitcher->restoreBlog();
            $this->store->set("$post_id:{$field['name']}:formatted", $formatted);
        }

        // This filter doesn't modify the loaded value. Return it as-is.
        return $value;
    }
}
