<template>
  <v-form
    ref="form"
    v-model="valid"
  >
    <v-card
      v-if="initGedaan == false"
      class="mb-12"
    >
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
            />
            <v-text-field
              ref="databaseNaam"
              v-model="databaseAccountGegevens.databaseNaam"
              type="text"
              label="Databasenaam"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="4">
            <v-text-field
              ref="databaseGebruiker"
              v-model="databaseAccountGegevens.databaseGebruiker"
              type="text"
              label="Gebruikersnaam"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="4">
            <v-text-field
              ref="databaseWachtwoord"
              v-model="databaseAccountGegevens.databaseWachtwoord"
              prepend-icon="mdi-lock"
              label="Wachtwoord herhalen"
              :rules="[rules.required]"
              :type="verbergWachtwoord ? 'password' : 'text'"
              :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
              @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
            />
            <v-text-field
              ref="databaseWachtwoord2"
              v-model="databaseAccountGegevens.databaseWachtwoord2"
              prepend-icon="mdi-lock"
              label="Wachtwoord herhalen"
              :rules="[rules.required, rules.matchDbPassword]"
              :type="verbergWachtwoord ? 'password' : 'text'"
              :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
              @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
            />
            <password
              v-model="databaseAccountGegevens.databaseWachtwoord"
              :strength-meter-only="true"
            />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          :disabled="testDisabled"
          color="primary"
          @click="testVerbinding()"
        >
          Test
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<script>
  import axios from 'axios'
  import zxcvbn from 'zxcvbn'
  import Password from 'vue-password-strength-meter'

  export default {
    components: { Password },

    props: {

    },

    data () {
      return {
        verbergWachtwoord: true,
        testDisabled: false,
        timeout: null,
        valid: false,

        databaseAccountGegevens: {
          databaseHost: '',
          databaseNaam: '',
          databaseGebruiker: '',
          databaseWachtwoord: '',
          databaseWachtwoord2: '',
        },

        rules: {
          required: value => !!value || 'Dit veld is verplicht',
          min: v => v.length >= 8 || 'Minimaal 6 characters or more for your password',
          sterk: v => zxcvbn(v).score >= 3 || 'Kies een sterker wachtwoord. Kies een mix van letters, cijfers en karakters',
          matchPassword: () => {
            return this.helios.Wachtwoord === this.helios.Wachtwoord2 || 'Wachtwoorden komen niet overeen!'
          },
          matchDbPassword: () => {
            return this.databaseAccountGegevens.databaseWachtwoord === this.databaseAccountGegevens.databaseWachtwoord2 || 'Database wachtwoorden komen niet overeen!'
          },
        },
      }
    },

    mounted () {
      this.validateForm()

      this.busy = true
      axios.get('/install_php/helios_info.php')
        .then(response => {
          if (response.data.initGedaan === true) {
            // De database gegevens zijn al geconfigureerd
            this.databaseVerbonden = true // anders kom je nooit naar step 2 (wanneer gebruiker terug naar step 1 gaat)
            this.databseBestaat = true
          } else {
            this.initGedaan = false
          }
          this.heliosObjecten = response.data.Objecten
          this.busy = false
        }).catch(e => {
          console.log(e)
          alert('Backend werkt niet. Controleer of de php functies werken')
        })
    },

    beforeDestroy () {
      // clear the timeout before the component is destroyed
      clearTimeout(this.timeout)
    },

    methods: {
      validateForm () {
        this.$refs.form.validate()
      },
    },
  }
</script>
