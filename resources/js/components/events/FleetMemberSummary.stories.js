import { storiesOf } from '@storybook/vue'
import { action } from '@storybook/addon-actions'
import FleetSummary from './FleetMemberSummary'

storiesOf('FoodFleet|events/FleetMemberSummary', module)
  .add(
    'member summary required',
    () => ({
      components: { FleetSummary },
      methods: {
        onButtonClick () {
          action('onButtonClick')('button clicked')
        },
        remove () {
          action('remove')('remove clicked')
        }
      },
      data () {
        return {
          member: {
            owner: 'Dan Smith',
            lisence_due: 'Dec, 30 2020',
            phone: '938 374822',
            email: 'dan.simth@gmail.com',
            tags: ['SEAFOOD', 'SMOKED', 'DESSERT', 'BAY AREA', 'VEGAN OPTIONS', 'SMOKED', 'DESSERT', 'SEAFOOD', 'SMOKED']
          }
        }
      },
      template: `        
    <v-container fluid>
      <v-layout row>
        <fleet-summary
         @onButtonClick="onButtonClick"
         @remove="remove"
         :member="member"
        />
        </v-layout>
    </v-container>
  `
    })
  )
