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
      v-if="dbBestaat == true"
      class="fill-height"
    >
      <div class="text-center">
        <v-chip
          color="green"
          text-color="white"
          style="border-radius: 4px"
          class="mt-2 ml-n3"
        >
          Database bestaat reeds, deze stap kan overgeslagen worden
        </v-chip>
      </div>
    </v-col>
    <v-form
      v-if="dbBestaat == false"
      ref="form"
      v-model="valid"
    >
      <v-card class="mb-12">
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
                @input="isAangepast = true"
              />
              <v-text-field
                ref="databaseNaam"
                v-model="databaseAccountGegevens.databaseNaam"
                type="text"
                label="Databasenaam"
                :rules="[rules.required]"
                @input="isAangepast = true"
              />
              <v-container v-if="isAangepast == false">
                <v-chip
                  v-if="databaseAccountGegevens.dbBestaat"
                  style="border-radius: 4px"
                  class="mt-2 ml-n3"
                >
                  Database bestaat reeds
                </v-chip>
                <v-chip
                  v-if="!databaseAccountGegevens.dbBestaat"
                  color="blue lighten-3"
                  text-color="white"
                  style="border-radius: 4px"
                  class="mt-2 ml-n3"
                >
                  Database bestaat niet, wordt aangemaakt
                </v-chip>
              </v-container>
            </v-col>
            <v-col cols="4">
              <v-text-field
                ref="databaseGebruiker"
                v-model="databaseAccountGegevens.databaseGebruiker"
                type="text"
                label="Gebruikersnaam"
                :rules="[rules.required]"
                @input="isAangepast = true"
              />
            </v-col>
            <v-col cols="4">
              <v-text-field
                ref="databaseWachtwoord"
                v-model="databaseAccountGegevens.databaseWachtwoord"
                prepend-icon="mdi-lock"
                label="Wachtwoord"
                :rules="[rules.required]"
                :type="verbergWachtwoord ? 'password' : 'text'"
                :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
                @input="isAangepast = true"
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
                @input="isAangepast = true"
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
            v-if="isAangepast == true"
            color="primary"
            @click="testVerbinding()"
          >
            Test
          </v-btn>
          <v-btn
            v-else
            color="primary"
            @click="uitvoeren()"
          >
            Uitvoeren
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
      dbBestaat: Boolean,
    },

    data () {
      return {
        verbergWachtwoord: true,
        isAangepast: true,

        busy: false,
        valid: false,

        databaseAccountGegevens: {
          databaseHost: '',
          databaseNaam: '',
          databaseGebruiker: '',
          databaseWachtwoord: '',
          databaseWachtwoord2: '',
          dbBestaat: false,
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

      testVerbinding () {
        this.validateForm()
        if (!this.valid) {
          alert('Zorg dat alle velden correct zijn ingevoerd')
          return
        }
        this.busy = true

        axios.post('/install_php/test_db.php', JSON.stringify(this.databaseAccountGegevens))
          .then(response => {
            this.busy = false
            if (response.data.dbError !== false) {
              alert('Geen database verbinding')
            } else {
              this.isAangepast = false
              this.databaseAccountGegevens.dbBestaat = response.data.dbBestaat
            }
          }).catch(e => {
            console.log(e)
            this.busy = false
            alert('Fout in backend')
          })
      },

      uitvoeren () {
        const r = confirm('Zeker weten? Deze actie kan maar 1 keer uitgevoerd worden')
        if (r === true) {
          this.create_db()
        }
      },

      async create_db () {
        this.validateForm()
        if (!this.valid) {
          alert('Zorg dat alle velden correct zijn ingevoerd')
          return
        }
        this.busy = true

        axios.post('/install_php/create_db.php', this.databaseAccountGegevens, {
          auth: {
            username: this.$store.state.heliosGebruikersNaam,
            password: this.$store.state.heliosWachtwoord,
          },
        })
          .then(response => {
            this.busy = false
            this.$emit('dbAangemaakt', 'done')
          }).catch(e => {
            console.log(e)
            this.busy = false
            alert('Fout in backend')
          })
      },
    },
  }
</script>
