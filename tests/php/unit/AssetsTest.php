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
        Functions\expect('get_current_screen')
            ->once()
            ->andReturn((object)[
                'base' => 'post',
            ]);

        Functions\expect('wp_register_script')
            ->once()
            ->with(
                'global_media',
                'url/assets/js/global-media.js',
                ['media-views'],
                filemtime('url/assets/js/global-media.js'),
                true
            );

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with('global_media');

        $pluginProperties = $this->createPartialMock(PluginProperties::class, ['dirUrl']);

        $pluginProperties
            ->expects($this->once())
            ->method('dirUrl')
            ->willReturn('url');

        $testee = new Assets($pluginProperties);

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
        Functions\expect('get_current_screen')
            ->once()
            ->andReturn((object)[
                'base' => 'post',
            ]);

        Functions\expect('wp_register_style')
            ->once()
            ->with(
                'global_media',
                'url/assets/css/global-media.css',
                [],
                '0.1'
            );

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with('global_media');

        $pluginProperties = $this->createPartialMock(PluginProperties::class, ['dirUrl']);

        $pluginProperties
            ->expects($this->once())
            ->method('dirUrl')
            ->willReturn('url');

        $testee = new Assets($pluginProperties);

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
