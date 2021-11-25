<?php # -*- coding: utf-8 -*-

namespace MultisiteGlobalMedia\ACF;

use MultisiteGlobalMedia\Helper;
use MultisiteGlobalMedia\SingleSwitcher;
use MultisiteGlobalMedia\Site;

class Image
{

    use Helper;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var SingleSwitcher
     */
    private $siteSwitcher;

    /**
     * @var \ACF_Data
     */
    private $store;

    /**
     * Image constructor
     *
     * @param Site $site
     * @param SingleSwitcher $siteSwitcher
     */
    public function __construct(Site $site, SingleSwitcher $siteSwitcher)
    {
        $this->site = $site;
        $this->siteSwitcher = $siteSwitcher;
        $this->store = acf_get_store('values');
    }

    // Fetch ACF file fields across sites when the global prefix is used.
    // We hook into 'load_value' which usually runs just before 'format_value'.
    // Then get the formatted output of the field in the global media site's context,
    // and store it in ACF's cache. So when format_value tries to use this value,
    // it will find the formatted one already in the cache.
    // This works around acf_format_value requiring a valid att ID as input, but
    // returning a string/array as output, so it can't be easily filtered.
    public function acfLoadValue($value, $post_id, $field)
    {
        if ($this->idPrefixIncludedInAttachmentId((int)$value, $this->site->idSitePrefix())) {
            $formatted = $this->stripSiteIdPrefixFromAttachmentId($this->site->idSitePrefix(), $value);
            $this->siteSwitcher->switchToBlog($this->site->id());
            $formatted = acf_format_value($formatted, $post_id, $field);
            $this->siteSwitcher->restoreBlog();
            $this->store->set("$post_id:{$field['name']}:formatted", $formatted);
        }
        // This filter doesn't modify the loaded value. Return it as-is.
        return $value;
    }
}
