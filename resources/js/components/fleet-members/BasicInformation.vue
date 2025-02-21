<template>
  <v-card>
    <v-card-title>
      <h3 class="grey--text">
        Basic Information
      </h3>
      <v-progress-linear
        v-if="loading"
        indeterminate
      />
    </v-card-title>
    <v-divider />
    <v-layout
      row
      wrap
      class="pa-3"
    >
      <v-flex
        xs12
        lg8
        pr-3
      >
        <v-layout
          row
          wrap
        >
          <v-flex
            xs12
            md9
            pr-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Fleet member name
            </div>
            <v-text-field
              v-model="name"
              v-validate="'required'"
              :error-messages="errors.collect('name')"
              data-vv-name="name"
              placeholder="Name"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md3
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Type
            </div>
            <v-select
              v-model="type_id"
              :items="types"
              item-text="name"
              item-value="id"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md8
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Tags
            </div>
            <f-autocomplete
              ref="tags"
              no-filter
              placeholder="Type a tag"
              value-fetch
              item-value="uuid"
              item-text="name"
              term-param="filter[name]"
              url="/foodfleet/store-tags"
              hide-details
              class="mb-4"
              solo
              outline
              flat
              not-clearable
              @input="onTagSelected"
            />
          </v-flex>
          <v-flex
            xs12
            md4
            pl-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Size of the truck / trailer
            </div>
            <v-text-field
              v-model="size"
              placeholder="input value"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            v-if="tags.length"
            xs12
            pb-2
          >
            <v-chip
              v-for="(tag, index) of tags"
              :key="index"
              close
              color="orange"
              @click="deleteTag(tag)"
            >
              {{ tag.name }}
            </v-chip>
          </v-flex>
          <v-layout
            row
            wrap
            class="mb-2"
          >
            <v-flex
              xs12
              md4
              pr-2
            >
              <div class="text-uppercase grey--text font-weight-bold">
                Square account
                <v-tooltip top>
                  <template v-slot:activator="{ on }">
                    <v-btn
                      small
                      icon
                      v-on="on"
                    >
                      <v-icon
                        color="grey"
                        small
                      >
                        info
                      </v-icon>
                    </v-btn>
                  </template>
                  You will be prompted to enter <br> your Square Credentials and share <br> your transactional data with Food Fleet
                </v-tooltip>
              </div>
              <v-btn
                v-if="!hasSquareAccount"
                depressed
                color="primary"
                @click="connectSquare"
              >
                Connect
              </v-btn>
              <v-btn
                v-else
                depressed
                color="error"
                :disabled="loading"
                @click="disconnectSquare"
              >
                Disconnect
              </v-btn>
            </v-flex>
            <v-flex
              xs12
              md4
              pr-2
              pl-2
            >
              <div class="ff-fleet-members__basic_information mb-2 text-uppercase grey--text font-weight-bold d-flex justify-space-between position-relative pt-2">
                Square business name
                <v-tooltip top>
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon
                      color="grey"
                      class="ff-fleet-members__tooltip-icon"
                      small
                      v-bind="attrs"
                      v-on="on"
                    >
                      info
                    </v-icon>
                  </template>
                  This is the business name you entered for your fleet member on square
                </v-tooltip>
              </div>
              <div v-if="!hasSquareAccount">
                Please connect to Square
              </div>
              <v-select
                v-if="hasSquareAccount"
                v-model="square_id"
                :items="squareLocations"
                single-line
                outline
                item-text="name"
                item-value="id"
              />
            </v-flex>
            <v-flex
              xs12
              md4
              pl-2
            >
              <v-layout
                column
                class="justify-content-between"
              >
                <div class="ff-fleet-members__basic_information mb-2 text-uppercase grey--text font-weight-bold d-flex justify-space-between position-relative pt-2">
                  Square location ID
                </div>
                <div v-if="!hasSquareAccount">
                  Please connect to Square
                </div>
                <v-text-field
                  v-else
                  :value="get(squareAccount, 'id')"
                  disabled
                  placeholder="Short description"
                  single-line
                  outline
                />
              </v-layout>
            </v-flex>
          </v-layout>
          <v-flex
            xs12
            class="mb-2"
          >
            <div class="text-uppercase grey--text font-weight-bold">
              Owned by
            </div>
            <simple
              label="Name"
              single-line
              outline
              url="users?filter[type]=1"
              term-param="term"
              results-id-key="uuid"
              :value="owner_uuid"
              placeholder="Name"
              height="48"
              not-clearable
              flat
              @input="onOwnerSelected"
            />
          </v-flex>
          <v-flex
            xs12
            md6
            pr-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Contact phone
            </div>
            <v-text-field
              v-model="contact_phone"
              placeholder="Phone"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md6
            pl-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              State of incorporation
            </div>
            <v-text-field
              v-model="state_of_incorporation"
              placeholder="State"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md6
            pr-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Website
            </div>
            <v-text-field
              v-model="website"
              placeholder="www.example.com"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md6
            pl-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Facebook
            </div>
            <v-text-field
              v-model="facebook"
              placeholder="facebook account"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md6
            pr-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Twitter
            </div>
            <v-text-field
              v-model="twitter"
              placeholder="Twitter account"
              single-line
              outline
            />
          </v-flex>
          <v-flex
            xs12
            md6
            pl-2
          >
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Instagram
            </div>
            <v-text-field
              v-model="instagram"
              placeholder="Instagram account"
              single-line
              outline
            />
          </v-flex>
          <v-flex xs12>
            <div class="mb-2 text-uppercase grey--text font-weight-bold">
              Food fleet staff notes
            </div>
            <v-textarea
              v-model="staff_notes"
              placeholder="Food fleet staff notes"
              single-line
              outline
            />
          </v-flex>
        </v-layout>
      </v-flex>
      <v-flex
        xs12
        lg4
        pl-3
      >
        <div class="px-2 xs-12 md-4">
          <div class="mb-2 text-uppercase grey--text font-weight-bold">
            Truck / Trailer image
          </div>
          <input
            class="ff-fleet-members__image_input"
            type="file"
            @change="storeImageChange"
          >
          <v-img
            :src="storeImage"
            class="grey lighten-2"
          />
        </div>
        <div class="py-3 d-flex justify-space-between">
          <v-btn
            depressed
            color="primary"
            @click="updateStoreImage"
          >
            Upload Image
          </v-btn>
          <v-btn
            depressed
            :disabled="!hasImage"
            @click="deleteStoreImage"
          >
            Delete Image
          </v-btn>
        </div>
      </v-flex>
    </v-layout>
    <v-divider />
    <v-card-actions class="pa-3 d-flex justify-space-between">
      <div>
        <v-btn
          depressed
          @click="onCancel"
        >
          Cancel
        </v-btn>
        <v-btn
          :loading="loading"
          depressed
          color="primary"
          @click="whenValid(save)"
        >
          {{ editing ? 'Save changes' : 'Submit' }}
        </v-btn>
      </div>
      <div
        v-if="editing"
        style="text-align: right;"
      >
        <v-btn
          depressed
          :loading="loading"
          @click="onDeleteMember"
        >
          Delete Fleet Member
        </v-btn>
      </div>
    </v-card-actions>
  </v-card>
