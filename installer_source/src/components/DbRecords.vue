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
            :items="records"
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
                >
                  {{ item.class }}
                </td>
                <td
                  align="left"
                >
                  {{ item.totaal }}
                </td>
                <td
                  align="left"
                >
                  {{ item.laatste_aanpassing }}
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            v-if="(records.length > 0)"

            color="primary"
            @click="records_info()"
          >
            Verversen
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
        records: [],
        filldata: true,

        headers: [
          { text: 'Naam', value: 'class' },
          { text: 'Record', value: 'records' },
          { text: 'Laatste_aanpassing', value: 'records' },
        ],
      }
    },

    mounted () {
      this.isIngelogd()
    },

    beforeDestroy () {
      // clear the timeout before the component is destroyed
      clearTimeout(this.isIngelogdTimer)
    },

    methods: {
      async records_info () {
        this.busy = true
        axios.post('/install_php/records.php', this.$store.state.dbTables, {
          auth: {
            username: this.$store.state.heliosGebruikersNaam,
            password: this.$store.state.heliosWachtwoord,
          },
        }).then(response => {
          this.busy = false
          if (response.data !== undefined) { this.records = response.data }
        })
          .catch(e => {
            this.busy = false
            console.log(e)
            alert('Backend werkt niet. Controleer of de php functies werken')
          })
      },

      async isIngelogd () {
        if ((this.$store.state.heliosGebruikersNaam != null) && (this.$store.state.heliosWachtwoord != null) && (this.$store.state.dbTables)) {
          this.records_info()
        } else {
          this.isIngelogdTimer = setTimeout(() => {
            this.isIngelogd()
          }, 1000)
        }
      },
    },
  }

 </script>
