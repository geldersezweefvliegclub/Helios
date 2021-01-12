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
          <installer-account @isIngelogd="isIngelogd" />
        </v-stepper-content>

        <v-stepper-content step="1">
          <db-info
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
          <db-tables />

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
          <db-views @viewsAangemaakt="viewsAangemaakt" />

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
          <db-records />

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
          step: 0,

        }
      },

      events: {

      },

      async mounted () {
        this.busy = true
        this.helios_info().then((response) => {
          this.$store.commit('HeliosInfo', response.data)
          this.busy = false
        })
      },

      methods:
        {
          volgendeStap () {
            this.step = (this.step + 1) % 5
          },
          vorigeStap () {
            this.step = (this.step + 4) % 5
          },

          dbAangemaakt (arg) {
            this.helios_info().then((response) => {
              this.$store.commit('HeliosInfo', response.data)
            })
            this.volgendeStap()
          },

          viewsAangemaakt (arg) {
            this.volgendeStap()
          },

          isIngelogd (arg) {
            this.helios_info().then((response) => {
              this.$store.commit('HeliosInfo', response.data)
            })
            this.volgendeStap()
          },

          async helios_info () {
            const response = await axios.get('/install_php/helios_info.php').catch(e => {
              console.log(e)
              alert('Backend werkt niet. Controleer of de php functies werken')
            })
            return response
          },
        },
    }
  </script>
