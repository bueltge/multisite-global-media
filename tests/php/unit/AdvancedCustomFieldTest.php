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
        $store = $this->createMock(\ACF_Data::class);

        Functions\expect('acf_get_store')
            ->once()
            ->andReturn($store);

        $testee = new Plugin('null');

        $testee->acfBootstrap($site, $siteSwitcher);
        self::assertEquals(10, has_filter('acf/load_value/type=image', 'MultisiteGlobalMedia\ACF\Image->acfLoadValue()'));
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

        include_once dirname(__DIR__, 2) . '/stubs/acf.php';
    }
}
