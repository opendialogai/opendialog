<template>
  <div class="mt-4">
    <h3>Dashboard</h3>
    <p>Welcome back, Caroline.</p>

    <div class="row mb-4">
      <div class="card col ml-2 mr-2 p-0">
        <div class="card-body">
          <div class="card-title">Users</div>
        </div>
      </div>

      <div class="card col ml-2 mr-2 p-0">
        <div class="card-body">
          <div class="card-title">Messages</div>
        </div>
      </div>
    </div>

    <div class="row text-center">
      <div class="card col ml-2 mr-2 p-0">
        <div class="card-body">
          <div class="card-title">Message editor</div>
        </div>
      </div>

      <div class="card col ml-2 mr-2 p-0">
        <div class="card-body">
          <div class="card-title">Webchat setting</div>
        </div>
      </div>

      <div class="card col ml-2 mr-2 p-0">
        <div class="card-body">
          <div class="card-title">Team</div>
        </div>
      </div>

      <div class="card col ml-2 mr-2 p-0">
        <div class="card-body">
          <div class="card-title">Documentation</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'home',
};
</script>

<style lang="scss" scoped>
.card {
  border-radius: 15px;
  box-shadow: 0 5px 5px 0 #8e8e8e;
}
</style>
