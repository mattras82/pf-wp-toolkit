import $ from 'jquery';

let frameOpts = {
    title: 'Select Image',
    button: {text: 'Select Image'},
    multiple: false,
    library: {type: 'image'}
};

let elements = {
    parent: null,
    preview: null,
    attachment_url: null,
    attachment_id: null,
    button: null
};

function render(props, attachment) {
    for (let key in props) {
        if (!props.hasOwnProperty(key) || props[key] === null)
            continue;

        if ((!props[key] instanceof jQuery) && (typeof props[key] === 'string'))
            props[key] = $(props[key]);
    }

    if (props.parent) props.parent.addClass('has-image');
    let url = attachment.url;
    if (attachment.sizes) {
        url = attachment.sizes.medium !== undefined ? attachment.sizes.medium.url : attachment.sizes.full.url;
    }
    if (['pdf', 'doc', 'ocx'].indexOf(attachment.url.substring(attachment.url.length - 3)) > -1) {
        url = '/wp-includes/images/media/document.png';
    }
    if (props.preview) props.preview.empty().html('<img src="' + url + '" />').show();
    if (props.attachment_url) props.attachment_url.val(attachment.url);
    if (props.attachment_id) props.attachment_id.val(attachment.id);
    if (props.button) props.button.text('Change Image');
}

function upload(settings, callback) {
    let frame = wp.media($.extend(true, {}, settings.opts));
    frame.on('select', function () {
        let attachment = frame.state().get('selection').toJSON()[0];
        render(settings.props, attachment);

        if (callback)
            callback.apply(attachment, [attachment]);
    });

    return frame.open();
}

function init() {
    return {
        opts: $.extend(true, {}, frameOpts),
        props: $.extend(true, {}, elements)
    };
}

export default {
    upload,
    init
};
