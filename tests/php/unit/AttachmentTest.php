<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use MultisiteGlobalMedia\Attachment;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\SiteSwitcher;
use MultisiteGlobalMedia\Tests\TestCase;

class AttachmentTest extends TestCase
{
    public function testInstance()
    {
        $testee = new Attachment(
            $this->createMock(Site::class),
            $this->createMock(SiteSwitcher::class)
        );

        self::assertInstanceOf(Attachment::class, $testee);
    }

    public function testPrepareAttachmentForJs()
    {
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $attachmentDataMock = [
            'id' => '',
            'editLink' => true,
            'nonces' => [
                'update' => true,
                'edit' => true,
                'delete' => true,
            ],
        ];
        $attachmentDataExpected = [
            'id' => intval(1 . Site::SITE_ID_PREFIX_RIGHT_PAD),
            'editLink' => false,
            'nonces' => [
                'update' => false,
                'edit' => false,
                'delete' => false,
            ],
        ];

        $testee = new Attachment($site, $siteSwitcher);
        $response = $testee->prepareAttachmentForJs($attachmentDataMock);

        self::assertSame($attachmentDataExpected, $response);
    }

    public function testAjaxQueryAttachments()
    {
        $_REQUEST = [
            'query' => [
                'global_media' => true,
            ],
        ];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_REQUEST['query'])
            ->andReturnFirstArg();

        Functions\expect('switch_to_blog')
            ->once()
            ->with(1);

        $site
            ->expects($this->once())
            ->method('id')
            ->willReturn(1);

        Filters\expectAdded('wp_prepare_attachment_for_js')
            ->with([$testee, 'prepareAttachmentForJs'], 0);

        Functions\expect('wp_ajax_query_attachments')
            ->once();

