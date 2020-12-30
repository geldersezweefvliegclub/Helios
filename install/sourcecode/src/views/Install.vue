<template>
  <v-container>
    <v-stepper non-linear>
      <v-stepper-header>
        <v-stepper-step
            editable
            step="1"
        >
          Database account aanmaken
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            editable
            step="2"
        >
          Database aanmaken
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            step="3"
            editable
        >
          Tabellen aanmaken
        </v-stepper-step>
      </v-stepper-header>

      <v-stepper-items>
        <v-stepper-content step="1">
          <v-card
              class="mb-12"
          >
            <v-card-text>
              <v-row class="d-flex justify-center">
                <v-col
                    cols="3">
                  <v-text-field
                      v-model="databaseAccountGegevens.databaseHost"
                      type="text"
                      label="Database server"
                  ></v-text-field>
                  <v-text-field
                      v-model="databaseAccountGegevens.databaseNaam"
                      type="text"
                      label="Databasenaam"
                  ></v-text-field>
                  <v-text-field
                      v-model="databaseAccountGegevens.databaseGebruiker"
                      type="text"
                      label="Gebruikersnaam"
                  ></v-text-field>
                  <v-text-field
                      v-model="databaseAccountGegevens.databaseWachtwoord"
                      type="password"
                      label="Wachtwoord"
                  ></v-text-field>
                  <v-text-field
                      v-model="databaseAccountGegevens.databaseWachtwoord"
                      type="password"
                      label="Wachtwoord herhalen"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <v-btn
              color="primary"
              @click="e1 = 2"
          >
            Continue
          </v-btn>

          <v-btn text>
            Cancel
          </v-btn>
        </v-stepper-content>

        <v-stepper-content step="2">
          <v-card
              class="mb-12"
              color="grey lighten-1"
          >
            <v-card-text>Database aanmaken (alleen checkbox)</v-card-text>
          </v-card>

          <v-btn
              color="primary"
              @click="e1 = 3"
          >
            Continue
          </v-btn>

          <v-btn text>
            Cancel
          </v-btn>
        </v-stepper-content>

        <v-stepper-content step="3">
          <v-card
              class="mb-12"
              color="grey lighten-1"
          >
            <v-card-text>Aangeven welke tabellen en dan knop voor aanmaken</v-card-text>
          </v-card>

          <v-btn
              color="primary"
              @click="e1 = 1"
          >
            Continue
          </v-btn>

          <v-btn text>
            Cancel
          </v-btn>
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </v-container>
</template>

<script>
export default {
  data: function () {
    return {
      databaseAccountGegevens: {
        databaseHost: undefined,
        databaseNaam: undefined,
        databaseGebruiker: undefined,
        databaseWachtwoord: undefined,
      }
    }
  }
}
</script>
