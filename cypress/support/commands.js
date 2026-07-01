function submitLogin(email, senha) {
  expect(email, 'email de login').to.be.a('string').and.not.be.empty
  expect(senha, 'senha de login').to.be.a('string').and.not.be.empty

  cy.request('/login').then((resp) => {
    const match = resp.body.match(/name="_token" value="([^"]+)"/)
    const token = match ? match[1] : null

    expect(token, 'csrf token').to.not.be.null

    cy.request({
      method: 'POST',
      url: '/login',
      form: true,
      body: {
        _token: token,
        email,
        senha,
      },
      failOnStatusCode: false,
    }).then((loginResp) => {
      expect([200, 302]).to.include(loginResp.status)
    })
  })
}

Cypress.Commands.add('loginAdmin', () => {
  submitLogin(
    Cypress.env('USUARIO_ADMINISTRADOR'),
    Cypress.env('SENHA_ADMINISTRADOR')
  )
})

Cypress.Commands.add('loginAs', (email, senha) => {
  submitLogin(email, senha)
})

Cypress.Commands.add('produtoRow', (nome) => {
  return cy.contains('#tableBody .nome-cell', nome).parents('tr')
})

Cypress.Commands.add('funcionarioRow', (nome) => {
  return cy.contains('#tableBody .nome-cell', nome).parents('tr')
})

Cypress.Commands.add('deleteProducts', (nomes) => {
  nomes.forEach((nome) => cy.task('deleteProduct', nome))
})

Cypress.Commands.add('deleteFuncionarios', (emails) => {
  emails.forEach((email) => cy.task('deleteUserFuncionario', email))
})

Cypress.Commands.add('criarProdutoUi', ({
  nome,
  preco = '5.00',
  descricao = 'Produto criado por teste automatizado',
  categoria = 'Bebidas',
  ativo = true,
}) => {
  cy.visit('/gerenciamento_Produtos')
  cy.get('#openCreateProduct').click()
  cy.get('#createProductOverlay').should('have.class', 'is-open')

  cy.get('#produto-nome').clear().type(nome)
  cy.get('#produto-preco').clear().type(preco)
  cy.get('#produto-descricao').clear().type(descricao)
  cy.get('#produto-categoria').select(categoria)
  cy.get(ativo ? '#btnAtivo' : '#btnInativo').click()

  cy.get('#createProductOverlay').within(() => {
    cy.contains('button[type="submit"]', 'Salvar').click()
  })

  cy.produtoRow(nome).should('be.visible')
})

Cypress.Commands.add('criarFuncionarioUi', ({
  nome,
  email,
  senha,
  telefone = '123456789',
  tipo = 'Entregador',
  salario = '2000',
  ativo = true,
}) => {
  cy.visit('/gerenciamento_Funcionario')
  cy.get('#openCreateUser').click()
  cy.get('#createUserOverlay').should('have.class', 'is-open')

  cy.get('#nome').clear().type(nome)
  cy.get('#email').clear().type(email)
  cy.get('#telefone').clear().type(telefone)
  cy.get('#tipo_usuario_id').select(tipo)
  cy.get(ativo ? '#btnAtivoFuncionario' : '#btnInativoFuncionario').click()
  cy.get('#salario').clear().type(salario)
  cy.get('#senha').clear().type(senha)
  cy.get('#senha_confirmation').clear().type(senha)

  cy.get('#createUserOverlay').within(() => {
    cy.contains('button[type="submit"]', 'Salvar').click()
  })

  cy.funcionarioRow(nome).should('be.visible')
})
