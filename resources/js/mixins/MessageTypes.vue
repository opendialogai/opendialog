<script>
export default {
  name: 'pager',
  data() {
    return {
      currentPage: 1,
      totalPages: 1,
    };
  },
  methods: {
    getMessageTypes() {
      return [
        {type: 'text-message', function: (message, msg) => {this.parseTextMessage(message, msg)}},
        {type: 'button-message', function: (message, msg) => {this.parseButtonMessage(message, msg)}},
        {type: 'image-message', function: (message, msg) => {this.parseImageMessage(message, msg)}},
        {type: 'cta-message', function: (message, msg) => {this.parseCtaMessage(message, msg)}},
        {type: 'hand-to-human-message', function: (message, msg) => {this.parseH2hMessage(message, msg)}},
        {type: 'fp-rich-message', function: (message, msg) => {this.parseRichMessage(message, msg)}},
        {type: 'rich-message', function: (message, msg) => {this.parseRichMessage(message, msg)}},
        {type: 'fp-form-message', function: (message, msg) => {this.parseFormMessage(message, msg)}},
        {type: 'form-message', function: (message, msg) => {this.parseFormMessage(message, msg)}},
        {type: 'long-text-message', function: (message, msg) => {this.parseLongTextMessage(message, msg)}},
        {type: 'list-message', function: (message, msg) => {this.parseListMessage(message, msg)}},
        {type: 'meta-message', function: (message, msg) => {this.parseMetaMessage(message, msg)}},
      ];
    },
    parseTextMessage (message, msg) {
      let text = '';
      msg.children.forEach((child) => {
        if (child.type === 'element') {
          text += ' <a target="_blank" href="' + child.childNamed('url').val.trim() + '">' + child.childNamed('text').val.trim() + '</a>';
        } else if (child.type === 'text') {
          text += ' ' + child.text.trim();
        }
      });
      message.data = text.trim();
    },
    parseButtonMessage(message, msg) {
      let buttons = [];
      msg.childrenNamed('button').forEach((button) => {
        buttons.push({
          text: (button.childNamed('text')) ? button.childNamed('text').val.trim() : '',
        });
      });

      message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : '';
      message.data.buttons = buttons;
    },
    parseImageMessage(message, msg) {
      message.data.src = (msg.childNamed('src')) ? msg.childNamed('src').val.trim() : '';
      message.data.link = (msg.childNamed('link')) ? msg.childNamed('link').val.trim() : '';
    },
    parseCtaMessage(message, msg) {
      message.data.text = msg.val;
    },
    parseH2hMessage: function (msg, message) {
      let data = [];
      msg.childrenNamed('data').forEach((d) => {
        data.push({
          name: d.attr.name,
          val: d.val,
        });
      });
      message.data.data = data;
    },
    parseRichMessage: function (message, msg) {
      message.data.title = (msg.childNamed('title')) ? msg.childNamed('title').val.trim() : '';
      message.data.subtitle = (msg.childNamed('subtitle')) ? msg.childNamed('subtitle').val.trim() : '';
      message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : '';
      message.data.button = {
        text: (msg.childNamed('button')) ? msg.childNamed('button').childNamed('text').val.trim() : '',
      };
      if (msg.childNamed('image')) {
        message.data.image = {
          src: (msg.childNamed('image').childNamed('src')) ? msg.childNamed('image').childNamed('src').val.trim() : '',
          url: (msg.childNamed('image').childNamed('url')) ? msg.childNamed('image').childNamed('url').val.trim() : '',
        };
      }
    },
    parseFormMessage: function (message, msg) {
      let elements = [];
      msg.childrenNamed('element').forEach((element) => {
        const elementType = element.childNamed('element_type').val.trim();
        let options = [];

        if (elementType == 'radio' || elementType == 'auto_complete_select') {
          element.childNamed('options').childrenNamed('option').forEach((option) => {
            options.push({
              key: (option.childNamed('key')) ? option.childNamed('key').val.trim() : '',
              value: (option.childNamed('value')) ? option.childNamed('value').val.trim() : '',
            });
          });
        }

        elements.push({
          element_type: elementType,
          display: (element.childNamed('display')) ? element.childNamed('display').val.trim() : '',
          default_value: (element.childNamed('default_value')) ? element.childNamed('default_value').val.trim() : '',
          options,
        });
      });

      message.data.text = (msg.childNamed('text')) ? msg.childNamed('text').val.trim() : '';
      message.data.submit_text = (msg.childNamed('submit_text')) ? msg.childNamed('submit_text').val.trim() : '';
      message.data.elements = elements;
    },
    parseLongTextMessage: function (message, msg) {
      message.data.submit_text = (msg.childNamed('submit_text')) ? msg.childNamed('submit_text').val.trim() : '';
      message.data.initial_text = (msg.childNamed('initial_text')) ? msg.childNamed('initial_text').val.trim() : '';
      message.data.placeholder = (msg.childNamed('placeholder')) ? msg.childNamed('placeholder').val.trim() : '';
      message.data.confirmation_text = (msg.childNamed('confirmation_text')) ? msg.childNamed('confirmation_text').val.trim() : '';
      message.data.character_limit = (msg.childNamed('character_limit')) ? msg.childNamed('character_limit').val.trim() : '';
    },
    parseListMessage: function (message, msg) {
      let items = [];
      msg.childrenNamed('item').forEach((item) => {
        item.children.forEach((children) => {
          if (children.type === 'element') {
            const i = this.parseMessage(children);
            items.push(i);
          }
        });
      });

      message.data.view_type = msg.attr['view-type'];
      message.data.items = items;
    },
    parseMetaMessage: function (message, msg) {
      let datas = [];
      msg.childrenNamed('data').forEach((data) => {
        datas.push({
          name: data.attr.name,
          value: data.val.trim(),
        });
      });

      message.data.datas = datas;
    },
  },
};
</script>
