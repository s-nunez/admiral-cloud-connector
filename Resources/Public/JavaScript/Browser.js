define("optional", [], {
    load : function (moduleName, parentRequire, onload, config){

        var onLoadSuccess = function(moduleInstance){
            // Module successfully loaded, call the onload callback so that
            // requirejs can work its internal magic.
            onload(moduleInstance);
        }

        var onLoadFailure = function(err){
            // optional module failed to load.
            var failedId = err.requireModules && err.requireModules[0];
            console.warn("Could not load optional module: " + failedId);

            // Undefine the module to cleanup internal stuff in requireJS
            requirejs.undef(failedId);

            // Now define the module instance as a simple empty object
            // (NOTE: you can return any other value you want here)
            define(failedId, [], function(){return {};});

            // Now require the module make sure that requireJS thinks
            // that is it loaded. Since we've just defined it, requirejs
            // will not attempt to download any more script files and
            // will just call the onLoadSuccess handler immediately
            parentRequire([failedId], onLoadSuccess);
        }

        parentRequire([moduleName], onLoadSuccess, onLoadFailure);
    }
});

require([
    'jquery',
    'nprogress',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/Backend/Notification',
    'TYPO3/CMS/Recordlist/LinkBrowser',
    'optional!TYPO3/CMS/Backend/Utility/MessageUtility'
], function ($, NProgress, Modal, Notification, LinkBrowser,MessageUtility) {
    'use strict';

    /**
     * @type {{currentLink: string, identifier: string, linkRecord: function, linkCurrent: function}}
     */
    var RecordLinkHandler = {
        currentLink: '',
        identifier: '',

        /**
         * @param {Event} event
         */
        linkRecord: function(event) {
            event.preventDefault();

            var data = $(this).parents('span').data();
            LinkBrowser.finalizeFunction(RecordLinkHandler.identifier + data.uid);
        },

        /**
         * @param {Event} event
         */
        linkCurrent: function(event) {
            event.preventDefault();

            LinkBrowser.finalizeFunction(RecordLinkHandler.currentLink);
        }
    };

    /**
     * The main CompactView object for AdmiralCloud
     *
     * @type {{compactViewUrl: string, inlineButton: string, title: string}}
     * @exports TYPO3/CMS/AdmiralCloud/CompactView
     */
    var Browser = {
        overviewButton: '.t3js-admiral_cloud-browser-btn.overview',
        uploadButton: '.t3js-admiral_cloud-browser-btn.upload',
        cropButton: '.t3js-admiral_cloud-browser-btn.crop',
        rteLinkButton: '.t3js-admiral_cloud-browser-btn.rte-link',
        browserUrl: '',
        title: 'AdmiralCloud',
        currentLink: '',
        /**
         * @param {Event} event
         */
        linkCurrent: function(event) {
            event.preventDefault();

            LinkBrowser.finalizeFunction(RecordLinkHandler.currentLink);
        }
    };

    /**
     * Initialize all variables and listeners for CompactView
     *
     * @private
     */
    Browser.initialize = function () {
        $('#iframeContainer').append($('#elAdmiralCloud'))

        // Add all listeners based on inline button
        $(document).on('click', Browser.overviewButton, function () {
            Browser.browserUrl = $(this).data('admiral_cloudBrowserUrl');
            Browser.open();
        });
        $(document).on('click', Browser.uploadButton, function () {
            Browser.browserUrl = $(this).data('admiral_cloudBrowserUrl');
            Browser.open();
        });
        $(document).on("click", Browser.cropButton, function () {
            Browser.browserUrl = $(this).data('admiral_cloudBrowserUrl');
            Browser.open();
        });
        $(document).on("click", Browser.rteLinkButton, function () {
            // Store if rte link should set to be downloaded
            window.rteLinkDownload = !document.getElementById('rteLinkDownload').checked;
            Browser.browserUrl = $(this).data('admiral_cloudBrowserUrl');
            Browser.open();
        });

        $(top.document).on('AdmiralCloudBrowserAddMedia', function (event) {
            //console.log('received', event.detail.media);
            var target = event.detail.target;
            var media = event.detail.media;
            var modus = event.detail.modus;

            if (modus === 'rte-link') {
                if (typeof inline !== 'undefined') {
                    if (LinkBrowser.thisScriptUrl !== undefined) {
                        Browser.getMediaPublicUrl(media);
                    }
                } else {
                    if (LinkBrowser.parameters !== undefined) {
                        Browser.getMediaPublicUrl(media);
                    }
                }
            }

            if (target && media && !modus) {
                Browser.addMedia(target, media);
            }

            if (target && media && modus === 'crop') {
                Browser.cropMedia(target, media);
            }
        });
        var body = $('body');
        Browser.currentLink = body.data('currentLink');
        $('input.t3js-linkCurrent').on('click', Browser.linkCurrent);
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
        $(parent.document).on("click", '.acModalParent', function () {
            //Modal.dismiss();
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
            url: TYPO3.settings.ajaxUrls['admiral_cloud_browser_get_files'],
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
                    if (typeof inline !== 'undefined') {
                        inline.importElementMultiple(
                            target,
                        'sys_file',
                        data.files,
                        'file'
                        );
                    } else {
                        data.files.forEach((fileId) => {
                            MessageUtility.MessageUtility.send({
                                objectGroup: target,
                                table: 'sys_file',
                                uid: fileId,
                                actionName: 'typo3:foreignRelation:insert'
                            });
                        });
                    }
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

    /**
     * Add media to irre element in frontend for possible saving
     *
     * @param {String} target
     * @param {Array} media
     *
     * @private
     */
    Browser.cropMedia = function (target, media) {
        return $.ajax({
            type: 'POST',
            url: TYPO3.settings.ajaxUrls['admiral_cloud_browser_crop_file'],
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
                if (data.cropperData.length && data.target.length) {
                    console.info(data);
                    $('#' + data.target).val(data.cropperData);
                    $('#' + data.target + '_image').attr('src',data.link);
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

    /**
     * Get public url from media
     *
     * @param {Array} media
     *
     * @private
     */
    Browser.getMediaPublicUrl = function (media) {
        console.info(TYPO3.settings.ajaxUrls['admiral_cloud_browser_get_media_public_url']);
        return $.ajax({
            type: 'POST',
            url: TYPO3.settings.ajaxUrls['admiral_cloud_browser_get_media_public_url'],
            dataType: 'json',
            data: {
                media: media,
                rteLinkDownload: window.rteLinkDownload
            },
            beforeSend: function () {
                Modal.dismiss();
                NProgress.start();
            },
            success: function (data) {
                if (data.publicUrl) {
                    LinkBrowser.finalizeFunction(data.publicUrl);
                } else {
                    Notification.error('', 'It was not possible to get the file public url.', Notification.duration);
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