        $testee->ajaxQueryAttachments();
    }

    public function testAjaxGetAttachment()
    {
        $_REQUEST = ['id' => 1 . Site::SITE_ID_PREFIX_RIGHT_PAD . 1];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_REQUEST['id'])
            ->andReturnFirstArg();

        Functions\expect('wp_ajax_get_attachment')
            ->once();

        Filters\expectAdded('wp_prepare_attachment_for_js')
            ->with([$testee, 'prepareAttachmentForJs'], 0);

        $site
            ->expects($this->once())
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

        $testee->ajaxGetAttachment();

        self::assertSame(['id' => 1], $_REQUEST);
    }

    public function testAjaxGetAttachmentPrepareForJsFilterIsNotAddedIfSiteIdPrefixNotMatch()
    {
        $_REQUEST = ['id' => 2 . Site::SITE_ID_PREFIX_RIGHT_PAD . 1];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_REQUEST['id'])
            ->andReturnFirstArg();

        Functions\stubs([
            'wp_ajax_get_attachment' => true,
        ]);

        Filters\expectAdded('wp_prepare_attachment_for_js')
            ->never();

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $siteSwitcher
            ->expects($this->never())
            ->method('switchToBlog');

        $siteSwitcher
            ->expects($this->never())
            ->method('restoreBlog');

        $testee->ajaxGetAttachment();

        self::assertSame(['id' => 2 . Site::SITE_ID_PREFIX_RIGHT_PAD . 1], $_REQUEST);
    }

    public function testAjaxGetAttachmentPrepareForJsFilterIsNotAddedIfSiteIdPrefixNotFound()
    {
        $_REQUEST = ['id' => 1];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_REQUEST['id'])
            ->andReturnFirstArg();

        Functions\stubs([
            'wp_ajax_get_attachment' => true,
        ]);

        Filters\expectAdded('wp_prepare_attachment_for_js')
            ->never();

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $siteSwitcher
            ->expects($this->never())
            ->method('switchToBlog');

        $siteSwitcher
            ->expects($this->never())
            ->method('restoreBlog');

        $testee->ajaxGetAttachment();

        self::assertSame(['id' => 1], $_REQUEST);
    }

    public function testAjaxQueryAttachmentsCallsWordPressAjaxQueryAttachments()
    {
        Functions\expect('wp_ajax_query_attachments')
            ->once();

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        $testee->ajaxQueryAttachments();
    }

    public function testAjaxQueryAttachmentsNeverAddFilterWpPrepareAttachmentForJs()
    {
        $_REQUEST = [
            'query' => [
                'global_media' => false,
            ],
        ];

        Functions\stubs([
            'wp_ajax_query_attachments' => true,
        ]);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_REQUEST['query'])
            ->andReturnFirstArg();

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Filters\expectAdded('wp_prepare_attachment_for_js')
            ->never();

        $testee->ajaxQueryAttachments();
    }

    public function testAjaxSendAttachmentToEditor()
    {
        $_POST = [
            'attachment' => [
                'id' => 1 . Site::SITE_ID_PREFIX_RIGHT_PAD . 1,
            ],
        ];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_POST['attachment'])
            ->andReturnFirstArg();

        Functions\expect('wp_slash')
            ->once()
            ->with(['id' => 1])
            ->andReturnFirstArg();

        Functions\expect('switch_to_blog')
            ->once()
            ->with(1);

        Functions\expect('wp_ajax_send_attachment_to_editor')
            ->once();

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $site
            ->expects($this->once())
            ->method('id')
            ->willReturn(1);

        Filters\expectAdded('mediaSendToEditor')
            ->once()
            ->with([$testee, 'mediaSendToEditor'], 10, 2);

        $testee->ajaxSendAttachmentToEditor();

        self::assertSame(
            [
                'attachment' => ['id' => 1,],
            ],
            $_POST
        );
    }

    public function testAjaxSendAttachmentToEditorDoesNotAddFilterIfSiteIdPrefixNotFound()
    {
        $_POST = [
            'attachment' => [
                'id' => 1,
            ],
        ];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\stubs([
            'wp_unslash' => $_POST['attachment'],
            'wp_ajax_send_attachment_to_editor' => true,
        ]);

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        Filters\expectAdded('mediaSendToEditor')
            ->never();

        $testee->ajaxSendAttachmentToEditor();
    }

    public function testAjaxSendAttachmentToEditorDoesNotAddFilterIfSiteIdPrefixDoesNotMatch()
    {
        $_POST = [
            'attachment' => [
                'id' => 2 . Site::SITE_ID_PREFIX_RIGHT_PAD,
            ],
        ];

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\stubs([
            'wp_unslash' => $_POST['attachment'],
            'wp_ajax_send_attachment_to_editor' => true,
        ]);

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        Filters\expectAdded('mediaSendToEditor')
            ->never();

        $testee->ajaxSendAttachmentToEditor();
    }

    public function testMediaSendToEditor()
    {
        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        $html = '<tag class="wp-image-1"></tag>';

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn(1 . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $response = $testee->mediaSendToEditor($html, 1);

        self::assertSame('<tag class="wp-image-1000001"></tag>', $response);
    }

    public function testAttachmentImageSrc()
    {
        $attachmentId = 1 . Site::SITE_ID_PREFIX_RIGHT_PAD . 1;
        $responseMock = [
            'attachment_url',
            100,
            100,
        ];
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_get_attachment_image_src')
            ->once()
            ->with(1, 'attachment-size', false)
            ->andReturn($responseMock);

        $site
            ->expects($this->once())
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

        $response = $testee->attachmentImageSrc(
            false,
            $attachmentId,
            'attachment-size',
            false
        );

        self::assertSame($responseMock, $response);
    }

    public function testAttachmentImageSrcReturnsOriginalImageDataIfGlobalSiteIdNotFound()
    {
        $siteId = 2;
        $globalSiteId = 1;

        $attachmentId = $siteId . Site::SITE_ID_PREFIX_RIGHT_PAD . 1;
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $site = $this->createMock(Site::class);
        $testee = new Attachment($site, $siteSwitcher);

        Functions\expect('wp_get_attachment_image_src')
            ->never();

        $site
            ->expects($this->once())
            ->method('idSitePrefix')
            ->willReturn($globalSiteId . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $response = $testee->attachmentImageSrc(
            ['attachment_url', 100, 100],
            $attachmentId,
            'attachment-size',
            false
        );

        self::assertSame(['attachment_url', 100, 100], $response);
    }
}
