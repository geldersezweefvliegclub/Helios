<template>
  <v-app-bar
      app
      color="primary"
      light
  >
    <v-app-bar-title>
      <router-link to="/" class="white--text">Installatie</router-link>
    </v-app-bar-title>
    <v-spacer></v-spacer>

    <v-btn text>
      <router-link to="/docs" class="white--text">Docs</router-link>
    </v-btn>
    <v-btn text>
      <router-link to="/sha1" class="white--text">SHA1</router-link>
    </v-btn>
    <v-btn text>
      <router-link to="/install" class="white--text">Installeren</router-link>
    </v-btn>
  </v-app-bar>
</template>

<script>
export default {
  name: "Appbar"
}
</script>

<style lang="scss">
.v-btn__content, .v-app-bar-title__content {
  a {
    text-decoration: none !important;
  }
}
</style>
