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
        const home = {
          name: "Home",
          routename: "home"
        };

        // if no crumbs supplied still create the home crumb
        if(this.$route.meta.breadcrumbs == undefined)
        {
          this.$route.meta.breadcrumbs = [home];
        }
        // if crumbs are supplied prepend the home crumb
        else if (
          this.$route.meta.breadcrumbs[0] == undefined ||
          this.$route.meta.breadcrumbs[0].routename != 'home'
        ) {
          this.$route.meta.breadcrumbs.unshift(home);
        }
        this.breadcrumbs = this.$route.meta.breadcrumbs;
      }
    },
    mounted() {
      this.updateBreadCrumbs()
    },
    watch: {
      '$route'(id) {
        this.updateBreadCrumbs()
      }
    }
  }
</script>

<style scoped>

</style>
