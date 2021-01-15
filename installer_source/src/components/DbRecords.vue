<template>
  <v-container>
    <div class="text-center">
      <v-overlay :value="busy">
        <v-col class="fill-height">
          <div class="text-center">
            <v-progress-circular indeterminate />
          </div>
        </v-col>
      </v-overlay>

      <v-card class="mb-12">
        <v-card-text>
          <v-data-table
            :headers="headers"
            :items="dbRecords"
            :hide-default-footer="true"
            fixed-header
            disable-pagination
            :no-data-text="busy ? 'Data wordt opgehaald' : 'Geen informatie beschikbaar'"
          >
            <template
              #item="{ item, index }"
            >
              <tr
                :data-id="index"
              >
                <td
                  align="left"
                >
                  {{ item.class }}
                </td>
                <td
                  align="left"
                >
                  {{ item.totaal }}
                </td>
                <td
                  align="left"
                >
                  {{ item.laatste_aanpassing }}
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            v-if="(dbRecords.length > 0)"

            color="primary"
            @click="refresh_records()"
          >
            Verversen
          </v-btn>
        </v-card-actions>
      </v-card>
    </div>
  </v-container>
</template>

<script>
  export default {
    props: {
      dbRecords: Array,
    },

    data () {
      return {
        busy: false,
        isIngelogdTimer: null,

        headers: [
          { text: 'Naam', value: 'class' },
          { text: 'Record', value: 'records' },
          { text: 'Laatste_aanpassing', value: 'records' },
        ],
      }
    },

    methods: {
      async refresh_records () {
        this.$emit('refreshRecords', 'done')
      },
    },
  }

 </script>
