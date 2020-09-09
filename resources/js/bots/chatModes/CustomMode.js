const moment = require('moment-timezone');

let CustomMode = function() {
    this.name = "custom";
    this.typingIndicatorMessages = null;
    this.modeInstance = 0;
    this.dataLayerEventName = 'message_sent_to_live_agent';
};

CustomMode.prototype.sendRequest = async function(message, webChatComponent) {
    return await new Promise((resolve) => {
        this.addTypingMessageToMessageList(webChatComponent);

        // Faked custom request response
        setTimeout(() => {
            console.log("response");
            resolve([
                {
                    content: 'Hello there! 👋'
                },
                {
                    content: 'What\'s up?'
                }
            ]);
        }, 3000);
    });
};

CustomMode.prototype.sendResponseSuccess = function(response, sentMessage, webChatComponent) {
    this.setTeamName('Custom mode [Instance ' + this.modeInstance + ']', webChatComponent);

    if (this.typingIndicatorMessages !== null && response.length > 0) {
        let firstMessage = response[0];
        this.convertTypingIndicatorToFirstMessage(firstMessage, webChatComponent);
        firstMessage.skip = true;
    }

    if (response) {
        response.forEach((message) => {
            this.addMessageToMessageList(message, webChatComponent);
        });
    }

    return Promise.resolve(webChatComponent.messageList);
};

CustomMode.prototype.sendResponseError = function(error, sentMessage, webChatComponent) {
    return Promise.resolve();
};

CustomMode.prototype.sendTypingRequest = function(response, webChatComponent) {
    return Promise.resolve();
};

CustomMode.prototype.sendTypingResponseSuccess = function(response, webChatComponent) {
    return Promise.resolve();
};

CustomMode.prototype.sendTypingResponseError = function(error, webChatComponent) {
    return Promise.resolve();
};

CustomMode.prototype.initialiseChat = async function(webChatComponent) {
    webChatComponent.contentEditable = true;
    this.setTeamName('Waiting for agent...', webChatComponent);
    return Promise.resolve();
};

CustomMode.prototype.destroyChat = async function(webChatComponent) {
    let modeDataInSession = webChatComponent.getModeDataInSession();
    modeDataInSession.modeInstance++;
    webChatComponent.setChatMode(modeDataInSession);
    return Promise.resolve();
};

CustomMode.prototype.postDestroyChat = function(oldModeData, webChatComponent) {
    // Convert the original hand-to-Human message to a text message
    let filteredMessageList = webChatComponent.messageList.filter(
        message =>
            message.mode === "webchat" && message.type === "hand-to-system"
    );
    let handToSystemMessage = filteredMessageList[filteredMessageList.length - 1];

    if (handToSystemMessage) {
        handToSystemMessage.type = "text";
        handToSystemMessage.data.text = handToSystemMessage.data.elements.text;
    }

    webChatComponent.sendMessage({
        type: "trigger",
        author: "me",
        callback_id: oldModeData.options.callback_id,
        data: {}
    });

    return Promise.resolve();
};

CustomMode.prototype.addAuthorMessage = function(message, webChatComponent) {
    let authorMessage = webChatComponent.newAuthorMessage({
        author: "them",
        data: {
            time: moment().tz("UTC").format("hh:mm:ss A"),
            date: moment().tz("UTC").format("ddd D MMM"),
        }
    });
    webChatComponent.messageList.push(authorMessage)
    return authorMessage;
};

CustomMode.prototype.convertTypingIndicatorToFirstMessage = function(firstMessage, webChatComponent) {
    let typingIndicatorIndices = this.typingIndicatorMessages;
    this.typingIndicatorMessages = null;

    let typingIndicatorMessage = typingIndicatorIndices.typing;
    typingIndicatorMessage.type = "text";
    typingIndicatorMessage.data = {
        text: firstMessage.content,
        time: moment().tz("UTC").format("hh:mm:ss A"),
        date: moment().tz("UTC").format("ddd D MMM"),
    };
};

CustomMode.prototype.addMessageToMessageList = function(textMessage, webChatComponent) {
    if (textMessage.skip) {
        return;
    }

    const message = {
        author: "them",
        mode: "custom",
        modeInstance: this.modeInstance,
        type: "text",
        user_id: webChatComponent.$store.state.uuid,
        data: {
            text: textMessage.content,
            time: moment().tz("UTC").format("hh:mm:ss A"),
            date: moment().tz("UTC").format("ddd D MMM"),
        }
    };

    webChatComponent.messageList.push(message);
    let event = "message_received_from_agent";

    window.parent.postMessage(
        { dataLayerEvent: event },
        this.referrerUrl
    );
};

CustomMode.prototype.addTypingMessageToMessageList = function(webChatComponent) {
    if (this.typingIndicatorMessages !== null) {
        return;
    }

    let previousMessage = webChatComponent.messageList[webChatComponent.messageList.length-1];

    let authorMessage = null;
    if (previousMessage.mode !== "custom" || previousMessage.author !== "them") {
        authorMessage = this.addAuthorMessage({}, webChatComponent) - 1;
    }

    let newTypingIndicatorMessage = {
        author: "them",
        type: "typing",
        mode: "custom",
        modeInstance: this.modeInstance,
        data: {
            animate: true,
        }
    };
    webChatComponent.messageList.push(newTypingIndicatorMessage);

    this.typingIndicatorMessages = {
        author: authorMessage,
        typing: newTypingIndicatorMessage
    };
};

CustomMode.prototype.setTeamName = function(teamName, webChatComponent) {
    let updatedModeData = webChatComponent.modeData;
    updatedModeData.options.teamName = teamName;
    webChatComponent.setChatMode(updatedModeData);
};

CustomMode.prototype.clearTypingIndicator = function(webChatComponent) {
    let typingIndicatorIndices = this.typingIndicatorMessages;
    let typingIndicatorMessage = typingIndicatorIndices.typing;
    if (typingIndicatorMessage.type === "typing") {
        webChatComponent.messageList.splice(webChatComponent.messageList.indexOf(typingIndicatorIndices.typing), 1);

        if (typingIndicatorIndices.author !== null) {
            webChatComponent.messageList.splice(webChatComponent.messageList.indexOf(typingIndicatorIndices.author), 1);
        }

        this.typingIndicatorMessages = null;
    }
};

CustomMode.prototype.setModeInstance = function(number) {
    this.modeInstance = number;
};

CustomMode.prototype.getDataLayerEventName = function () {
    return this.dataLayerEventName;
};

export default CustomMode;
