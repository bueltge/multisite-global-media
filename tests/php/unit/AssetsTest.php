<?php # -*- coding: utf-8 -*-
// phpcs:disable

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Functions;
use MultisiteGlobalMedia\Assets;
use MultisiteGlobalMedia\PluginProperties;
use MultisiteGlobalMedia\Tests\TestCase;

class AssetsTest extends TestCase
{
    public function testInstance()
    {
        $pluginProperties = $this->createMock(PluginProperties::class);
        $testee = new Assets($pluginProperties);

        self::assertInstanceOf(Assets::class, $testee);
    }

    public function testEnqueueScriptsOnEditPostScreen()
    {
        $pluginProperties = $this->createPartialMock(PluginProperties::class, ['dirUrl', 'dirPath']);
        $testee = new Assets($pluginProperties);

        Functions\expect('get_current_screen')
            ->once()
            ->andReturn((object)[
                'base' => 'post',
            ]);

        Functions\expect('wp_register_script')
            ->once()
            ->with(
                'global_media',
                'asset_url/assets/js/global-media.js',
                ['media-views'],
                'filemtime',
                true
            );

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('global_media');

        Functions\expect('filemtime')
            ->once()
            ->with('asset_path/assets/js/global-media.js')
            ->andReturn('filemtime');

        $pluginProperties
            ->expects($this->once())
            ->method('dirUrl')
            ->willReturn('asset_url');

        $pluginProperties
            ->expects($this->once())
            ->method('dirPath')
            ->willReturn('asset_path');

        $testee->enqueueScripts();
    }

    public function testEnqueueScriptsNotCalledIfCurrentEditScreenIsNotPost()
    {
        Functions\expect('get_current_screen')
            ->once()
            ->andReturn((object)[
                'base' => 'not_post_edit_screen',
            ]);

        Functions\expect('wp_register_script')
            ->never();

        Functions\expect('wp_enqueue_script')
            ->never();

        $pluginProperties = $this->createMock(PluginProperties::class);
        $testee = new Assets($pluginProperties);

        $testee->enqueueScripts();
    }

    public function testEnqueueStylesOnEditPostScreen()
    {
        $pluginProperties = $this->createPartialMock(PluginProperties::class, ['dirUrl', 'dirPath']);
        $testee = new Assets($pluginProperties);

        Functions\expect('get_current_screen')
            ->once()
            ->andReturn((object)[
                'base' => 'post',
            ]);

        Functions\expect('wp_register_style')
            ->once()
            ->with(
                'global_media',
                'asset_url/assets/css/global-media.css',
                [],
                'filemtime'
            );

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('global_media');

        Functions\expect('filemtime')
            ->once()
            ->with('asset_path/assets/css/global-media.css')
            ->andReturn('filemtime');

        $pluginProperties
            ->expects($this->once())
            ->method('dirUrl')
            ->willReturn('asset_url');

        $pluginProperties
            ->expects($this->once())
            ->method('dirPath')
            ->willReturn('asset_path');

        $testee->enqueueStyles();
    }

    public function testEnqueueStylesNotCalledIfCurrentEditScreenIsNotPost()
    {
        Functions\expect('get_current_screen')
            ->once()
            ->andReturn((object)[
                'base' => 'not_post_edit_screen',
            ]);

        Functions\expect('wp_register_style')
            ->never();

        Functions\expect('wp_enqueue_style')
            ->never();

        $pluginProperties = $this->createMock(PluginProperties::class);
        $testee = new Assets($pluginProperties);

        $testee->enqueueStyles();
    }
}
