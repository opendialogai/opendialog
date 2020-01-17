<template>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li v-for="(breadcrumb) in breadcrumbs"
                class="breadcrumb-item active" aria-current="page"><a href="#" @click="directTo(breadcrumb.routename, $event)">{{breadcrumb.name}}</a></li>
        </ol>
    </nav>
</template>

<script>
  export default {
    name: "Breadcrumbs",
    data () {
      return {
        breadcrumbs: []
      }
    },
    methods: {
      directTo(routeName, e) {
        this.$router.push({name: routeName, params: {id:this.$route.params.id}})
        e.preventDefault()
      },
      updateBreadCrumbs() {
        this.breadcrumbs = this.$route.meta.breadcrumbs;
      }
    },
    mounted() {
      this.updateBreadCrumbs()
    },
    watch: {
      '$route':()=> {
        this.updateBreadCrumbs()
      }
    }
  }
</script>

<style scoped>

</style>
