var iframeURL_HOST = document.getElementById('admiral_cloud-browser').getAttribute('data-iframeHost');
// overview => iframeURL_HOST + '/overview?cmsOrigin=' + btoa(location.protocol + '//' + location.host);
// upload => iframeURL_HOST + '/upload/files?cmsOrigin=' + btoa(location.protocol + '//' + location.host);
// The url will be set in variable iframeUrl in \CPSIT\AdmiralCloudConnector\Controller\Backend\BrowserController
var iframeURL_overview = document.getElementById('admiral_cloud-browser').getAttribute('data-iframeurl');
var readyCallbacks = [];
var elAdmiralCloud = document.querySelector('.acModalParent');
var admiralCloudAction = document.getElementById('admiral_cloud-browser').getAttribute('data-modus');
var irreObjectTarget = document.getElementById('admiral_cloud-browser').getAttribute('data-irreObject');
var currentIframeURL;

async function loadIframeWithAuthCode(device) {

    const resp = await fetch(document.getElementById('admiral_cloud-browser').getAttribute('data-typo3-ajax-url'), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            callbackUrl: document.getElementById('admiral_cloud-browser').getAttribute('data-iframeurl'),
            device,
        }),
    });

    const data = await resp.json();
    console.info(data);

    if (data.error !== undefined) {
        hideAC();

        // Remove iframe to close all connections and the next time try the authentication again
        $('#acModalParent').remove();

        // Show error message
        TYPO3.Notification.error('The authentication to AdmiralCloud was not possible.', data.error, 30);

        return;
    }

    applyAuthCode(data.code);
}

function applyAuthCode(code) {
    const iframeURL = currentIframeURL;
    load(currentIframeURL + '&code=' + code);
    currentIframeURL = iframeURL;
}

function load(iframeURL){
    mediaContainerId = document.getElementById('admiral_cloud-browser').getAttribute('data-mediaContainerId');
    embedLink = document.getElementById('admiral_cloud-browser').getAttribute('data-embedLink');

    if (elAdmiralCloud) {
        if (mediaContainerId && embedLink) {
            editImage(mediaContainerId, embedLink);
        }
        const iframe = elAdmiralCloud.querySelector('iframe');

        // If the requested URL is already open, simply show it
        if (currentIframeURL.includes(iframeURL)) {
            elAdmiralCloud.querySelector('.acBackdrop').classList.remove('hidden');
            executeReadyCallbacks();
            return;
        }

        // Otherwise navigate to the requested URL
        iframe.src = iframeURL;
        currentIframeURL = iframeURL;
        elAdmiralCloud.querySelector('.acBackdrop').classList.remove('hidden');
        return;
    }

    // Create a new iframe and authenticate
    var el = document.createElement('div');
    el.setAttribute('class','acModalParent');
    el.setAttribute('id','acModalParent');
    el.innerHTML = '<div class="acBackdrop"><iframe src="' + iframeURL + '&auth=1"></iframe><div class="close"><i class="fa fa-times"></i></div></div>';
    currentIframeURL = iframeURL;
    document.body.appendChild(el);

    el.addEventListener('click', () => hideAC());
    elAdmiralCloud = el;
    if (mediaContainerId && embedLink) {
        editImage(mediaContainerId, embedLink);
    }
    //const iframe = document.getElementById('elAdmiralCloud');
    //iframe.src = '';
    //iframe.src = document.getElementById('admiral_cloud-browser').getAttribute('data-iframeurl') + '&auth=1';
}

function editImage(mediaContainerId, embedLink) {
    readyCallbacks.unshift(function () {
        elAdmiralCloud.querySelector('iframe').contentWindow.postMessage(JSON.stringify({
            command: 'CROP_IMAGE',
            mediaContainerId,
            embedLink
        }), iframeURL_HOST);
    });
}

function executeReadyCallbacks() {
    console.info(readyCallbacks);
    while (readyCallbacks.length > 0) {
        readyCallbacks.pop().call();
    }
}

window.onmessage = function (e) {
    if(e.data) {
        var data = JSON.parse(e.data);
    }

    // Receive Auth Device-Identifier
    if (data.command === 'AUTH') {
        const {device} = data;
        loadIframeWithAuthCode(device);
        return;
    }

    // Receive severe Auth Failure -> Reload
    if (data.command === 'AUTH_FAILURE') {
        console.info('auth_failure');
        return;
    }

    // Receive Signal to execute Ready Callbacks
    if (data.command === 'READY') {
        mediaContainerId = document.getElementById('admiral_cloud-browser').getAttribute('data-mediaContainerId');
        embedLink = document.getElementById('admiral_cloud-browser').getAttribute('data-embedLink');
        if (mediaContainerId && embedLink) {
            editImage(mediaContainerId, embedLink);
        }

        executeReadyCallbacks();

        return;
    }

    // Receive Media
    if (data.command === 'MEDIA') {
        console.log('MEDIA', data);
        const isInsertAllowed = true;
        console.log('INSERT?', isInsertAllowed);

        var parentDocument = parent.document,
            contentIframe = parent.document.getElementById('typo3-contentIframe');

        if (contentIframe) {
            parentDocument = contentIframe.contentDocument;
        }

        // Dispatch internal interaction for TYPO3/CMS/AdmiralCloudConnector/Browser
        var event, parameters = {
            detail: {
                target: irreObjectTarget,
                media: data,
                modus: admiralCloudAction
            }
        };

        if (typeof CustomEvent === 'function') {
            event = new CustomEvent('AdmiralCloudBrowserAddMedia', parameters);
        } else {
            // Add IE11 support
            event = top.document.createEvent('CustomEvent');
            event.initCustomEvent('AdmiralCloudBrowserAddMedia', true, true, parameters);
        }

        top.document.dispatchEvent(event);
        hideAC();
    }
};
load(iframeURL_overview);

function hideAC() {
    elAdmiralCloud.querySelector('iframe').contentWindow.postMessage(JSON.stringify({command: 'HIDE_CROPPER_MODAL'}), iframeURL_HOST);
    elAdmiralCloud.querySelector('.acBackdrop').classList.add('hidden');
}