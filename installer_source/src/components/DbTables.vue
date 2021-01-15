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
            :items="dbTables"
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
                  width="90%"
                >
                  {{ item.class }}
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-checkbox
            v-if="dbTables.length > 0 && allesIngevuld == false"
            v-model="filldata"
            label="Vullen tabellen met data"
          />
          <v-spacer />
          <v-btn
            v-if="dbTables.length > 0 && allesIngevuld == false"

            color="primary"
            @click="create_tabellen()"
          >
            Aanmaken
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
      dbTables: Array,
    },

    data () {
      return {
        busy: false,
        isIngelogdTimer: null,
        filldata: true,

        headers: [
          { text: 'Bestaat', value: 'bestaat' },
          { text: 'Naam', value: 'class' },

        ],
      }
    },

    computed: {
      allesIngevuld: function () {
        let retValue = true

        this.dbTables.forEach(function (table) {
          if (table.bestaat === false) {
            retValue = false
          }
        })
        return retValue
      },
    },

    methods: {
      async create_tabellen () {
        this.busy = true

        axios.post('/install_php/create_tables.php?filldata=' + (this.filldata ? 'true' : 'false'), this.dbTables, {
          auth: {
            username: this.$store.state.heliosGebruikersNaam,
            password: this.$store.state.heliosWachtwoord,
          },
        })
          .then(response => {
            this.busy = false
            this.$emit('tabellenAangemaakt', 'done')
          }).catch(e => {
            console.log(e)
            this.busy = false
            alert('Fout in backend')
          })
      },
    },
  }

 </script>
