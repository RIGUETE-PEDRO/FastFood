describe('RF03 - proteção de páginas por autenticação e perfil', () => {
  const paginasProtegidas = [
    '/perfil',
    '/pedidos',
    '/carrinho',
    '/mesas',
    '/mesas/historico',
    '/entregas',
    '/gerenciamento_Produtos',
    '/Pedidos_Administrativo',
  ]

  beforeEach(() => {
    cy.clearCookies()
    cy.clearLocalStorage()
  })

  paginasProtegidas.forEach((caminho) => {
    it(`RF03 protege ${caminho} quando não há sessão`, () => {
      cy.visit(caminho)

      cy.location('pathname', { timeout: 10000 }).should('eq', '/login')
      cy.get('h1').should('have.text', 'Login')
    })
  })
})
