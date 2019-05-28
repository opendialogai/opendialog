require('ckeditor');
require('ckeditor/config');
require('ckeditor/adapters/jquery');

$(() => $('[data-editor="ckeditor"]').ckeditor());
