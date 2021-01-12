<template>
  <v-container>
    <v-overlay :value="busy">
      <v-col class="fill-height">
        <div class="text-center">
          <v-progress-circular indeterminate />
        </div>
      </v-col>
    </v-overlay>

    <v-col
      class="fill-height"
    >
      <v-card class="mb-12">
        <v-card-text>
          <v-row class="d-flex justify-center">
            <v-col cols="6">
              <v-text-field
                v-model="ingave"
                type="text"
                label="Ingave"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="sha"
                label="Sha"
                type="text"
                disabled
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="primary"
            @click="sha1()"
          >
            Converteer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-col>
  </v-container>
</template>

<script>
  import axios from 'axios'

  export default {
    data () {
      return {
        ingave: null,
        sha: null,
        busy: false,
      }
    },

    methods: {
      sha1 () {
        this.busy = true

        axios.post('/install_php/sha1.php', { ingave: this.ingave }).then(response => {
          this.busy = false
          this.sha = response.data
        })
      },
    },
  }

</script>
