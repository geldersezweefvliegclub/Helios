<template>
  <v-container>
    <v-overlay :value="busy">
      <v-col class="fill-height">
        <div class="text-center">
          <v-progress-circular indeterminate />
        </div>
      </v-col>
    </v-overlay>

    <v-stepper v-model="step">
      <v-stepper-header>
        <v-stepper-step
          :complete="step > 0"
          step="0"
        >
          Installatie-account
        </v-stepper-step>

        <v-divider />
        <v-stepper-step
          :complete="step > 1"
          step="1"
        >
          Database account
        </v-stepper-step>

        <v-divider />
        <v-stepper-step
          step="2"
          :complete="step > 2"
        >
          Tabellen aanmaken
        </v-stepper-step>

        <v-divider />
        <v-stepper-step
          step="3"
          :complete="step > 3"
        >
          Views aanmaken
        </v-stepper-step>

        <v-divider />
        <v-stepper-step
          step="4"
          :complete="step > 4"
        >
          Resultaat
        </v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="0">
          <installer-account
            :installer-account="heliosInfo.installer_account"
            @isIngelogd="isIngelogd"
          />
        </v-stepper-content>

        <v-stepper-content step="1">
          <db-info
            :db-bestaat="heliosInfo.db_bestaat"
            @dbAangemaakt="dbAangemaakt"
          />

          <v-card-actions>
            <v-btn
              text
              @click="vorigeStap()"
            >
              Vorige
            </v-btn>
            <v-spacer />
            <v-btn
              color="primary"
              @click="volgendeStap()"
            >
              Volgende
            </v-btn>
          </v-card-actions>
        </v-stepper-content>

        <v-stepper-content step="2">
          <db-tables :db-tables="db_tables" />

          <v-card-actions>
            <v-btn
              text
              @click="vorigeStap()"
            >
              Vorige
            </v-btn>
            <v-spacer />
            <v-btn
              color="primary"
              @click="volgendeStap()"
            >
              Volgende
            </v-btn>
          </v-card-actions>
        </v-stepper-content>

        <v-stepper-content step="3">
          <db-views
            :db-views="db_views"
            @viewsAangemaakt="viewsAangemaakt"
          />

          <v-card-actions>
            <v-btn
              text
              @click="vorigeStap()"
            >
              Vorige
            </v-btn>
            <v-spacer />
            <v-btn
              color="primary"
              @click="volgendeStap()"
            >
              Volgende
            </v-btn>
          </v-card-actions>
        </v-stepper-content>

        <v-stepper-content step="4">
          <db-records
            :db-records="db_records"
            @refreshRecords="records_info"
          />

          <v-card-actions>
            <v-btn
              text
              @click="vorigeStap()"
            >
              Vorige
            </v-btn>
            <v-spacer />
          </v-card-actions>
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </v-container>
</template>

<script>
  import axios from 'axios'
  import DbInfo from '@/components/DbInfo.vue'
  import InstallerAccount from '@/components/InstallerAccount.vue'
  import DbTables from '@/components/DbTables.vue'
  import DbViews from '@/components/DbViews.vue'
  import DbRecords from '@/components/DbRecords.vue'

  export default
    {
      components: { DbInfo, DbTables, DbViews, DbRecords, InstallerAccount },

      data () {
        return {
          busy: false,
          heliosInfo: {
            installer_account: false,
            db_bestaat: false,
          },
          db_tables: [],
          db_views: [],
          db_records: [],
          step: 0,

        }
      },

      events: {

      },

      async mounted () {
        this.helios_info()
      },

      methods:
        {
          volgendeStap () {
            this.step = (this.step + 1) % 5
            this.laden()
          },
          vorigeStap () {
            this.step = (this.step + 4) % 5
            this.laden()
          },

          laden () {
            switch (this.step) {
              case 0: this.helios_info()
                      break
              case 1: this.helios_info()
                      break
              case 2: this.tables_info()
                      break
              case 3: this.views_info()
                      break
              case 4: this.records_info()
                      break
            }
          },

          isIngelogd (arg) {
            this.volgendeStap()
          },

          dbAangemaakt (arg) {
            this.volgendeStap()
          },

          tabellenAangemaakt (arg) {
            this.volgendeStap()
          },

          viewsAangemaakt (arg) {
            this.volgendeStap()
          },

          async helios_info () {
            this.busy = true
            axios.get('/install_php/info_helios.php').then(response => {
              this.busy = false
              this.heliosInfo = response.data
            })
              .catch(e => {
                this.busy = false
                console.log(e)
                alert('Backend werkt niet. Controleer of de php functies werken')
              })
          },

          async tables_info () {
            this.busy = true
            axios.get('/install_php/info_tables.php', {
              auth: {
                username: this.$store.state.heliosGebruikersNaam,
                password: this.$store.state.heliosWachtwoord,
              },
            }).then(response => {
              this.busy = false
              this.db_tables = response.data
            })
              .catch(e => {
                this.busy = false
                console.log(e)
                alert('Backend werkt niet. Controleer of de php functies werken')
              })
          },

          async views_info () {
            this.busy = true
            axios.post('/install_php/info_views.php', this.db_tables, {
              auth: {
                username: this.$store.state.heliosGebruikersNaam,
                password: this.$store.state.heliosWachtwoord,
              },
            }).then(response => {
              this.busy = false
              this.db_views = response.data
            })
              .catch(e => {
                this.busy = false
                console.log(e)
                alert('Backend werkt niet. Controleer of de php functies werken')
              })
          },

          async records_info () {
            this.busy = true
            axios.post('/install_php/info_records.php', this.db_tables, {
              auth: {
                username: this.$store.state.heliosGebruikersNaam,
                password: this.$store.state.heliosWachtwoord,
              },
            }).then(response => {
              this.busy = false
              this.db_records = response.data
            })
              .catch(e => {
                this.busy = false
                console.log(e)
                alert('Backend werkt niet. Controleer of de php functies werken')
              })
          },
        },
    }
  </script>
