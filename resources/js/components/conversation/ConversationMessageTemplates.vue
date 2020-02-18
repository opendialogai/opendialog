<template>
    <div>
        <h2 class="mb-3">Message Templates</h2>

        <div class="overflow-auto">
            <table class="table table-hover">
                <thead class="thead-light">
                <tr>
                    <th scope="col" class="id-col">#</th>
                    <th scope="col" class="name-col">Name</th>
                    <th scope="col" class="actions-col">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(messageTemplate, idx) in messageTemplates">
                    <td>
                        {{ messageTemplate.id }}
                    </td>
                    <td>
                        {{ messageTemplate.name }}
                        <router-link class="small d-block" :to="{ name: 'view-outgoing-intent', params: { id: messageTemplate.outgoing_intent_id }}">
                            {{ messageTemplate.outgoing_intent.name }}
                        </router-link>
                    </td>
                    <td class="actions">
                        <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Edit" @click.stop="editMessageTemplate(messageTemplate)">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-primary" data-toggle="collapse" :data-target="'#collapse-' + idx" aria-expanded="false" :aria-controls="'collapse-' + idx">
                            <span class="expand-label">expand</span>
                            <span class="collapse-label">collapse</span>
                        </button>
                        <div class="collapse" :id="'collapse-' + idx">
                            <div class="card card-body mt-3 mb-1">
                                <MessageBuilder :message="messageTemplate" />
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <nav aria-label="navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="(currentPage == 1) ? 'disabled' : ''">
                    <router-link class="page-link" :to="{ name: 'conversation-message-templates', query: { page: currentPage - 1 } }">Previous</router-link>
                </li>

                <li class="page-item" v-for="pageNumber in totalPages">
                    <router-link class="page-link" :to="{ name: 'conversation-message-templates', query: { page: pageNumber } }">{{ pageNumber }}</router-link>
                </li>

                <li class="page-item" :class="(currentPage == totalPages) ? 'disabled' : ''">
                    <router-link class="page-link" :to="{ name: 'conversation-message-templates', query: { page: currentPage + 1 } }">Next</router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import MessageBuilder from '../message-template/MessageBuilder';

    export default {
        name: 'conversation-message-templates',
        components: {
            MessageBuilder,
        },
        props: ['id'],
        data() {
            return {
                messageTemplates: [],
                currentPage: 1,
                totalPages: 1,
            };
        },
        watch: {
            '$route' () {
                this.fetchMessageTemplates();
            }
        },
        mounted() {
            this.fetchMessageTemplates();
        },
        methods: {
            fetchMessageTemplates() {
                this.currentPage = this.$route.query.page || 1;

                axios.get('/admin/api/conversation/' + this.id + '/message-templates?page=' + this.currentPage).then(
                    (response) => {
                        this.totalPages = response.data.meta.last_page;
                        this.messageTemplates = response.data.data;
                    },
                );
            },
            editMessageTemplate(messageTemplate) {
                this.$router.push({
                    name: 'edit-message-template',
                    params: {
                        outgoingIntent: messageTemplate.outgoing_intent_id,
                        id: messageTemplate.id
                    },
                    query: {
                        conversationId: this.id
                    }
                });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .table {
        .id-col {
            width: 70px;
        }
        .name-col,
        .actions-col {
            width: 50%;
        }

        td.actions {
            min-width: 160px;

            button[aria-expanded="true"] {
                .expand-label {
                    display: none;
                }
            }
            button[aria-expanded="false"] {
                .collapse-label {
                    display: none;
                }
            }
        }
    }
</style>
