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
            :items="db_tables"
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
            v-if="allesIngevuld == false"
            v-model="filldata"
            label="Vullen tabellen met data"
          />
          <v-spacer />
          <v-btn
            v-if="(db_tables.length > 0) && (allesIngevuld == false)"

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

    },

    data () {
      return {
        busy: false,
        isIngelogdTimer: null,
        db_tables: [],
        filldata: true,

        headers: [
          { text: 'Bestaat', value: 'bestaat' },
          { text: 'Naam', value: 'class' },

        ],
      }
    },

    computed: {
      allesIngevuld: function () {
        this.db_tables.forEach(function (table) {
          if (table.bestaat === false) { return false }
        })
        return true
      },
    },

    mounted () {
      this.isIngelogd()
    },

    beforeDestroy () {
      // clear the timeout before the component is destroyed
      clearTimeout(this.isIngelogdTimer)
    },

    methods: {
      async tables_info () {
        this.busy = true
        axios.get('/install_php/tables_info.php', {
          auth: {
            username: this.$store.state.heliosGebruikersNaam,
            password: this.$store.state.heliosWachtwoord,
          },
        }).then(response => {
          this.busy = false
          this.db_tables = response.data
          this.$store.commit('DbTables', this.db_tables)
        })
          .catch(e => {
            this.busy = false
            console.log(e)
            alert('Backend werkt niet. Controleer of de php functies werken')
          })
      },

      async isIngelogd () {
        if ((this.$store.state.heliosGebruikersNaam != null) && (this.$store.state.heliosWachtwoord != null) && (this.$store.state.heliosInfo.db_info === true)) {
          this.tables_info()
        } else {
          this.isIngelogdTimer = setTimeout(() => {
            this.isIngelogd()
          }, 1000)
        }
      },

      async create_tabellen () {
        this.busy = true

        axios.post('/install_php/create_tables.php?filldata=' + (this.filldata ? 'true' : 'false'), this.db_tables, {
          auth: {
            username: this.$store.state.heliosGebruikersNaam,
            password: this.$store.state.heliosWachtwoord,
          },
        })
          .then(response => {
            this.busy = false
            this.db_tables = response.data
            this.$store.commit('DbTables', this.db_tables)
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
