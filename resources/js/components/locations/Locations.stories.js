import { storiesOf } from '@storybook/vue'
import { action } from '@storybook/addon-actions'

import Locations from './Locations'
import { FIXTURE_LOCATIONS } from '../../../../tests/Javascript/__data__/locations'

export const Empty = () => ({
  components: { Locations },
  data () {
    return {
      items: []
    }
  },
  template: `
    <locations
      :items="items"
    />
  `
})

export const Loading = () => ({
  components: { Locations },
  template: `
    <locations
      is-loading
    />
  `
})

export const Populated = () => ({
  components: { Locations },
  data () {
    return {
      items: FIXTURE_LOCATIONS
    }
  },
  methods: {
    onManage (act, item) {
      action('onManage')(act, item)
    },
    onManageMultiple (act, items) {
      action('onManageMultiple')(act, items)
    }
  },
  template: `
    <locations
      :items="items"
      @manage="onManage"
      @manage-multiple="onManageMultiple"
    />
  `
})

storiesOf('FoodFleet|components/locations/Locations', module)
  .addParameters({
    backgrounds: [
      { name: 'default', value: '#f1f3f6', default: true }
    ]
  })
  .add('Empty', Empty)
  .add('Loading', Loading)
  .add('Populated', Populated)
