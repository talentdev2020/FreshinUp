import { createLocalVue, shallowMount, mount } from '@vue/test-utils'
import Component from '~/components/events/StoreList.vue'
import { FIXTURE_STORES } from 'tests/__data__/stores'

describe('Store List component', () => {
  // Component instance "under test"
  let localVue
  describe('Snapshots', () => {
    test('stores set', () => {
      localVue = createLocalVue()
      const wrapper = mount(Component, {
        localVue: localVue,
        propsData: {
          stores: FIXTURE_STORES
        }
      })
      expect(wrapper.element).toMatchSnapshot()
    })
    test('stores empty', () => {
      localVue = createLocalVue()
      const wrapper = mount(Component, {
        localVue: localVue,
        propsData: {
          stores: []
        }
      })
      expect(wrapper.element).toMatchSnapshot()
    })
  })
  describe('Methods', () => {
    beforeEach(() => {
      localVue = createLocalVue()
    })

    test('assign function emitted single manage action', () => {
      const wrapper = shallowMount(Component, {
        localVue
      })

      const mockEvent = { id: 1, title: 'mock title', status: 1 }

      wrapper.vm.assign('assign', mockEvent)

      expect(wrapper.emitted()['manage-assign']).toBeTruthy()
    })

    test('assignMultiple function emitted multiple manage action', () => {
      const wrapper = shallowMount(Component, {
        localVue
      })

      wrapper.vm.assignMultiple('assign')

      expect(wrapper.emitted()['manage-multiple-assign']).toBeTruthy()
    })
  })

  describe('Computed', () => {
    beforeEach(() => {
      localVue = createLocalVue()
    })
    test('selectedActions', () => {
      const wrapper = shallowMount(Component)
      wrapper.setData({ selected: [] })
      expect(wrapper.vm.selectedActions).toEqual([])
      wrapper.setData({ selected: [ 1 ] })
      expect(wrapper.vm.selectedActions[0].action).toBe('assign')
    })
  })
})
