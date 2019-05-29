<template>
    <div class="panel panel-default" v-if="collection.length">
        <div class="panel-heading">
            <h3 class="panel-title">Folders</h3>
        </div>
        <div class="panel-body">
            <ul class="folder-list">
                <li v-for="(dir, i) in collection" :key="i" v-if="dir">
                    <div class="pull-right" v-if="dir !== '../'">
                        <a @click="$emit('remove', dir)"><i class="fa fa-trash"></i></a>
                    </div>
                    <div class="pull-left">
                        <a style="margin-left: 10px; cursor: pointer;" v-if="dir" @click="$emit('cd', dir)">
                            <i class="fa fa fa-folder-open"></i>
                            {{ dir.filename || dir }}
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            collection: {
                type: Array,
                default: () => []
            }
        }
    };
</script>