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
        {
          type: 'text-message', renderer: (message, msg) => {this.parseTextMessage(message, msg)},
          xml: "<message>\n" +
            "  <text-message> \n" +
            "    Hello, this is a text message.\n" +
            "  </text-message>\n" +
            "</message>"
        },
        {
          type: 'attribute-message', renderer: (message, msg) => {this.parseAttributeMessage(message, msg)},
          xml: "<message>\n" +
            "  <attribute-message> \n" +
            "    context_name.attribute_name\n" +
            "  </attribute-message>\n" +
            "</message>"
        },
        {
          type: 'button-message', renderer: (message, msg) => {this.parseButtonMessage(message, msg)},
          xml: "<message>\n" +
            "  <button-message>\n" +
            "    <button>\n" +
            "      <text>Button text</text>\n" +
            "      <value>button_value</value>\n" +
            "      <callback>button_callback</callback>\n" +
            "      <display>true</display>\n" +
            "    </button>\n" +
            "  </button-message>\n"+
            "</message>"
        },
        {
          type: 'image-message', renderer: (message, msg) => {this.parseImageMessage(message, msg)},
          xml: "<message>\n" +
            "  <image-message>\n" +
            "    <src>https://docs.opendialog.ai/img/od-logo-with-credit.jpg</src>\n" +
            "    <url new_tab=\"true\">{url}</url>\n" +
            "  </image-message>\n" +
            "</message>"
        },
        {
          type: 'cta-message', renderer: (message, msg) => {this.parseCtaMessage(message, msg)},
          xml: "<message>\n" +
            "  <cta-message> \n" +
            "    Hello, this is a cta message.\n" +
            "  </cta-message>\n" +
            "</message>"
        },
        {
          type: 'hand-to-system-message', renderer: (message, msg) => {this.parseH2sMessage(message, msg)},
          xml: "<message>\n" +
            "  <hand-to-system-message system=\"my-custom-system\">\n" +
            "    <data name=\"replace_name_attribute\">Value</data>\n" +
            "  </hand-to-system-message>\n"+
            "</message>"
        },
        {
          type: 'fp-rich-message', renderer: (message, msg) => {this.parseRichMessage(message, msg)},
          xml: "<message>\n" +
            "  <fp-rich-message>\n" +
            "    <title>Rich Message</title>\n" +
            "    <subtitle>With a subtitle</subtitle>\n" +
            "    <text>Some engaging text</text>\n" +
            "    <image>\n" +
            "      <src>https://docs.opendialog.ai/img/od-logo-with-credit.jpg</src>\n" +
            "      <url new_tab=\"true\">https://docs.opendialog.ai</url>\n" +
            "    </image>\n" +
            "  </fp-rich-message>\n" +
            "</message>"
        },
        {
          type: 'rich-message', renderer: (message, msg) => {this.parseRichMessage(message, msg)},
          xml: "<message>\n" +
            "  <rich-message>\n" +
            "    <title>Rich Message</title>\n" +
            "    <subtitle>With a subtitle</subtitle>\n" +
            "    <text>Some engaging text</text>\n" +
            "    <image>\n" +
            "      <src>https://docs.opendialog.ai/img/od-logo-with-credit.jpg</src>\n" +
            "      <url new_tab=\"true\">https://docs.opendialog.ai</url>\n" +
            "    </image>\n" +
            "  </rich-message>\n" +
            "</message>"
        },
        {
          type: 'fp-form-message', renderer: (message, msg) => {this.parseFormMessage(message, msg)},
          xml: "<message>\n" +
            "  <fp-form-message>\n" +
            "    <text>Text</text>\n" +
            "    <submit_text>Submit Text</submit_text>\n" +
            "    <callback>Callback</callback>\n" +
            "    <auto_submit>false</auto_submit>\n" +
            "\n" +
            "    <element>\n" +
            "      <element_type>select</element_type>\n" +
            "      <name>title</name>\n" +
            "      <display>Title</display>\n" +
            "      <options>\n" +
            "        <option>\n" +
            "          <key>mr</key>\n" +
            "          <value>Mr.</value>\n" +
            "        </option>\n" +
            "        <option>\n" +
            "          <key>mrs</key>\n" +
            "          <value>Mrs.</value>\n" +
            "        </option>\n" +
            "        <option>\n" +
            "          <key>other</key>\n" +
            "          <value>Other</value>\n" +
            "        </option>\n" +
            "      </options>\n" +
            "    </element>\n" +
            "\n" +
            "    <element>\n" +
            "      <element_type>text</element_type>\n" +
            "      <name>name</name>\n" +
            "      <display>Name</display>\n" +
            "    </element>\n" +
            "\n" +
            "  </fp-form-message>\n" +
            "</message>"
        },
        {
          type: 'form-message', renderer: (message, msg) => {this.parseFormMessage(message, msg)},
          xml: "<message>\n" +
            "  <form-message>\n" +
            "    <text>Text</text>\n" +
            "    <submit_text>Submit Text</submit_text>\n" +
            "    <callback>Callback</callback>\n" +
            "    <auto_submit>false</auto_submit>\n" +
            "\n" +
            "    <element>\n" +
            "      <element_type>select</element_type>\n" +
            "      <name>title</name>\n" +
            "      <display>Title</display>\n" +
            "      <options>\n" +
            "        <option>\n" +
            "          <key>mr</key>\n" +
            "          <value>Mr.</value>\n" +
            "        </option>\n" +
            "        <option>\n" +
            "          <key>mrs</key>\n" +
            "          <value>Mrs.</value>\n" +
            "        </option>\n" +
            "        <option>\n" +
            "          <key>other</key>\n" +
            "          <value>Other</value>\n" +
            "        </option>\n" +
            "      </options>\n" +
            "    </element>\n" +
            "\n" +
            "    <element>\n" +
            "      <element_type>text</element_type>\n" +
            "      <name>name</name>\n" +
            "      <display>Name</display>\n" +
            "    </element>\n" +
            "\n" +
            "  </form-message>\n" +
            "</message>"
        },
        {
          type: 'long-text-message', renderer: (message, msg) => {this.parseLongTextMessage(message, msg)},
          xml: "<message>\n" +
            "  <long-text-message>\n" +
            "    <submit_text>Submit Text</submit_text>\n" +
            "    <callback>callback</callback>\n" +
            "    <initial_text>initialText</initial_text>\n" +
            "    <placeholder>placeholder</placeholder>\n" +
            "    <confirmation_text>confirmationText</confirmation_text>\n" +
            "    <character_limit>characterLimit</character_limit>\n" +
            "  </long-text-message>\n" +
            "</message>"
        },
        {
          type: 'list-message', renderer: (message, msg) => {this.parseListMessage(message, msg)},
          xml: "<message>\n" +
            "  <list-message list_type=\"horizontal\">\n" +
            "    <text-message>Hello text message</text-message>\n" +
            "    <image-message>Image message</image-message>\n" +
            "    <rich-message>Rich message</rich-message>\n" +
            "  </list-message>\n" +
            "</message>"
        },
        {
          type: 'meta-message', renderer: (message, msg) => {this.parseMetaMessage(message, msg)},
          xml: "<message>\n" +
            "  <meta-message>\n" +
            "    <data name=\"key\">value</data>\n" +
            "  </meta-message>\n"+
            "</message>"
        },
        {
          type: 'autocomplete-message', renderer: (message, msg) => {this.parseAutoCompleteMessage(message, msg)},
          xml: "<message>\n" +
            "  <autocomplete-message>\n" +
            "    <title>Title</title>\n" +
            "   <callback>callback.id</callback>\n" +
            "   <submit_text>Submit</submit_text>\n" +
            "    <options-endpoint>\n" +
            "      <params>\n" +
            "        <param name=\"name\" value=\"value\" />\n" +
            "      </params>\n" +
            "      <query-param-name>name</query-param-name>\n" +
            "    </options-endpoint>\n" +
            "  </autocomplete-message>\n"+
            "</message>"
        },
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
    parseAttributeMessage (message, msg) {
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
    parseH2sMessage: function (message, msg) {
      let data = [];
      msg.childrenNamed('data').forEach((d) => {
        data.push({
          name: d.attr.name,
          val: d.val,
        });
      });
      message.data.system = msg.attr['system'];
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

        if (elementType == 'radio' || elementType == 'select' || elementType == 'auto_complete_select') {
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
      message.data.callback = (msg.childNamed('callback')) ? msg.childNamed('callback').val.trim() : '';
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
    parseAutoCompleteMessage: function (message, msg) {
      message.data.submit_text = (msg.childNamed('submit_text')) ? msg.childNamed('submit_text').val.trim() : '';
      message.data.callback = (msg.childNamed('callback')) ? msg.childNamed('callback').val.trim() : '';
      message.data.title = (msg.childNamed('title')) ? msg.childNamed('title').val.trim() : '';
    },
    parseMessage(msg) {
      const message = {
        type: msg.name,
        data: {},
      };

      const messageTypes = this.getMessageTypes();
      const messageTypeConfig = messageTypes.find(messageConfig => messageConfig.type === message.type)
      // update the message properties based on its type
      messageTypeConfig.renderer(message, msg);
      return message;
    },
  },
};
</script>
