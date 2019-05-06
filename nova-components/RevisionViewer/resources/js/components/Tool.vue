<template>
    <div>
        <table class="table w-full">
            <thead>
                <th class="text-left">Date</th>
                <th class="text-left">User</th>
                <th class="text-left">Updates</th>
                <th class="text-left">Actions</th>
            </thead>
            <tbody>
                <template v-for="(row, i) in rows">
                    <tr>
                        <td>{{ row.date }}</td>
                        <td>{{ row.user }}</td>
                        <td>{{ row.updates }}</td>
                        <td>
                            <a v-if="row.class" class="mr-6" href="#" @click.prevent="expand(i)">View changes</a>
                            <a v-else class="mr-6" href="#" @click.prevent="hide(i)">Hide changes</a>

                            <a href="#" @click.prevent="revert(i)">Revert</a>
                        </td>
                    </tr>
                    <tr :class="row.class">
                        <td colspan="4" v-html="row.format"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: ['resourceName', 'resourceId', 'field'],
    data() {
        return {
            rows: [],
        };
    },
    mounted() {
        window.axios
            .get(`/nova/conversation-log/${this.resourceId}`)
            .then(response => {
                response.data.forEach(row => {
                    row.class = "collapsed";
                    this.rows.push(row);
                });
            });
    },
    methods: {
        expand(i) {
            this.rows[i].class = "";
        },
        hide(i) {
            this.rows[i].class = "collapsed";
        },
        revert(i) {

        },
    },
}
</script>

<style lang="scss">
.collapsed {
  display: none;
}

.label {
  margin: 15px 0 5px 0;
  font-weight: 500;
}

.DifferencesSideBySide {
  width: 100%;
  margin: 7px 0 30px 0;

  thead {
    display: none;
  }

  tr {
    td {
      width: 50%;
      height: auto;
      border: none;
    }
    th {
      border: none;
      background: none;
      padding-top: 0;
      padding-bottom: 0;
    }
  }

  .Left {
    del {
      text-decoration: none;
      background: #f98b8b;
    }
  }

  .Right {
    ins {
      text-decoration: none;
      background: #44ee44;
    }
  }
}
</style>
