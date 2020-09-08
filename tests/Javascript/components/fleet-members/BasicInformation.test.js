import { createLocalVue, mount, shallowMount } from '@vue/test-utils'
import * as Stories from '~/components/fleet-members/BasicInformation.stories'
import Component from '~/components/fleet-members/BasicInformation.vue'
import { FIXTURE_STORE } from '../../__data__/stores'

describe('fleet-members/BasicInformation', () => {
  describe('Snapshots', () => {
    test('Default', async () => {
      const wrapper = mount(Stories.Default())
      await wrapper.vm.$nextTick()
      expect(wrapper.element).toMatchSnapshot()
    })
    test('WithData', async () => {
      const wrapper = mount(Stories.WithData())
      await wrapper.vm.$nextTick()
      expect(wrapper.element).toMatchSnapshot()
    })
    test('Loading', async () => {
      const wrapper = mount(Stories.Loading())
      await wrapper.vm.$nextTick()
      expect(wrapper.element).toMatchSnapshot()
    })
  })

  describe('Props & Computed', () => {
    test('loading', async () => {
      const wrapper = shallowMount(Component)
      expect(wrapper.vm.loading).toBe(false)

      wrapper.setProps({
        loading: true
      })
      await wrapper.vm.$nextTick()
      expect(wrapper.vm.loading).toBe(true)
    })
  })

  describe('methods', () => {
    let localVue

    beforeEach(() => {
      localVue = createLocalVue()
    })

    test('On save changes', () => {
      const wrapper = shallowMount(Component, {
        localVue: localVue
      })

      wrapper.setData({
        storeData: FIXTURE_STORE
      })

      wrapper.vm.onSaveChanges()
      expect(wrapper.emitted().save).toBeTruthy()
      const saveData = wrapper.emitted().save[0][0]
      expect(saveData).toEqual(FIXTURE_STORE)
    })

    test('On cancel', () => {
      const wrapper = shallowMount(Component, {
        localVue: localVue
      })

      wrapper.vm.onCancel()
      expect(wrapper.emitted().cancel).toBeTruthy()
    })

    test('On delete member', () => {
      const wrapper = shallowMount(Component, {
        localVue: localVue
      })

      wrapper.setData({
        storeData: FIXTURE_STORE
      })

      wrapper.vm.onDeleteMember()
      expect(wrapper.emitted().delete).toBeTruthy()
      const deleteData = wrapper.emitted().delete[0][0]
      expect(deleteData).toEqual(FIXTURE_STORE)
    })
  })
})
