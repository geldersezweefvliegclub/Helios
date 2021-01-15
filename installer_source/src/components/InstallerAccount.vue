<template>
  <v-container>
    <v-overlay :value="busy">
      <v-col class="fill-height">
        <div class="text-center">
          <v-progress-circular indeterminate />
        </div>
      </v-col>
    </v-overlay>

    <v-col
      v-if="installerAccount == true"
      class="fill-height"
    >
      <div class="text-center">
        <v-chip
          color="secondary"
          text-color="white"
          style="border-radius: 4px"
          class="mt-n10 ml-n3"
        >
          Account is beschikbaar, vul wachtwoord in
        </v-chip>
      </div>
    </v-col>
    <v-form
      ref="form"
      v-model="valid"
    >
      <v-card class="mb-12">
        <v-card-text>
          <v-row class="d-flex justify-center">
            <v-col cols="6">
              <v-text-field
                v-model="helios.GebruikersNaam"
                type="text"
                label="Gebruikersnaam"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="helios.Wachtwoord"
                prepend-icon="mdi-lock"
                label="Wachtwoord"
                :rules="[rules.required]"
                :type="verbergWachtwoord ? 'password' : 'text'"
                :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
              />
              <v-text-field
                v-if="installerAccount == false"
                v-model="helios.Wachtwoord2"
                prepend-icon="mdi-lock"
                label="Wachtwoord herhalen"
                :rules="[rules.required, rules.min, rules.sterk, rules.matchPassword]"
                :type="verbergWachtwoord ? 'password' : 'text'"
                :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
              />

              <password
                v-if="installerAccount == false"
                v-model="helios.Wachtwoord"
                :strength-meter-only="true"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            v-if="installerAccount == true"
            color="primary"
            @click="login()"
          >
            Inloggen
          </v-btn>
          <v-btn
            v-else
            color="primary"
            @click="create_account()"
          >
            Aanmaken
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-container>
</template>

<script>
  import axios from 'axios'
  import zxcvbn from 'zxcvbn'
  import Password from 'vue-password-strength-meter'

  export default {
    components: { Password },

    props: {
      installerAccount: Boolean,
    },

    data () {
      return {
        verbergWachtwoord: true,
        isAangepast: true,

        busy: false,
        valid: false,

        helios: {
          GebruikersNaam: '',
          Wachtwoord: '',
          Wachtwoord2: '',
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

    methods: {
      validateForm () {
        this.$refs.form.validate()
      },

      async helios_info () {
        const response = await axios.get('/install_php/helios_info.php').catch(e => {
          console.log(e)
          alert('Backend werkt niet. Controleer of de php functies werken')
        })
        return response
      },

      async create_account () {
        this.validateForm()
        if (!this.valid) {
          alert('Zorg dat alle velden correct zijn ingevoerd')
          return
        }
        this.busy = true

        axios.get('/install_php/create_account.php', {
          auth: {
            username: this.helios.GebruikersNaam,
            password: this.helios.Wachtwoord,
          },
        })
          .then(response => {
            this.busy = false

            if (response.data != null) {
              this.$store.commit('HeliosGebruikersNaam', this.helios.GebruikersNaam)
              this.$store.commit('HeliosWachtwoord', this.helios.Wachtwoord)
              this.$emit('isIngelogd', this.helios)
            }
          }).catch(e => {
            console.log(e)
            this.busy = false
            alert('Fout in backend')
          })
      },

      login () {
        this.validateForm()
        if (!this.valid) {
          alert('Zorg dat alle velden correct zijn ingevoerd')
          return
        }
        this.busy = true

        axios.get('/install_php/login.php', {
          auth: {
            username: this.helios.GebruikersNaam,
            password: this.helios.Wachtwoord,
          },
        }).then(response => {
          this.busy = false
          this.$store.commit('HeliosGebruikersNaam', this.helios.GebruikersNaam)
          this.$store.commit('HeliosWachtwoord', this.helios.Wachtwoord)
          this.$emit('isIngelogd', this.helios)
        })
          .catch(error => {
            this.busy = false
            switch (error.response.status) {
              case 401:
                alert('Niet geautoriseerd')
                break

              default: alert('Backend werkt niet. Controleer of de php functies werken')
            }
          })
      },
    },
  }
</script>
