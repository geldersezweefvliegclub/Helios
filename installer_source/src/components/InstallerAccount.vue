<template>
  <v-form
    ref="form"
    v-model="valid"
  >
    <v-card class="mb-12">
      <v-card-text>
        <v-row class="d-flex justify-center">
          <v-col cols="6">
            <v-text-field
              value="helios"
              type="text"
              label="Gebruikersnaam"
              disabled
            />
          </v-col>
          <v-col cols="6">
            <v-text-field
              v-model="helios.Wachtwoord"
              prepend-icon="mdi-lock"
              label="Wachtwoord herhalen"
              :rules="[rules.required, rules.min, rules.sterk]"
              :type="verbergWachtwoord ? 'password' : 'text'"
              :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
              @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
            />
            <v-text-field
              v-model="helios.Wachtwoord2"
              prepend-icon="mdi-lock"
              label="Wachtwoord herhalen"
              :rules="[rules.required, rules.min, rules.sterk, rules.matchPassword]"
              :type="verbergWachtwoord ? 'password' : 'text'"
              :append-icon="verbergWachtwoord ? 'mdi-eye' : 'mdi-eye-off'"
              @click:append="() => (verbergWachtwoord = !verbergWachtwoord)"
            />
            <password
              v-model="helios.Wachtwoord"
              :strength-meter-only="true"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-form>
</template>
