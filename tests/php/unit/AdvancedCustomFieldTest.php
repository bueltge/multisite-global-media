<?php

# -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Functions;
use MultisiteGlobalMedia\ACF\Image;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\SiteSwitcher;
use MultisiteGlobalMedia\Tests\TestCase;

define('ABSPATH', 1); // ACF quits early without it
include_once dirname(__DIR__, 3) . '/vendor/wpackagist-plugin/advanced-custom-fields/includes/class-acf-data.php';

class AdvancedCustomFieldTest extends TestCase
{
    public function testBootstrap()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);

        $testee = Image::bootstrap($site, $siteSwitcher);
        self::assertEquals(10, has_action('after_setup_theme', [$testee, 'afterSetupTheme']));
    }

    public function testAfterSetupTheme()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);

        Functions\expect('function_exists')
            ->once()
            ->with('get_field')
            ->andReturn(true);

        Functions\expect('acf_get_store')
            ->once();

        $testee = Image::bootstrap($site, $siteSwitcher);
        $testee->afterSetupTheme();
        self::assertEquals(10, has_filter('acf/load_value/type=image', [$testee, 'acfLoadValue']));
    }

    public function testAfterSetupThemeWithoutACF()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);

        $testee = Image::bootstrap($site, $siteSwitcher);
        $testee->afterSetupTheme();
        self::assertFalse(has_filter('acf/load_value/type=image', [$testee, 'acfLoadValue']));
    }

    public function testAcfLoadValue()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);
        $store = $this->createMock(\ACF_Data::class);

        Functions\expect('function_exists')
            ->once()
            ->with('get_field')
            ->andReturn(true);

        Functions\expect('acf_get_store')
            ->once()
            ->andReturn($store);

        $testee = Image::bootstrap($site, $siteSwitcher);
        $testee->afterSetupTheme();

        $field = ['name' => 'image'];
        $attachmentId = 1 . Site::SITE_ID_PREFIX_RIGHT_PAD . 1;

        $site
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $site
            ->expects($this->once())
            ->method('id')
            ->willReturn(1);

        $siteSwitcher
            ->expects($this->once())
            ->method('switchToBlog')
            ->with(1);

        $siteSwitcher
            ->expects($this->once())
            ->method('restoreBlog');

        $store
            ->expects($this->once())
            ->method('set')
            ->with('123:image:formatted', 'formattedImage');

        Functions\expect('acf_format_value')
            ->once()
            ->with(1, '123', $field)
            ->andReturn('formattedImage');

        $returnedValue = $testee->acfLoadValue($attachmentId, '123', $field);
        self::assertSame($attachmentId, $returnedValue);
    }
}
