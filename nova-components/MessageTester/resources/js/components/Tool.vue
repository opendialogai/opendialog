<template>
    <div>
        <heading class="mb-6">Message Tester</heading>

        <card class="card mb-6">
            <div class="py-3 px-6">
                <div class="py-3 border-b border-40">
                    <h3 class="font-normal">User info</h3>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">First Name</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.first_name">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Last Name</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.last_name">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Email</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.email">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">External ID</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.external_id">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Browser</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.browser">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Browser Language</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.browserLanguage">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">OS</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.os">
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Timezone</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.timezone">
                    </div>
                </div>

                <div class="flex">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">IP Address</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="this.userInfo.ipAddress">
                    </div>
                </div>
            </div>
        </card>

        <card class="card mb-6">
            <div class="py-3 px-6">
                <div class="py-3 border-b border-40">
                    <h3 class="font-normal">Message info</h3>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Message Type</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <div class="mb-2">
                            <input type="radio" id="text" value="text" v-model="messageType">
                            <label class="ml-1" for="text">Text</label>
                        </div>
                        <div class="mb-2">
                            <input type="radio" id="chat-open" value="chat-open" v-model="messageType">
                            <label class="ml-1" for="chat-open">Chat open</label>
                        </div>
                        <div class="mb-2">
                            <input type="radio" id="trigger" value="trigger" v-model="messageType">
                            <label class="ml-1" for="trigger">Trigger</label>
                        </div>
                        <div>
                            <input type="radio" id="button" value="button" v-model="messageType">
                            <label class="ml-1" for="button">Button</label>
                        </div>
                    </div>
                </div>

                <div class="flex" v-if="messageType == 'text'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Message Text</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <textarea class="w-full form-control form-input form-input-bordered py-3 h-auto" v-model="messageText" />
                    </div>
                </div>

                <div class="flex" v-if="messageType == 'chat-open'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Callback ID</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="chatOpenCallbackId" />
                    </div>
                </div>

                <div class="flex border-b border-40" v-if="messageType == 'trigger'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Callback ID</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="triggerCallbackId" />
                    </div>
                </div>

                <div class="flex border-b border-40" v-if="messageType == 'trigger'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Value</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="triggerValue" />
                    </div>
                </div>

                <div class="flex border-b border-40" v-if="messageType == 'button'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Button Text</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="buttonText" />
                    </div>
                </div>

                <div class="flex border-b border-40" v-if="messageType == 'button'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Button Callback ID</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="buttonCallbackId" />
                    </div>
                </div>

                <div class="flex" v-if="messageType == 'button'">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">Button Value</h4>
                    </div>
                    <div class="w-3/4 py-4">
                        <input type="text" class="w-full form-control form-input form-input-bordered" v-model="buttonValue" />
                    </div>
                </div>
            </div>

            <div class="bg-30 flex py-4 px-6">
                <button class="btn btn-default btn-primary ml-auto" @click="sendMessage">
                    Send Message
                </button>
            </div>
        </card>

        <card v-if="responseMessages.length" class="card mb-6">
            <div class="py-6 px-6">
                <div class="message py-3 px-3 mt-3 mb-3" v-for="message in responseMessages">
                    <div v-if="message.type == 'button'">
                        <div>{{ message.text }}</div>
                        <div class="buttons flex mt-2">
                            <div v-for="button in message.buttons">
                                <button class="btn btn-default btn-primary ml-auto mr-3">
                                    {{ button.text }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="message.type == 'image'">
                        <div>{{ message.text }}</div>
                        <div v-if="message.href">
                            <a :href="message.href">
                                <img :src="message.src" />
                            </a>
                        </div>
                        <div v-else>
                            <img :src="message.src" />
                        </div>
                    </div>

                    <div v-if="message.type == 'text'">{{ message.text }}</div>
                </div>
            </div>
        </card>
    </div>
</template>

<script>
import axios from 'axios';

import { uuid } from 'vue-uuid';

const { detect } = require('detect-browser');
const jstz = require('jstz');
const moment = require('moment-timezone');

export default {
    data() {
        return {
            buttonCallbackId: '',
            buttonText: '',
            buttonValue: '',
            chatOpenCallbackId: '',
            messageType: 'text',
            messageText: '',
            responseMessages: [],
            userInfo: {},
            triggerCallbackId: '',
            triggerValue: '',
        };
    },

    mounted() {
        this.userTimezone = jstz.determine().name();
        const browserInfo = detect();
        const ipAddress = 'n/a';
        const { country } = 'n/a';
        const browserLanguage = navigator.language || navigator.userLanguage;
        const { os } = browserInfo;
        const browser = `${browserInfo.name} ${browserInfo.version}`;
        const timezone = jstz.determine().name();

        this.userInfo = {
            first_name: 'First name',
            last_name: 'Last name',
            email: 'test@test.com',
            external_id: '1',
            ipAddress,
            country,
            browserLanguage,
            os,
            browser,
            timezone,
        };

        axios.get('https://ipinfo.io/').then(
            (response) => {
                this.userInfo.ipAddress = response.data.ip;
                this.userInfo.country = response.data.country;
            },
        );
    },

    methods: {
        sendMessage() {
            this.responseMessages = [];

            const userId = uuid.v4();

            let message = {
                id: uuid.v4(),
                user_id: userId,
                author: userId,
                user: this.userInfo,
                data: {
                    date: moment().tz('UTC').format('ddd D MMM'),
                    time: moment().tz('UTC').format('hh:mm A'),
                },
            };

            if (this.messageType == 'text') {
                message.type = 'text';
                message.data.text = this.messageText;
            } else if (this.messageType == 'chat-open') {
                message.type = 'chat_open';
                message.data.callback_id = this.chatOpenCallbackId;
            } else if (this.messageType == 'trigger') {
                message.type = 'trigger';
                message.data.callback_id = this.triggerCallbackId;
                message.data.value = this.triggerValue;
            } else if (this.messageType == 'button') {
                message.type = 'button_response';
                message.data.callback_id = this.buttonCallbackId;
                message.data.text = this.buttonText;
                message.data.value = this.buttonValue;
            }

            const webchatMessage = {
                notification: 'message',
                user_id: message.user_id,
                author: message.author,
                message_id: message.id,
                content: message,
            };

            axios.post('/incoming/webchat', webchatMessage).then(
                (response) => {
                    if (response.data instanceof Array) {
                        response.data.forEach((message, i) => {
                            if (message.type == 'text') {
                                this.responseMessages.push({
                                    type: 'text',
                                    text: message.data.text,
                                });
                            } else if (message.type == 'image') {
                                this.responseMessages.push({
                                    type: 'image',
                                    src: message.data.img_src,
                                    href: message.data.img_link,
                                    text: message.data.text,
                                });
                            } else if (message.type == 'button') {
                                this.responseMessages.push({
                                    type: 'button',
                                    buttons: message.data.buttons,
                                    text: message.data.text,
                                });
                            }
                        });
                    } else {
                        this.responseMessages.push({
                            text: response.data.data.text,
                        });
                    }
                },
                // Axios error handler.
                () => {
                    this.responseMessages.push({
                        text: "We're sorry, that didn't work, please try again",
                    });
                },
            );
        },
    },
}
</script>

<style>
.message {
    border-radius: 14px;
    border-bottom-left-radius: 0;
    box-shadow: #CFCFCF -1px 1px 1px;
    background-color: #F2F2F2 !important;
    max-width: 600px;
}
</style>