</template>
<script>
import FAutocomplete from '../../components/FAutocomplete'
import Simple from 'fresh-bus/components/search/simple'
import pick from 'lodash/pick'
import keys from 'lodash/keys'
import get from 'lodash/get'
import MapValueKeysToData from '../../mixins/MapValueKeysToData'
import Validate from 'fresh-bus/components/mixins/Validate'

export const DEFAULT_STORE = {
  name: '',
  type_id: '',
  tags: [],
  square_id: '',
  size: '',
  owner_uuid: '',
  contact_phone: '',
  state_of_incorporation: '',
  website: '',
  twitter: '',
  facebook: '',
  instagram: '',
  staff_notes: '',
  image: null
}

export const DEFAULT_IMAGE = 'https://via.placeholder.com/800x600.png'

export default {
  components: { FAutocomplete, Simple },
  mixins: [MapValueKeysToData, Validate],
  props: {
    loading: { type: Boolean, default: false },
    types: { type: Array, default: () => [] },
    squareLocations: { type: Array, default: () => [] },
    // Overriding value prop from mixin MapValueKeysToData to grab the default values
    value: { type: Object, default: () => DEFAULT_STORE }
  },
  data () {
    return {
      ...DEFAULT_STORE
    }
  },
  computed: {
    editing () {
      return !!get(this.value, 'uuid')
    },
    hasImage () {
      return !!this.image && this.image !== DEFAULT_IMAGE
    },
    storeImage () {
      return this.hasImage ? this.image : '/images/default.png'
    },
    squareAccountsById () {
      return this.squareLocations.reduce((map, location) => {
        map[location.id] = location
        return map
      }, {})
    },
    squareAccount () {
      return this.squareAccountsById[this.square_id]
    },
    hasSquareAccount () {
      return this.squareLocations.length > 0
    }
  },
  methods: {
    get,
    connectSquare () {
      this.$emit('connect-square')
    },
    disconnectSquare () {
      this.$emit('disconnect-square')
    },
    storeImageChange (e) {
      const file = e.target.files[0]
      if (!file) {
        return false
      }
      const reader = new FileReader()
      reader.onload = (event) => {
        this.image = event.target.result
      }
      reader.readAsDataURL(file)
    },
    updateStoreImage () {
      const image = this.$el.querySelector('.ff-fleet-members__image_input')
      if (!image) {
        return false
      }
      image.click()
    },
    deleteStoreImage () {
      const image = this.$el.querySelector('.ff-fleet-members__image_input')
      if (!image) {
        return false
      }
      image.value = null
      this.image = null
    },
    deleteTag (tag) {
      this.tags = this.tags.filter(t => t.uuid !== tag.uuid)
    },
    onOwnerSelected (owner) {
      this.owner_uuid = owner ? owner.uuid : null
    },
    onTagSelected (tag) {
      // on add if not already
      if (this.tags.findIndex(t => t.uuid === tag.uuid) === -1) {
        this.tags.push(tag)
      }
      this.$refs.tags.clear()
    },
    onCancel () {
      this.$emit('cancel')
    },
    onDeleteMember () {
      this.$emit('delete', pick(this, keys(this.value)))
    }
  }
}
</script>
<style lang="scss" scoped>
  .ff-fleet-members__basic_information {
    position: relative;

    .ff-fleet-members__tooltip-icon {
      position: absolute;
      right: 0;
    }
  }
  .ff-fleet-members__image_input {
    display: none;
  }
</style>
