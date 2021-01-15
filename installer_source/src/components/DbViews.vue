<template>
  <v-container>
    <div class="text-center">
      <v-overlay :value="busy">
        <v-col class="fill-height">
          <div class="text-center">
            <v-progress-circular indeterminate />
          </div>
        </v-col>
      </v-overlay>

      <v-card class="mb-12">
        <v-card-text>
          <v-data-table
            :headers="headers"
            :items="dbViews"
            :hide-default-footer="true"
            fixed-header
            disable-pagination
            :no-data-text="busy ? 'Data wordt opgehaald' : 'Geen informatie beschikbaar'"
          >
            <template
              #item="{ item, index }"
            >
              <tr
                :data-id="index"
              >
                <td
                  align="left"
                  class="d-none d-lg-table-cell"
                >
                  <template v-if="item.bestaat == 1">
                    <v-icon>
                      mdi-check
                    </v-icon>
                  </template>
                  <template v-else>
                    <v-icon>
                      mdi-checkbox-blank-outline
                    </v-icon>
                  </template>
                </td>
                <td
                  align="left"
                  class="d-none d-lg-table-cell"
                >
                  <template v-if="item.verwijderd == 1">
                    <v-icon>
                      mdi-check
                    </v-icon>
                  </template>
                  <template v-else>
                    <v-icon>
                      mdi-checkbox-blank-outline
                    </v-icon>
                  </template>
                </td>
                <td
                  align="left"
                >
                  {{ item.class }}
                </td>
                <td
                  align="left"
                  width="50%"
                >
                  <v-btn
                    color="primary"
                    @click="create_views([item])"
                  >
                    Aanmaken
                  </v-btn>
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            v-if="(dbViews.length > 0)"

            color="primary"
            @click="create_views()"
          >
            Alle views aanmaken
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </v-container>
</template>

<script>
  import axios from 'axios'

  export default {
    props: {
      dbViews: Array,
    },

    data () {
      return {
        busy: false,
        filldata: true,

        headers: [
          { text: 'Bestaat', value: 'bestaat' },
          { text: 'Verwijderd', value: 'verwijderd' },
          { text: 'Naam', value: 'class' },
          { text: '', value: '' },

        ],
      }
    },

    methods: {
      async create_views (tabel) {
        this.busy = true

        axios.post('/install_php/create_views.php', (tabel === undefined) ? this.dbViews : tabel, {
          auth: {
            username: this.$store.state.heliosGebruikersNaam,
            password: this.$store.state.heliosWachtwoord,
          },
        })
          .then(response => {
            this.busy = false

            if (tabel === undefined) {
              this.$emit('viewsAangemaakt', 'done')
            }
          }).catch(e => {
            console.log(e)
            this.busy = false
            alert('Fout in backend')
          })
      },
    },
  }

 </script>
