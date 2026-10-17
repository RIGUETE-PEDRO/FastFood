describe('validacao dos formularios de autenticacao', () => {
  it('exige e-mail e senha no formulário de login', () => {
    cy.visit('/login')

    cy.get('#email')
      .should('have.attr', 'type', 'email')
      .and('have.attr', 'required')
    cy.get('#senha')
      .should('have.attr', 'type', 'password')
      .and('have.attr', 'required')
    cy.get('#entrar').should('be.enabled')
  })

  it('exige todos os dados e a confirmação de senha no cadastro', () => {
    cy.visit('/registro')

    cy.get('#name').should('have.attr', 'required')
    cy.get('#phone').should('have.attr', 'required')
    cy.get('#email')
      .should('have.attr', 'type', 'email')
      .and('have.attr', 'required')
    cy.get('input[name="senha"]').should('have.attr', 'required')
    cy.get('#password_confirmation').should('have.attr', 'required')
  })

  it('permite voltar da recuperação de senha para o login', () => {
    cy.visit('/esqueci-senha')

    cy.get('h1').should('have.text', 'Recuperar Senha')
    cy.get('#email')
      .should('have.attr', 'type', 'email')
      .and('have.attr', 'required')
    cy.contains('a', 'Voltar para Login').click()

    cy.location('pathname').should('eq', '/login')
    cy.get('h1').should('have.text', 'Login')
  })

  it('exige nova senha e confirmação na redefinição', () => {
    cy.visit('/redefinir-senha?token=token-de-teste&email=cliente%40flashfood.test')

    cy.get('#password')
      .should('have.attr', 'required')
    cy.get('#password_confirmation')
      .should('have.attr', 'required')
  })
})
