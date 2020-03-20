<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Filters;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\Tests\TestCase;
use PHPUnit\Framework\Exception;

class SiteTest extends TestCase
{
    public function testInstance()
    {
        $testee = new Site();

        try {
            self::assertInstanceOf(Site::class, $testee);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
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
        $testee = $this->createPartialMock(Site::class, ['id']);

        $testee
            ->expects($this->once())
            ->method('id')
            ->willReturn(1);

        /** @var Site $testee */
        $idSitePrefix = $testee->idSitePrefix();

        self::assertSame(1 . Site::SITE_ID_PREFIX_RIGHT_PAD, $idSitePrefix);
    }
}
