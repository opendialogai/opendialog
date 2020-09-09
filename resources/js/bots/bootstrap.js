import {registerCustomChatModes} from "./registerChatModes";

window.openDialogWebchat = {
    chatService: {
        getCustomModes() {
            return registerCustomChatModes();
        }
    }
};
