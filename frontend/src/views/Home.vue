<template>
  <div class="home pa-4"></div>
</template>

<script>

export default {
  name: 'Home',
  data: () => ({
    a: '',
  }),
  mounted(){
  }
}
</script>

<style scoped>
.v-card {
  transition: opacity 0.4s ease-in-out;
}

.v-card:not(.on-hover) {
  opacity: 0.8;
}

.show-btns {
  color: rgba(255, 255, 255, 1) !important;
}
</style>
