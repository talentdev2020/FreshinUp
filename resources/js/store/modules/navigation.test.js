import module from './navigation'

describe('store/modules/navigation', () => {
  test('the state has items', () => {
    const item = {}
    const items = []
    const result = module({ items, item })
    expect(result).toHaveProperty('state')
    expect(result.state).toHaveProperty('items', items)
  })

  test('the state has item', () => {
    const item = {}
    const items = []
    const result = module({ items, item })
    expect(result).toHaveProperty('state')
    expect(result.state).toHaveProperty('item', item)
  })

  test('is a namespaced module', () => {
    let item = {}
    let items = []
    const result = module({ items, item })
    expect(result).toHaveProperty('namespaced', true)
  })

  // TODO: Future work. Ignore for now
  describe('Mutations', () => {})
  describe('Actions', () => {})
})
