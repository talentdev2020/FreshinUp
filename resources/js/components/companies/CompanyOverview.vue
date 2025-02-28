<template>
  <v-card>
    <v-card-title class="justify-space-between px-4 py-1">
      <span class="grey--text subheading font-weight-bold">Company overview</span>
      <v-btn
        v-if="!isEmptyCompany"
        depressed
        color="primary"
        @click="viewDetails"
      >
        View Details
      </v-btn>
    </v-card-title>
    <v-divider />
    <v-card-text>
      <v-progress-linear
        v-if="isLoading"
        indeterminate
      />
      <v-layout
        v-if="isEmptyCompany"
        class="pa-4"
        align-center
        justify-center
      >
        Empty company
      </v-layout>
      <v-layout
        v-else
        class="pa-4"
        align-center
        justify-center
      >
        <v-flex class="mr-4">
          <v-img
            :src="companyLogo"
            class="grey lighten-2"
          />
        </v-flex>
        <v-flex>
          <div class="primary--text subheading">
            {{ name }}
          </div>
          <div class="grey--text caption">
            Type: {{ typeName }}
          </div>
          <div class="grey--text caption">
            Status {{ statusName }}
          </div>
        </v-flex>
        <v-flex class="grey--text caption">
          Member {{ members_count }}
        </v-flex>
        <v-flex class="grey--text caption">
          {{ admin.level_name }}
        </v-flex>
        <v-flex>
          <v-layout align-center>
            <v-flex
              xs3
              mx-2
            >
              <f-user-avatar
                :tile="false"
                :user="admin"
                :size="80"
              />
            </v-flex>
            <v-flex>
              <div class="primary--text subheading">
                {{ admin.name }}
              </div>
              <div class="grey--text caption">
                {{ admin.email }}
              </div>
              <div class="grey--text caption">
                {{ admin.level_name }} @{{ name }}
              </div>
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-card-text>
  </v-card>
</template>
<script>
import FUserAvatar from '@freshinup/core-ui/src/components/FUserAvatar'
import get from 'lodash/get'

import MapValueKeysToData from '~/mixins/MapValueKeysToData'

export const DEFAULT_COMPANY = {
  type_id: 0,
  status: 0,
  name: '',
  logo: '',
  // members_count: 0, is included but we exclude it here to manually return it
  admin: {
    name: '',
    email: '',
    avatar: '',
    level_name: ''
  }
}

export const DEFAULT_IMAGE = 'https://via.placeholder.com/800x600.png'

export default {
  components: {
    FUserAvatar
  },
  mixins: [MapValueKeysToData],
  props: {
    // overriding value prop to define default value
    value: { type: Object, default: () => DEFAULT_COMPANY },
    isLoading: { type: Boolean, default: false },
    types: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] }
  },
  data () {
    return {
      ...DEFAULT_COMPANY
    }
  },
  computed: {
    members_count () {
      return get(this, 'members.length')
    },
    companyLogo () {
      return this.logo || DEFAULT_IMAGE
    },
    typesById () {
      return this.types.reduce((map, type) => {
        map[type.id] = type
        return map
      }, {})
    },
    typeName () {
      const type = this.typesById[this.type_id] || {}
      return type.name
    },
    statusesById () {
      return this.statuses.reduce((map, status) => {
        map[status.id] = status
        return map
      }, {})
    },
    statusName () {
      const status = this.statusesById[this.status] || {}
      return status.name
    },
    isEmptyCompany () {
      return !this.name
    }
  },
  methods: {
    viewDetails () {
      this.$emit('manage-view', this.payload)
      this.$emit('manage', 'view', this.payload)
    }
  }
}
</script>
