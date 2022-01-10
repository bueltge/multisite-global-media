<?php

# -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Functions;
use MultisiteGlobalMedia\ACF\Image;
use MultisiteGlobalMedia\Plugin;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\SiteSwitcher;
use MultisiteGlobalMedia\Tests\TestCase;

class AdvancedCustomFieldTest extends TestCase
{
    public function testBootstrap()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);

        $testee = new Plugin('null');
        $testee->acfBootstrap($site, $siteSwitcher);
        self::assertEquals(10, has_action('after_setup_theme', 'function()'));
    }

    public function testAcfLoadValue()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);
        $store = $this->createMock(\ACF_Data::class);

        $testee = new Image($site, $siteSwitcher, $store);

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

    protected function setUp()
    {
        parent::setUp();

        define('ABSPATH', 1); // ACF quits early without it
        include_once dirname(__DIR__, 3) . '/vendor/wpackagist-plugin/advanced-custom-fields/includes/class-acf-data.php';
    }
}
