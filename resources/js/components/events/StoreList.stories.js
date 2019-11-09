import { storiesOf } from '@storybook/vue'
import { action } from '@storybook/addon-actions'

// Components
import StoreList from './StoreList.vue'

let stores = [
  {
    uuid: 'a7936425-485a-4419-9acd-13cdccaed346',
    name: 'accusantium',
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
    assigned: false
  },
  {
    uuid: 'c48fb5d3-37e0-4cb5-bb44-d2d1b5fd97d5',
    name: 'saepe',
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
    assigned: true
  },
  {
    uuid: '790aba97-1eb6-4630-82d9-7bd561256c67',
    name: 'quibusdam',
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
    assigned: true
  }
]

storiesOf('FoodFleet|event/StoreList', module)
  .addParameters({
    backgrounds: [
      { name: 'default', value: '#f1f3f6', default: true }
    ]
  })
  .add('member is empty', () => ({
    components: { StoreList },
    data () {
      return {
        stores: [],
        pagination: {
          page: 5,
          rowsPerPage: 10,
          totalItems: 5
        },
        sorting: {
          descending: false,
          sortBy: ''
        }
      }
    },
    template: `
      <store-list
        :stores="stores"
        :rows-per-page="pagination.rowsPerPage"
        :page="pagination.page"
        :total-items="pagination.totalItems"
        :sort-by="sorting.sortBy"
        :descending="sorting.descending"
      />
    `
  }))
  .add('member is set', () => ({
    components: { StoreList },
    data () {
      return {
        stores: stores,
        pagination: {
          page: 5,
          rowsPerPage: 10,
          totalItems: 5
        },
        sorting: {
          descending: false,
          sortBy: ''
        }
      }
    },
    methods: {
      assign (params) {
        action('manage-assign')(params)
      },
      cancelAssign (params) {
        action('manage-cancel-assign')(params)
      },
      multipleAssign (params) {
        action('manage-multiple-assign')(params)
      }
    },
    template: `
      <store-list
        :stores="stores"
        :rows-per-page="pagination.rowsPerPage"
        :page="pagination.page"
        :total-items="pagination.totalItems"
        :sort-by="sorting.sortBy"
        :descending="sorting.descending"
        @manage-assign="assign"
        @manage-cancel-assign="cancelAssign"
        @manage-multiple-assign="multipleAssign"
      />
    `
  }))
