<?php # -*- coding: utf-8 -*-
// phpcs:disable

namespace MultisiteGlobalMedia\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use MultisiteGlobalMedia\Attachment;
use MultisiteGlobalMedia\Site;
use MultisiteGlobalMedia\SiteSwitcher;

class AttachmentTest extends \MultisiteGlobalMedia\Tests\TestCase
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
            ->willReturn('1' . Site::SITE_ID_PREFIX_RIGHT_PAD);

        $attachmentDataMock = [
            'id' => 'ID',
            'editLink' => true,
            'nonces' => [
                'update' => true,
                'edit' => true,
                'delete' => true,
            ],
        ];
        $attachmentDataExpected = [
            'id' => '1' . Site::SITE_ID_PREFIX_RIGHT_PAD . 'ID',
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

    public function testAjaxQueryAttachmentsCallsWordPressAjaxQueryAttachments()
    {
        Functions\expect('wp_ajax_query_attachments')
            ->once();

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        $testee->ajaxQueryAttachments();
    }

    public function testsAjaxQueryAttachmentsAddFilterWpPrepareAttachmentForJs()
    {
        $_REQUEST = [
            'query' => [
                'global_media' => true,
            ],
        ];

        Functions\stubs([
            'wp_ajax_query_attachments' => true,
        ]);

        Functions\expect('wp_unslash')
            ->once()
            ->with($_REQUEST['query'])
            ->andReturnFirstArg();

        Functions\expect('switch_to_blog')
            ->once()
            ->with(1);

        $site = $this->createMock(Site::class);
        $siteSwitcher = $this->createMock(SiteSwitcher::class);
        $testee = new Attachment($site, $siteSwitcher);

        $site
            ->expects($this->once())
            ->method('id')
            ->willReturn(1);

        Filters\expectAdded('wp_prepare_attachment_for_js')
            ->with([$testee, 'prepareAttachmentForJs'], 0);

        $testee->ajaxQueryAttachments();
    }

    public function testsAjaxQueryAttachmentsNeverAddFilterWpPrepareAttachmentForJs()
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
}
