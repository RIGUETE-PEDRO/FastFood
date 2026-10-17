describe('RF02 - encerramento de sessão', () => {
  const cliente = {
    nome: 'Cliente Cypress Logout',
    email: 'rf02.logout@flashfood.test',
    senha: 'TesteLogout123',
    telefone: '11999999998',
  }

  const limparCliente = () => cy.task('cleanupPedidoFlow', {
    email: cliente.email,
    produtoNome: null,
    categoriaNome: null,
    cidadeNome: null,
  })

  before(() => limparCliente())
  after(() => limparCliente())

  it('RF02 encerra a sessão e bloqueia páginas autenticadas', () => {
    cy.clearCookies()
    cy.clearLocalStorage()
    cy.registrarCliente(cliente)
    cy.loginAs(cliente.email, cliente.senha)
    cy.visit('/')

    cy.get('.ff-sidebar__user-btn').click()
    cy.get('.dropdown-menu.show').contains('a', 'Sair').click()
    cy.location('pathname', { timeout: 10000 }).should('eq', '/login')

    cy.visit('/pedidos')
    cy.location('pathname', { timeout: 10000 }).should('eq', '/login')
  })
})
