let tinymce = require('tinymce');
require('tinymce/themes/modern/theme');
require('tinymce/plugins/advlist');
require('tinymce/plugins/autolink');
require('tinymce/plugins/lists');
require('tinymce/plugins/paste');
require('tinymce/plugins/link');
require('./tinymce/plugins/image');
require('tinymce/plugins/charmap');
require('tinymce/plugins/print');
require('tinymce/plugins/preview');
require('tinymce/plugins/searchreplace');
require('tinymce/plugins/code');
require('tinymce/plugins/fullscreen');
require('tinymce/plugins/media');
require('tinymce/plugins/table');

tinymce.init({
    selector: 'textarea[data-editor="tinymce"]',
    plugins: [
        "advlist autolink lists link image charmap print preview",
        "searchreplace code fullscreen",
        "media table paste"
    ],
    image_advtab: true,
    images_upload_url: '/cms/media/upload',
    relative_urls: false,
    height: 300,
    statusbar: false,

    //menubar: false,
    //toolbar: 'undo redo' +
    //' | bold italic underline strikethrough' +
    //' | alignleft  aligncenter alignright alignjustify' +
    //' | bullist numlist blockquote outdent indent' +
    //' | link image imagetools' +
    //' | removeformat code brbtn',
    //plugins: [
    //    'lists autolink link image preview print',
    //    'fullscreen code paste',
    //],
    setup: (editor) => {
        let e = editor.getElement();
        editor.settings.readonly = e.readOnly || e.disabled;

       // editor.addButton('brbtn', {
       //     icon: 'line',
       //     tooltip: "Insert New Line",
       //     onclick: function() {
       //         editor.insertContent('<br />');
       //     },
       // });
    },
});