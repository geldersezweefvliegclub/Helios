<template>
  <v-container>
    <v-overlay :value="busy">
          <v-flex fill-height>
            <div class="text-center">
              <v-progress-circular indeterminate />
            </div>
          </v-flex>
    </v-overlay>

    <v-stepper v-model="step">
      <v-stepper-header>
        <v-stepper-step
            :complete="step > 0"
            step="1"
        >
          Database account
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            :complete="step > 1"
            step="2"
        >
          Installatie-account 
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            step="3"
            :complete="step > 2"
        >
          Database aanmaken
        </v-stepper-step>

        <v-divider></v-divider>

        <v-stepper-step
            step="4"
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
          <v-stepper-content step="1">
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

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" :disabled="!this.databaseVerbonden" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>    
          </v-stepper-content>

          <v-stepper-content step="2">
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

          <v-stepper-content step="3">
            <v-label v-if="databaseBestaat == true">Database bestaat, deze stap kan overgeslagen worden</v-label>
            <v-card class="mb-12" v-else>
              <v-card-text >
                <v-checkbox 
                    v-model="databaseAanmaken"
                    label="Database aanmaken" />    
              </v-card-text>
            </v-card>

            <v-card-actions>
              <v-btn text @click="vorigeStap()">Vorige</v-btn>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>   
          </v-stepper-content>

          <v-stepper-content step="4">
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
  import axios from 'axios'
  import zxcvbn from 'zxcvbn'
  import Password from 'vue-password-strength-meter'

  export default 
  {
    components: { Password },

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
          databaseHost: "",
          databaseNaam: "",
          databaseGebruiker: "",
          databaseWachtwoord: "",
          databaseWachtwoord2: "",
        },
        

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
      this.busy = true
      axios.get("/install_php/helios_info.php")
      .then(response => {
        if (response.data.initGedaan == true)
        {
          // De database gegevens zijn al geconfigureerd
          this.step = 2
          this.databaseVerbonden = true // anders kom je nooit naar step 2 (wanneer gebruiker terug naar step 1 gaat)
          this.databseBestaat = true
        }
        else
        {
          this.initGedaan = false;
        }
        this.heliosObjecten = response.data.Objecten
        this.busy = false

      }).catch(e => {
        console.log(e);
        alert('Backend werkt niet. Controleer of de php functies werken')
      });
    },
    beforeDestroy () {
     // clear the timeout before the component is destroyed
     clearTimeout(this.timeout)
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
        console.log(this.$refs);
        return true;
        //return this.$refs['databaseHost'].hasError;
      }
    },
      
    methods: 
    {
      validateForm() {
        this.$refs.form.validate();
      },
      volgendeStap() {
        (this.step + 1 <= 3) ? this.step++ : this.step = 4;
        console.log(this.step)
      },
      vorigeStap() {
        (this.step - 1 > 0) ? this.step-- : this.step = 0;
      },

      testVerbinding()
      {
        if (!this.$refs.databaseHost.valid)
        {
          alert("Database server niet ingevuld");
          return;
        }
        if (!this.$refs.databaseNaam.valid)
        {
          alert("Databasenaam niet ingevuld");
          return;
        }      
        if (!this.$refs.databaseGebruiker.valid)
        {
          alert("Gebruikersnaam niet ingevuld");
          return;
        }  
        if (!this.$refs.databaseWachtwoord.valid)
        {
          alert("Database wachtwoord niet juist");
          return;
        }  
        if (!this.$refs.databaseWachtwoord2.valid)
        {
          alert("Database wachtwoord niet juist");
          return;
        }        

        this.testDisabled = true

        // Re-enable after 10 seconds
        this.timeout = setTimeout(() => {
          this.testDisabled = false
        }, 10000)

        //axios.post("/install_php/test_db.php", JSON.stringify(this.databaseAccountGegevens))
        this.busy = true
        axios.post("/install_php/test_db.php", JSON.stringify(this.databaseAccountGegevens))
          .then(response => {
            this.busy = false
            if (response.data.dbError !== false)
            {
              alert("Geen database verbinding")
            }
            else
            {
              this.databaseVerbonden = true;
            }
        }).catch(e => {
          console.log(e)
            this.busy = false
            alert("Fout in backend")
        });
      }
    }
}
</script>
