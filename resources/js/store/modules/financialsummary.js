import makeRestStore from 'fresh-bus/store/utils/makeRestStore'

export default (initialState = {}) => {
  const store = makeRestStore(
    'financialsummary',
    { item: initialState.item, items: initialState.items },
    {
      itemsPath: () => `/foodfleet/financial-summary`,
      itemPath: ({ id }) => `/foodfleet/financial-summary/${id}`
    }
  )

  return {
    namespaced: true,
    ...store
  }
}
