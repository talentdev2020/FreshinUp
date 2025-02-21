import { storiesOf } from '@storybook/vue'
import { action } from '@storybook/addon-actions'

// Components
import EventCalendar from './EventCalendar.vue'
import { FIXTURE_EVENT_STATUSES } from '../../../../tests/Javascript/__data__/eventStatuses'

const events = [
  {
    uuid: 'a7936425-485a-4419-9acd-13cdccaed346',
    name: 'accusantium',
    status_id: 1,
    start_at: '2019-12-10 11:04:19',
    end_at: '2019-12-10 11:04:19',
    manager: {
      id: 1,
      uuid: 'c6be43eb-461f-4654-82b5-7dd6a6f11e54',
      name: 'Demo Admin'
    },
    venue: {
      uuid: '4b2e762d-ec19-44ef-a1ad-78e7c45dec00',
      name: 'New Hattie'
    },
    event_tags: [
      {
        uuid: '1',
        name: 'minus'
      },
      {
        uuid: '2',
        name: 'hic'
      }
    ],
    host: {
      id: 89,
      uuid: '28138d6d-9605-42e8-9ceb-f2616a514ee5',
      name: 'Swift-Wehner'
    }
  },
  {
    uuid: 'c48fb5d3-37e0-4cb5-bb44-d2d1b5fd97d5',
    name: 'saepe',
    status_id: 2,
    start_at: '2019-12-10 11:04:19',
    end_at: '2019-12-12 11:04:19',
    manager: {
      id: 3,
      uuid: '2ccdd232-c73a-4398-a2dc-342de7d43bf1',
      name: 'Level 2 User'
    },
    venue: {
      uuid: '4d6ace0e-5f3f-423a-ab47-648a142ba450',
      name: 'Baronhaven'
    },
    event_tags: [
      {
        uuid: '1',
        name: 'minus'
      },
      {
        uuid: '2',
        name: 'sit'
      },
      {
        uuid: '3',
        name: 'hicsdfsdf'
      }
    ],
    host: {
      id: 96,
      uuid: '5d3e79a3-81aa-4645-b1fd-c6173026e01f',
      name: 'Goodwin-Carroll'
    }
  },
  {
    uuid: '790aba97-1eb6-4630-82d9-7bd561256c67',
    name: 'quibusdam',
    status_id: 3,
    start_at: '2019-12-05 11:04:19',
    end_at: '2019-12-06 11:04:19',
    manager: {
      id: 2,
      uuid: '16527881-c80f-42d8-850f-594b6d5ec4a0',
      name: 'Level 1 User'
    },
    venue: {
      uuid: 'cfc8c89e-000b-4adb-8f1a-9cec5aecc6ef',
      name: 'Lake Lavernehaven'
    },
    event_tags: [
      {
        uuid: '1',
        name: 'minus'
      },
      {
        uuid: '2',
        name: 'hic'
      },
      {
        uuid: '3',
        name: 'acsfdd'
      },
      {
        uuid: '4',
        name: 'fsdf'
      }
    ],
    host: {
      id: 94,
      uuid: '77f3a8af-450f-4505-889f-a705cf720b3a',
      name: 'Carter-Green'
    }
  },
  {
    uuid: '395aga97-13b6-4430-86d2-7td46155dc67',
    name: 'quibusa',
    status_id: 4,
    start_at: '2019-12-25 11:04:19',
    end_at: '2019-12-25 11:05:19',
    manager: {
      id: 2,
      uuid: '16527881-c80f-42d8-850f-594b6d5ec4a0',
      name: 'Level 1 User'
    },
    venue: {
      uuid: 'cfc8c89e-000b-4adb-8f1a-9cec5aecc6ef',
      name: 'Lake Lavernehaven'
    },
    event_tags: [
      {
        uuid: '1',
        name: 'minus'
      },
      {
        uuid: '2',
        name: 'hic'
      },
      {
        uuid: '3',
        name: 'acsfdd'
      },
      {
        uuid: '4',
        name: 'fsdf'
      }
    ],
    host: {
      id: 94,
      uuid: '77f3a8af-450f-4505-889f-a705cf720b3a',
      name: 'Carter-Green'
    }
  }
]

storiesOf('FoodFleet|components/event/EventCalendar', module)
  .addParameters({
    backgrounds: [
      { name: 'default', value: '#f1f3f6', default: true }
    ]
  })
  .add('without events', () => ({
    components: { EventCalendar },
    data () {
      return {
        events: [],
        type: 'month',
        yearRange: [2010, 2020],
        date: '2019-12-31',
        statuses: FIXTURE_EVENT_STATUSES
      }
    },
    template: `
      <event-calendar
        :events="events"
        :statuses="statuses"
        :type="type"
        :yearRange="yearRange"
        :date="date"
      />
    `
  }))
  .add('with events', () => ({
    components: { EventCalendar },
    data () {
      return {
        events: events,
        type: 'month',
        yearRange: [2010, 2020],
        date: '2019-12-31',
        statuses: FIXTURE_EVENT_STATUSES
      }
    },
    methods: {
      clickEvent (params) {
        action('Click Event')(params)
      },
      changeDate (params) {
        action('Change Date')(params)
      }
    },
    template: `
      <event-calendar
        :events="events"
        :statuses="statuses"
        :type="type"
        :yearRange="yearRange"
        :date="date"
        @click-event="clickEvent"
        @change-date="changeDate"
      />
    `
  }))
