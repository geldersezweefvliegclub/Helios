<template>
  <v-container>
    <v-stepper v-model="step">
      <v-stepper-header>
        <v-stepper-step
            :complete="step > 0"
            step="0"
        >
          Database account
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            :complete="step > 1"
            step="1"
        >
          Installatie-account aanmaken
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
            <v-card class="mb-12" >
              <v-card-text>
                <v-row class="d-flex justify-center">
                  <v-col cols="4">
                    <v-text-field
                      ref="databaseHost"
                      v-model="databaseAccountGegevens.databaseHost"
                      type="text"
                      label="Database server"
                      required
                      :rules="[rules.required]"
                    ></v-text-field>
                    <v-text-field
                      ref="databaseNaam"
                      v-model="databaseAccountGegevens.databaseNaam"
                      type="text"
                      label="Databasenaam"
                      :rules="[rules.required]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="4">
                    <v-text-field
                      ref="databaseGebruiker"
                      v-model="databaseAccountGegevens.databaseGebruiker"
                      type="text"
                      label="Gebruikersnaam"
                      :rules="[rules.required]"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="4">  
                    <v-text-field
                      ref="databaseWachtwoord"
                      prepend-icon="mdi-lock"
                      label="Wachtwoord herhalen"
                      v-model="databaseAccountGegevens.databaseWachtwoord"
                      :rules="[rules.required, rules.min, rules.sterk]"
                      :type="verbergWachtwoord ? 'password' : 'text'"
                      :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                      @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
                    ></v-text-field>
                    <v-text-field
                      ref="databaseWachtwoord2"
                      prepend-icon="mdi-lock"
                      label="Wachtwoord herhalen"
                      v-model="databaseAccountGegevens.databaseWachtwoord2"
                      :rules="[rules.required, rules.min, rules.sterk, rules.matchDbPassword]"
                      :type="verbergWachtwoord ? 'password' : 'text'"
                      :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                      @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
                    ></v-text-field>
                    <password v-model="databaseAccountGegevens.databaseWachtwoord" :strength-meter-only="true"/>
                  </v-col>
                </v-row>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn 
                  color="primary" 
                  :disabled="!dbDataValid()"
                  @click="testVerbinding()">Test</v-btn>
              </v-card-actions>              
            </v-card>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" :hidden="!databaseVerbonden" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>    
          </v-stepper-content>

          <v-stepper-content step="1">
            <v-card class="mb-12" >
              <v-card-text>
                <v-row class="d-flex justify-center">
                  <v-col cols="6">
                    <v-text-field
                        value="helios"
                        type="text"
                        label="Gebruikersnaam"
                        disabled
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                        prepend-icon="mdi-lock"
                        label="Wachtwoord herhalen"
                        v-model="helios.Wachtwoord"
                        :rules="[rules.required, rules.min, rules.sterk]"
                        :type="verbergWachtwoord ? 'password' : 'text'"
                        :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                        @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
                    ></v-text-field>
                    <v-text-field
                        prepend-icon="mdi-lock"
                        label="Wachtwoord herhalen"
                        v-model="helios.Wachtwoord2"
                        :rules="[rules.required, rules.min, rules.sterk, rules.matchPassword]"
                        :type="verbergWachtwoord ? 'password' : 'text'"
                        :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                        @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
                    ></v-text-field>
                    <password v-model="helios.Wachtwoord" :strength-meter-only="true"/>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <v-card-actions>
              <v-btn text @click="vorigeStap()">Vorige</v-btn>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>   
          </v-stepper-content>          

          <v-stepper-content step="2">
            <v-card class="mb-12" >
              <v-card-text>
                <v-checkbox
                    v-model="databaseAanmaken"
                    label="Database aanmaken"
                ></v-checkbox>
              </v-card-text>
            </v-card>

            <v-card-actions>
              <v-btn text @click="vorigeStap()">Vorige</v-btn>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>   
          </v-stepper-content>

          <v-stepper-content step="3">
            <v-card class="mb-12" >
              <v-card-text>Aangeven welke tabellen en dan knop voor aanmaken</v-card-text>
            </v-card>

            <v-card-actions>
              <v-btn text @click="vorigeStap()">Vorige</v-btn>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>   
          </v-stepper-content>
        </v-stepper-items>
      </v-form>
    </v-stepper>
  </v-container>
</template>

<script>
  import zxcvbn from 'zxcvbn'
  import Password from 'vue-password-strength-meter'

  export default {
    components: { Password },
  data() {
    return {
      databaseVerbonden: false,

      verbergWachtwoord: true,
      valid: false,
      step: 0,
      helios: {
        Wachtwoord: "",
        Wachtwoord2: "",
      },

      databaseAccountGegevens: {
        databaseHost: "",
        databaseNaam: "",
        databaseGebruiker: "",
        databaseWachtwoord: "",
        databaseWachtwoord2: "",
      },
      databaseAanmaken: true,

      rules: {
        required: value => !!value || 'Dit veld is verplicht',
        min: v => v.length >= 8 || 'Minimaal 6 characters or more for your password',
        sterk: v => zxcvbn(v).score >= 3 || 'Kies een sterker wachtwoord. Kies een mix van letters, cijfers en karakters',        
        matchPassword: () => {
          return this.helios.Wachtwoord=== this.helios.Wachtwoord2 || 'Wachtwoorden komen niet overeen!';
        },
        matchDbPassword: () => {
          return this.databaseAccountGegevens.databaseWachtwoord === this.databaseAccountGegevens.databaseWachtwoord2 || 'Database wachtwoorden komen niet overeen!';
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

  computed: {
    dbDataValid() {
      console.log(this.$form);
      return true;
      //return this.$refs['databaseHost'].hasError;
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
