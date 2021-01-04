<template>
    <v-form
        ref="form"
        v-model="valid">
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
    </v-form>
</template>
