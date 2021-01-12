import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
 state: {
     heliosWachtwoord: null,
     heliosGebruikersNaam: null,
     heliosInfo: null,
     dbTables: null,
 },

 getters: {},

 mutations: {
    HeliosWachtwoord (state, payload) {
        state.heliosWachtwoord = payload
    },
    HeliosGebruikersNaam (state, payload) {
        state.heliosGebruikersNaam = payload
    },
    HeliosInfo (state, payload) {
        state.heliosInfo = payload
    },
    DbTables (state, payload) {
        state.dbTables = payload
    },
 },

 actions: {},
})
