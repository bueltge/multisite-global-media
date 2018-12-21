<?php # -*- coding: utf-8 -*-
// phpcs:disable

namespace MultisiteGlobalMedia;

use Brain\Monkey\Filters;
use MultisiteGlobalMedia\Tests\TestCase;

class SiteTest extends TestCase
{
    public function testInstance()
    {
        $testee = new Site();

        self::assertInstanceOf(Site::class, $testee);
    }

    public function testSiteIdFilterIsApplied()
    {
        Filters\expectApplied(Site::SITE_ID)
            ->once()
            ->with(1);

        $testee = new Site();

        $id = $testee->id();

        self::assertSame(1, $id);
    }

    public function testIdSitePrefixReturnSiteIdRightPaddedWithZeros()
    {
        $site = \Mockery::mock('MultisiteGlobalMedia\\Site[id]')
            ->makePartial();

        $site
            ->shouldReceive('id')
            ->once()
            ->andReturn(1);

        $idSitePrefix = $site->idSitePrefix();

        self::assertSame(1 . Site::SITE_ID_PREFIX_RIGHT_PAD, $idSitePrefix);
    }
}
