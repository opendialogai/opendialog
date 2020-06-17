<script>
import CodeMirror from 'codemirror';
import 'codemirror/addon/hint/show-hint.css';
import 'codemirror/addon/hint/show-hint';
import 'codemirror/addon/hint/xml-hint';

export default {
  name: 'xml-codemirror',
  data() {
    return {
      cmMarkupOptions: {
        tabSize: 2,
        mode: 'application/xml',
        theme: 'dracula',
        lineNumbers: true,
        line: true,
        autoCloseTags: true,
        matchTags: true,
        extraKeys: {
          "'<'": this.completeAfter,
          "' '": this.completeIfInTag,
          "'='": this.completeIfInTag,
          "Ctrl-Space": "autocomplete",
        },
        hintOptions: {
          schemaInfo: {
            '!top': ['message'],
            '!attrs': {
              disable_text: ['true', 'false'],
              hide_avatar: ['true', 'false'],
            },
            'attribute-message': {},
            'button-message': {
              children: ['button', 'external', 'text'],
            },
            'cta-message': {},
            'empty-message': {},
            'form-message': {
              children: ['auto_submit', 'callback', 'cancel_callback', 'cancel_text', 'submit_text', 'text'],
            },
            'fp-form-message': {
              children: ['auto_submit', 'callback', 'cancel_callback', 'cancel_text', 'submit_text', 'text'],
            },
            'fp-rich-message': {
              children: ['button', 'image', 'subtitle', 'text', 'title'],
            },
            'hand-to-human-message': {
              children: ['data'],
            },
            'image-message': {
              children: ['link', 'src'],
            },
            'list-message': {
              attrs: {
                'view-type': ['horizontal', 'vertical'],
              },
              children: ['item'],
            },
            'long-text-message': {
              children: ['callback', 'character_limit', 'confirmation_text', 'initial_text', 'placeholder', 'submit_text'],
            },
            'rich-message': {
              children: ['button', 'image', 'subtitle', 'text', 'title'],
            },
            'text-message': {
              children: ['link'],
            },
          },
        },
      },
    };
  },
  methods: {
    completeAfter(cm, pred) {
      var cur = cm.getCursor();
      if (!pred || pred()) setTimeout(() => {
        if (!cm.state.completionActive) {
          cm.showHint({ completeSingle: false });
        }
      }, 100);
      return CodeMirror.Pass;
    },
    completeIfInTag(cm) {
      return this.completeAfter(cm, () => {
        var tok = cm.getTokenAt(cm.getCursor());
        if (tok.type == "string" && (!/['"]/.test(tok.string.charAt(tok.string.length - 1)) || tok.string.length == 1)) return false;
        var inner = CodeMirror.innerMode(cm.getMode(), tok.state).state;
        return inner.tagName;
      });
    },
  },
};
</script>
