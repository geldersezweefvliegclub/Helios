<template>
  <v-container style="height:90vh;">
    <iframe
      :src="url"
      width="100%"
      height="100%"
      frameborder="0"
    />
  </v-container>
</template>

<script>

  export default
    {
      data () {
        return {

        }
      },

      events: {

      },

      computed: {
        url: function () {
          return '../docs/index.php'
        },
      },

      mounted () {

      },

      methods:
        {

        },
    }
  </script>
