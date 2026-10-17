describe('RF03 - autorização por perfil', () => {
  const cliente = {
    nome: 'Cliente Cypress Perfil',
    email: 'rf03.perfil@flashfood.test',
    senha: 'TestePerfil123',
    telefone: '11999999997',
  }

  const limparCliente = () => cy.task('cleanupPedidoFlow', {
    email: cliente.email,
    produtoNome: null,
    categoriaNome: null,
    cidadeNome: null,
  })

  before(() => limparCliente())
  after(() => limparCliente())

  it('RF03 impede cliente de abrir o gerenciamento de produtos', () => {
    cy.clearCookies()
    cy.clearLocalStorage()
    cy.registrarCliente(cliente)
    cy.loginAs(cliente.email, cliente.senha)
    cy.visit('/gerenciamento_Produtos')

    cy.location('pathname', { timeout: 10000 }).should('eq', '/AcessoNegado')
    cy.get('h1').should('contain.text', 'Área restrita')
  })

  it('RF03 permite que administrador autorizado abra o gerenciamento', () => {
    cy.loginCypressAdmin()
    cy.visit('/gerenciamento_Produtos')

    cy.location('pathname').should('eq', '/gerenciamento_Produtos')
    cy.get('#openCreateProduct').should('be.visible')
  })
})
