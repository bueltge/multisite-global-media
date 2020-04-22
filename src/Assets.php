<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class Assets
 */
class Assets
{

    /**
     * @var PluginProperties
     */
    private $pluginProperties;

    /**
     * Assets constructor
     *
     * @param PluginProperties $pluginProperties
     */
    public function __construct(PluginProperties $pluginProperties)
    {
        $this->pluginProperties = $pluginProperties;
    }

    /**
     * Enqueue script for media modal
     *
     * @since  2015-01-26
     */
    public function enqueueScripts()
    {
        if ('post' !== get_current_screen()->base) {
            return;
        }

        $scriptFile = '/assets/js/global-media.js';
        wp_register_script(
            'global_media',
            $this->pluginProperties->dirUrl() . $scriptFile,
            ['media-views'],
            filemtime($this->pluginProperties->dirPath() . $scriptFile),
            true
        );
        wp_enqueue_script('global_media');
    }

    /**
     * Enqueue script for media modal
     *
     * @since   2015-02-27
     */
    public function enqueueStyles()
    {
        if ('post' !== get_current_screen()->base) {
            return;
        }

        $styleFile = '/assets/css/global-media.css';
        wp_register_style(
            'global_media',
            $this->pluginProperties->dirUrl() . $styleFile,
            [],
            filemtime($this->pluginProperties->dirPath() . $styleFile)
        );
        wp_enqueue_style('global_media');
    }
}
