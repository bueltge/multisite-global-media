<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Functions;
use MultisiteGlobalMedia\Assets;
use MultisiteGlobalMedia\PluginProperties;
use MultisiteGlobalMedia\Tests\TestCase;

class AssetsTest extends TestCase
{
    public function testInstance()
    {
        $pluginProperties = $this->createStub(PluginProperties::class);
        $testee = new Assets($pluginProperties);

        self::assertInstanceOf(Assets::class, $testee);
    }

    public function testEnqueueScriptsInAdmin()
    {
        $pluginProperties = $this->createPluginPropertiesMock();
        $testee = new Assets($pluginProperties);

        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

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


        $testee->enqueueScripts();
    }

    public function testEnqueueScriptsNotCalledOutsideAdmin()
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Functions\expect('wp_register_script')
            ->never();

        Functions\expect('wp_enqueue_script')
            ->never();

        $pluginProperties = $this->createStub(PluginProperties::class);
        $testee = new Assets($pluginProperties);

        $testee->enqueueScripts();
    }

    public function testEnqueueStylesInAdmin()
    {
        $pluginProperties = $this->createPluginPropertiesMock();
        $testee = new Assets($pluginProperties);

        Functions\expect('is_admin')
            ->once()
            ->andReturn(true);

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


        $testee->enqueueStyles();
    }

    public function testEnqueueStylesNotCalledOutsideAdmin()
    {
        Functions\expect('is_admin')
            ->once()
            ->andReturn(false);

        Functions\expect('wp_register_style')
            ->never();

        Functions\expect('wp_enqueue_style')
            ->never();

        $pluginProperties = $this->createStub(PluginProperties::class);
        $testee = new Assets($pluginProperties);

        $testee->enqueueStyles();
    }

    /**
     * PluginProperties resolves dirUrl()/dirPath() via __call(), so those can't be mocked directly.
     */
    private function createPluginPropertiesMock(): PluginProperties
    {
        $pluginProperties = $this->createPartialMock(PluginProperties::class, ['__call']);
        $pluginProperties
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap([
                [PluginProperties::DIR_URL, [], 'asset_url'],
                [PluginProperties::DIR_PATH, [], 'asset_path'],
            ]);

        return $pluginProperties;
    }
}