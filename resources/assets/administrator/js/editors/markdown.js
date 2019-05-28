const SimpleMDE = require('simplemde');

document.querySelectorAll('[data-editor="markdown"]').forEach((e) => {
    new SimpleMDE({element: e});
});