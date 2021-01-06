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
          step="1"
        >
          Installatie-account
        </v-stepper-step>

        <v-divider />

        <v-stepper-step
          :complete="step > 1"
          step="2"
        >
          Database account
        </v-stepper-step>

        <v-divider />

        <v-stepper-step
          step="3"
          :complete="step > 2"
        >
          Database aanmaken
        </v-stepper-step>

        <v-divider />

        <v-stepper-step
          step="4"
          :complete="step > 3"
        >
          Tabellen aanmaken
        </v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="1">
          <installer-account @isIngelogd="isIngelogd" />
        </v-stepper-content>

        <v-stepper-content step="2">
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

        <v-stepper-content step="3">
          <v-card class="mb-12">
            <v-card-text>Aangeven welke tabellen en dan knop voor aanmaken</v-card-text>
          </v-card>

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
      </v-stepper-items>
    </v-stepper>
  </v-container>
</template>

<script>
  import DbInfo from '@/components/DbInfo.vue'
  import InstallerAccount from '@/components/InstallerAccount.vue'

  export default
    {
      components: { DbInfo, InstallerAccount },

      data () {
        return {
          db_info: true,
          busy: false,
          step: 1,

        }
      },

      events: {

      },

      methods:
        {
          volgendeStap () {
            this.step = (this.step + 1) % 3
            console.log(this.step)
          },
          vorigeStap () {
            this.step = (this.step + 2) % 3
          },

          dbAangemaakt (dbInfo) {
            this.volgendeStap()
          },

          isIngelogd (dbInfo) {
            this.volgendeStap()
          },
        },
    }
  </script>
