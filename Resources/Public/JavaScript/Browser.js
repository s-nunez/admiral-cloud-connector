/**
 * Module: TYPO3/CMS/AdmiralcloudConnector/Browser
 */
define(['jquery',
    'nprogress',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/Backend/Notification'
], function ($, NProgress, Modal, Notification) {
    'use strict';
    /**
     * The main CompactView object for Bynder
     *
     * @type {{compactViewUrl: string, inlineButton: string, title: string}}
     * @exports TYPO3/CMS/Bynder/CompactView
     */
    var Browser = {
        overviewButton: '.t3js-admiralcloud-browser-btn.overview',
        uploadButton: '.t3js-admiralcloud-browser-btn.upload',
        browserUrl: '',
        title: 'Admiralcloud'
    };

    /**
     * Initialize all variables and listeners for CompactView
     *
     * @private
     */
    Browser.initialize = function () {
        var $button = $(Browser.overviewButton);

        var $uploadButton = $(Browser.uploadButton);

        // Add all listeners based on inline button
        $button.on('click', function (event) {
            Browser.browserUrl = $button.data('admiralcloudBrowserUrl');
            Browser.open();
        });
        $uploadButton.on('click', function (event) {
            Browser.browserUrl = $uploadButton.data('admiralcloudBrowserUrl');
            Browser.open();
        });

        $(document).on('AdmiralcloudBrowserAddMedia', function (event) {
            //console.log('received', event.detail.media);
            var target = event.detail.target;
            var media = event.detail.media;
            if (target && media) {
                Browser.addMedia(target, media);
            }
        });
    };

    /**
     * Open Compact View through CompactViewController
     *
     * @private
     */
    Browser.open = function () {
        Modal.advanced({
            type: Modal.types.ajax,
            title: Browser.title,
            content: Browser.browserUrl,
            size: Modal.sizes.full
        });
    };

    /**
     * Add media to irre element in frontend for possible saving
     *
     * @param {String} target
     * @param {Array} media
     *
     * @private
     */
    Browser.addMedia = function (target, media) {
        return $.ajax({
            type: 'POST',
            url: TYPO3.settings.ajaxUrls['admiralcloud_browser_get_files'],
            dataType: 'json',
            data: {
                target: target,
                media: media
            },
            beforeSend: function () {
                Modal.dismiss();
                NProgress.start();
            },
            success: function (data) {
                if (typeof data.files === 'object' && data.files.length) {
                    inline.importElementMultiple(
                        target,
                        'sys_file',
                        data.files,
                        'file'
                    );
                }

                if (data.message) {
                    Notification.success('', data.message, Notification.duration);
                }
            },
            error: function (xhr, type) {
                var data = xhr.responseJSON || {};
                if (data.error) {
                    Notification.error('', data.error, Notification.duration);
                } else {
                    Notification.error('', 'Unknown ' + type + ' occured.', Notification.duration);
                }
            },
            complete: function () {
                NProgress.done();
            }
        });
    };

    Browser.initialize();
    return Browser;
});
