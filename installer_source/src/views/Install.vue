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


      <v-stepper-items>

          <v-stepper-content step="1">
            <db-info></db-info>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" :disabled="!this.databaseVerbonden" @click="volgendeStap()">Volgende</v-btn>
            </v-card-actions>    
          </v-stepper-content>

          <v-stepper-content step="2">
<!---- -->

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

    </v-stepper>
  </v-container>
</template>

<script>
  import axios from 'axios'
  import DbInfo from '@/components/DbInfo.vue';


  export default 
  {
    components: { Password, DbInfo },

    data() {
      return {
        databaseBestaat: true,        
        busy: false,
        step: 1,

        helios: {
          Wachtwoord: "",
          Wachtwoord2: "",
        },
      }
    },
    mounted() {
      this.busy = true
      axios.get("/install_php/helios_info.php")
      .then(response => {
        if (response.data.initGedaan == true)
        {
          // De database gegevens zijn al geconfigureerd
         // this.step = 2
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
      
    methods: 
    {
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