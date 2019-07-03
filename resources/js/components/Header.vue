<template>
  <div class="header">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark row">
      <div class="col-7">
        <div class="navbar-brand">
          <img src="/images/logo.svg" />
          <span>OpenDialog</span>
        </div>

        <router-link :to="{ name: 'home' }">
          <img class="home" src="/images/home.png" />
        </router-link>
      </div>

      <div class="col-5 row">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item mr-4">
            <router-link :to="{ name: 'home' }" class="nav-link">
              <span>Message Editor</span>
            </router-link>
          </li>

          <li class="nav-item mr-4">
            <router-link :to="{ name: 'home' }" class="nav-link">
              <span>Insights</span>
            </router-link>
          </li>

          <li class="nav-item mr-4">
            <router-link :to="{ name: 'home' }" class="nav-link">
              <span>Settings</span>
            </router-link>
          </li>
        </ul>

        <div class="help">
          <img src="/images/help.svg" />
        </div>

        <div class="dropdown ml-4">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Account
          </button>

          <div class="dropdown-menu" aria-labelledby="dropdownMenu">
            <router-link :to="{ name: 'home' }" class="dropdown-item">
              <img src="" />
              <span>Profile</span>
            </router-link>
            <router-link :to="{ name: 'home' }" class="dropdown-item">
              <img src="" />
              <span>Team</span>
            </router-link>
            <router-link :to="{ name: 'home' }" class="dropdown-item">
              <img src="" />
              <span>Logout</span>
            </router-link>
          </div>
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'header',
};
</script>

<style lang="scss" scoped>
.header {
  .home {
    height: 30px;
  }

  .navbar-brand {
    img {
      height: 40px;
    }
  }

  .help {
    display: flex;
    img {
      margin: auto;
      height: 30px;
    }
  }
}
</style>
