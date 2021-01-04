<template>
    <v-container>
        <v-overlay :value="busy">
          <v-flex fill-height>
            <div class="text-center">
              <v-progress-circular indeterminate />
            </div>
          </v-flex>
        </v-overlay>

        <v-card class="mb-12" v-if="initGedaan == false">
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
                    :rules="[rules.required]"
                    :type="verbergWachtwoord ? 'password' : 'text'"
                    :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                    @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
                ></v-text-field>
                <v-text-field
                    ref="databaseWachtwoord2"
                    prepend-icon="mdi-lock"
                    label="Wachtwoord herhalen"
                    v-model="databaseAccountGegevens.databaseWachtwoord2"
                    :rules="[rules.required, rules.matchDbPassword]"
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
                :disabled="testDisabled"
                color="primary" 
                @click="testVerbinding()">Test</v-btn>
            </v-card-actions>              
        </v-card>
    </v-container>
</template>
<script>
import App from '../../../install/sourcecode/src/App.vue'
  export default {
  components: { App },
    name: 'DbInfo',

    props: {
        databaseHost: {
        type: String,
        default: '',
      },
        databaseNaam: {
        type: String,
        default: '',
      },
        databaseGebruiker: {
        type: String,
        default: '',
      },
        databaseWachtwoord: {
        type: String,
        default: '',
      },
        databaseWachtwoord2:: {
        type: String,
        default: '',
      },

      heading: {
        type: String,
        default: '',
      },
      subtitle: {
        type: String,
        default: '',
      },
    },

    data() {
      return {
        databaseVerbonden: false,
        databaseBestaat: true,
        databaseAanmaken: false,

        initGedaan: true,
        heliosObjecten: null,

        verbergWachtwoord: true,
        testDisabled: false,
        timeout: null,
        valid: false,
        busy: false,
        step: 1,

        helios: {
          Wachtwoord: "",
          Wachtwoord2: "",
        },

        databaseAccountGegevens: {

        },
  }
</script>
