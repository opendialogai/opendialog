import {registerCustomChatModes} from "./registerChatModes";
import {registerCustomBootstrapFunctions} from "./registerCustomBootstrapFunctions";

window.openDialogWebchat = {
    chatService: {
        getCustomModes() {
            return registerCustomChatModes();
        }
    },
    bootstrap: {
        customBootstrapFunctions: (defaultBootstrapFunctions) => {
            return registerCustomBootstrapFunctions(defaultBootstrapFunctions);
        }
    }
};
