<template>
  <v-container>
    <v-stepper v-model="step">
      <v-stepper-header>
        <v-stepper-step
            :complete="step > 0"
            step="0"
        >
          Installatie-account aanmaken
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            :complete="step > 1"
            step="1"
        >
          Database account aanmaken
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            step="2"
            :complete="step > 2"
        >
          Database aanmaken
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            step="3"
            :complete="step > 3"
        >
          Tabellen aanmaken
        </v-stepper-step>
      </v-stepper-header>

      <v-form
          ref="form"
          v-model="valid"
      >
        <v-stepper-items>
          <v-stepper-content step="0">
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
                        required
                        :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                        v-model="databaseAccountGegevens.databaseNaam"
                        type="text"
                        label="Databasenaam"
                        :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                        v-model="databaseAccountGegevens.databaseGebruiker"
                        type="text"
                        label="Gebruikersnaam"
                        :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                        v-model="databaseAccountGegevens.databaseWachtwoord"
                        type="password"
                        label="Wachtwoord"
                        :rules="[rules.required]"
                        @change="this.$refs.form.validate();"
                    ></v-text-field>
                    <v-text-field
                        :rules="[rules.required, rules.matchPassword]"
                        v-model="databaseAccountGegevens.databaseWachtwoord2"
                        type="password"
                        label="Wachtwoord herhalen"

                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            <v-btn text @click="vorigeStap()">Vorige</v-btn>
          </v-stepper-content>

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
                        required
                        :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                        v-model="databaseAccountGegevens.databaseNaam"
                        type="text"
                        label="Databasenaam"
                        :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                        v-model="databaseAccountGegevens.databaseGebruiker"
                        type="text"
                        label="Gebruikersnaam"
                        :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                        v-model="databaseAccountGegevens.databaseWachtwoord"
                        type="password"
                        label="Wachtwoord"
                        :rules="[rules.required]"
                        @change="this.$refs.form.validate();"
                    ></v-text-field>
                    <v-text-field
                        :rules="[rules.required, rules.matchPassword]"
                        v-model="databaseAccountGegevens.databaseWachtwoord2"
                        type="password"
                        label="Wachtwoord herhalen"

                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            <v-btn text @click="vorigeStap()">Vorige</v-btn>
          </v-stepper-content>

          <v-stepper-content step="2">
            <v-card
                class="mb-12"
            >
              <v-card-text>
                <v-checkbox
                    v-model="databaseAanmaken"
                    label="Database aanmaken"
                ></v-checkbox>
              </v-card-text>
            </v-card>

            <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            <v-btn text @click="vorigeStap()">Vorige</v-btn>
          </v-stepper-content>

          <v-stepper-content step="3">
            <v-card
                class="mb-12"
                color="grey lighten-1"
            >
              <v-card-text>Aangeven welke tabellen en dan knop voor aanmaken</v-card-text>
            </v-card>

            <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            <v-btn text @click="vorigeStap()">Vorige</v-btn>
          </v-stepper-content>
        </v-stepper-items>
      </v-form>
    </v-stepper>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      valid: false,
      step: 0,
      databaseAccountGegevens: {
        databaseHost: undefined,
        databaseNaam: undefined,
        databaseGebruiker: undefined,
        databaseWachtwoord: undefined,
        databaseWachtwoord2: undefined,
      },
      databaseAanmaken: undefined,
      rules: {
        required: value => !!value || 'Dit veld is verplicht',
        matchPassword: () => {
          return this.databaseAccountGegevens.databaseWachtwoord === this.databaseAccountGegevens.databaseWachtwoord2 || 'Wachtwoorden komen niet overeen!';
        }
      }
    }
  },
  mounted() {
    this.validateForm();
  },
  watch: {
    immediate: true,
    async handler() {
      await this.$nextTick();
      this.validateForm();
    }
  },
  methods: {
    validateForm() {
      this.$refs.form.validate();
    },
    volgendeStap() {
      (this.step + 1 <= 3) ? this.step++ : this.step = 4;
      console.log(this.step)
    },
    vorigeStap() {
      (this.step - 1 > 0) ? this.step-- : this.step = 0;
    }
  }
}
</script>
