import { defineConfig } from "cypress"
import mysql from "mysql2/promise"
import dotenv from "dotenv"

dotenv.config()

function dbHost() {
  if (process.env.CYPRESS_DB_HOST) {
    return process.env.CYPRESS_DB_HOST
  }

  if (process.env.DB_HOST === "db") {
    return "127.0.0.1"
  }

  return process.env.DB_HOST || "127.0.0.1"
}

function dbPort() {
  if (process.env.CYPRESS_DB_PORT) {
    return Number(process.env.CYPRESS_DB_PORT)
  }

  if (process.env.DB_HOST === "db") {
    return 3307
  }

  return Number(process.env.DB_PORT || 3307)
}

function dbUser() {
  return process.env.CYPRESS_DB_USERNAME || process.env.DB_USERNAME || "root"
}

function dbPassword() {
  return process.env.CYPRESS_DB_PASSWORD || process.env.DB_PASSWORD || "root"
}

function dbName() {
  return process.env.CYPRESS_DB_DATABASE || process.env.DB_DATABASE || "FlashFood"
}

async function getConnection() {
  return await mysql.createConnection({
    host: dbHost(),
    port: dbPort(),
    user: dbUser(),
    password: dbPassword(),
    database: dbName(),
  })
}

function usuarioTable() {
  return process.env.DB_TABLE_USUARIO || "usuarios"
}

const ORDER_FLOW_CATEGORY = 'Categoria Cypress Delivery'
const ORDER_FLOW_CITY = 'Cidade Cypress Delivery'

async function ensureOrderFlowBaseRecords(conn) {
    const requiredRecords = [
        ['tipo_usuarios', 'id', 1, 'tipo de usuario Cliente'],
        ['status', 'id', 1, 'status Pendente'],
        ['status', 'id', 2, 'status Em preparo'],
        ['status', 'id', 3, 'status A caminho'],
        ['status', 'id', 4, 'status Entregue'],
        ['status', 'id', 5, 'status Cancelado'],
        ['tipo_pagamento', 'id', 3, 'forma de pagamento Pix'],
    ]

    for (const [table, column, value, label] of requiredRecords) {
        const [rows] = await conn.execute(
            `SELECT ${column} FROM ${table} WHERE ${column} = ? LIMIT 1`,
            [value]
        )

        if (!rows.length) {
            throw new Error(`Base de teste incompleta: ${label} nao encontrado.`)
        }
    }

    await conn.execute(
        `INSERT INTO categoria_produto (nome, deleted, created_at, updated_at)
         VALUES (?, 0, NOW(), NOW())
         ON DUPLICATE KEY UPDATE deleted = 0, updated_at = NOW()`,
        [ORDER_FLOW_CATEGORY]
    )

    await conn.execute(
        `INSERT INTO cidade (nome, created_at, updated_at)
         VALUES (?, NOW(), NOW())
         ON DUPLICATE KEY UPDATE updated_at = NOW()`,
        [ORDER_FLOW_CITY]
    )
}

export default defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || "http://localhost:8000",

    setupNodeEvents(on, config) {

        on('task', {

            async deleteUser(email) {
                const conn = await getConnection()

                await conn.execute(
                    `DELETE FROM ${usuarioTable()} WHERE email = ?`,
                    [email]
                )

                await conn.end()

                return null
            },

            async deleteProduct(nome) {
                const conn = await getConnection()

                await conn.execute(
                    `DELETE FROM produtos WHERE nome = ?`,
                    [nome]
                )

                await conn.end()

                return null
            },

            async deleteUserFuncionario(email) {

                const conn = await getConnection()

                await conn.execute(
                    `
                    DELETE f FROM funcionario f
                    INNER JOIN usuarios u ON u.id = f.usuario_id
                    WHERE u.email = ?
                    `,
                    [email]
                )

                await conn.execute(
                    `DELETE FROM ${usuarioTable()} WHERE email = ?`,
                    [email]
                )

                await conn.end()

                return null
            },

            async ensureOrderFlowBaseData() {
                const conn = await getConnection()

                await ensureOrderFlowBaseRecords(conn)

                await conn.end()

                return null
            },

            async cleanupPedidoFlow({
                email,
                produtoNome,
                produtonome,
                categoriaNome = ORDER_FLOW_CATEGORY,
                cidadeNome = ORDER_FLOW_CITY,
            }) {
                const conn = await getConnection()
                const usuarios = usuarioTable()
                const nomeProduto = produtoNome ?? produtonome

                if (email) {
                    await conn.execute(
                        `
                        DELETE s FROM sessions s
                        INNER JOIN ${usuarios} u ON u.id = s.user_id
                        WHERE u.email = ?
                        `,
                        [email]
                    )

                    await conn.execute(
                        `
                        DELETE ip FROM item_pedido ip
                        INNER JOIN pedidos p ON p.id = ip.pedido_id
                        INNER JOIN ${usuarios} u ON u.id = p.usuario_id
                        WHERE u.email = ?
                        `,
                        [email]
                    )

                    await conn.execute(
                        `
                        DELETE p FROM pedidos p
                        INNER JOIN ${usuarios} u ON u.id = p.usuario_id
                        WHERE u.email = ?
                        `,
                        [email]
                    )

                    await conn.execute(
                        `
                        DELETE c FROM carrinho c
                        INNER JOIN ${usuarios} u ON u.id = c.usuario_id
                        WHERE u.email = ?
                        `,
                        [email]
                    )

                    await conn.execute(
                        `
                        DELETE e FROM endereco e
                        INNER JOIN ${usuarios} u ON u.id = e.usuario_id
                        WHERE u.email = ?
                        `,
                        [email]
                    )

                    await conn.execute(
                        `
                        DELETE f FROM funcionario f
                        INNER JOIN ${usuarios} u ON u.id = f.usuario_id
                        WHERE u.email = ?
                        `,
                        [email]
                    )

                    await conn.execute(
                        `DELETE FROM ${usuarios} WHERE email = ?`,
                        [email]
                    )
                }

                if (nomeProduto) {
                    await conn.execute(
                        `
                        DELETE c FROM carrinho c
                        INNER JOIN produtos p ON p.id = c.produto_id
                        WHERE p.nome = ?
                        `,
                        [nomeProduto]
                    )

                    await conn.execute(
                        `
                        DELETE ip FROM item_pedido ip
                        INNER JOIN produtos p ON p.id = ip.produto_id
                        WHERE p.nome = ?
                        `,
                        [nomeProduto]
                    )

                    await conn.execute(
                        `DELETE FROM produtos WHERE nome = ?`,
                        [nomeProduto]
                    )
                }

                if (categoriaNome) {
                    await conn.execute(
                        `
                        DELETE cp FROM categoria_produto cp
                        LEFT JOIN produtos p ON p.categoria_id = cp.id
                        WHERE cp.nome = ? AND p.id IS NULL
                        `,
                        [categoriaNome]
                    )
                }

                if (cidadeNome) {
                    await conn.execute(
                        `
                        DELETE ci FROM cidade ci
                        LEFT JOIN endereco e ON e.cidade_id = ci.id
                        WHERE ci.nome = ? AND e.id IS NULL
                        `,
                        [cidadeNome]
                    )
                }

                await conn.end()

                return null
            },

            async createCheckoutProduct({
                nome,
                preco = '18.90',
                descricao = 'Produto criado para fluxo de compra automatizado',
                categoria = ORDER_FLOW_CATEGORY,
            }) {
                const conn = await getConnection()

                await ensureOrderFlowBaseRecords(conn)

                const [categorias] = await conn.execute(
                    `SELECT id FROM categoria_produto WHERE nome = ? LIMIT 1`,
                    [categoria]
                )

                const categoriaId = categorias[0].id
                const precoNormalizado = Number(preco).toFixed(2)

                const [existentes] = await conn.execute(
                    `SELECT id FROM produtos WHERE nome = ? ORDER BY id DESC LIMIT 1`,
                    [nome]
                )

                let produtoId

                if (existentes.length) {
                    produtoId = existentes[0].id

                    await conn.execute(
                        `
                        UPDATE produtos
                        SET categoria_id = ?, preco = ?, descricao = ?, imagem_url = 'sem_imagem.jpg',
                            disponivel = 1, deleted = 0, updated_at = NOW()
                        WHERE id = ?
                        `,
                        [categoriaId, precoNormalizado, descricao, produtoId]
                    )
                } else {
                    const [result] = await conn.execute(
                        `
                        INSERT INTO produtos
                            (nome, categoria_id, preco, descricao, imagem_url, disponivel, deleted, created_at, updated_at)
                        VALUES
                            (?, ?, ?, ?, 'sem_imagem.jpg', 1, 0, NOW(), NOW())
                        `,
                        [nome, categoriaId, precoNormalizado, descricao]
                    )

                    produtoId = result.insertId
                }

                await conn.end()

                return {
                    id: produtoId,
                    nome,
                    preco: Number(precoNormalizado),
                    categoria,
                }
            },

            async ensureCustomerAddress(email) {
                const conn = await getConnection()
                const usuarios = usuarioTable()

                await ensureOrderFlowBaseRecords(conn)

                const [usuariosEncontrados] = await conn.execute(
                    `SELECT id FROM ${usuarios} WHERE email = ? LIMIT 1`,
                    [email]
                )

                if (!usuariosEncontrados.length) {
                    await conn.end()
                    throw new Error(`Cliente de teste nao encontrado: ${email}`)
                }

                const usuarioId = usuariosEncontrados[0].id

                const [cidades] = await conn.execute(
                    `SELECT id FROM cidade WHERE nome = ? LIMIT 1`,
                    [ORDER_FLOW_CITY]
                )

                const cidadeId = cidades[0].id

                const [enderecos] = await conn.execute(
                    `
                    SELECT id FROM endereco
                    WHERE usuario_id = ? AND logradouro = 'Rua Cypress Delivery'
                    LIMIT 1
                    `,
                    [usuarioId]
                )

                let enderecoId

                if (enderecos.length) {
                    enderecoId = enderecos[0].id
                    await conn.execute(
                        `
                        UPDATE endereco
                        SET numero = '123', complemento = 'Casa de teste',
                            bairro = 'Centro', cidade_id = ?, updated_at = NOW()
                        WHERE id = ?
                        `,
                        [cidadeId, enderecoId]
                    )
                } else {
                    const [result] = await conn.execute(
                        `
                        INSERT INTO endereco
                            (logradouro, numero, complemento, bairro, cidade_id, usuario_id, created_at, updated_at)
                        VALUES
                            ('Rua Cypress Delivery', '123', 'Casa de teste', 'Centro', ?, ?, NOW(), NOW())
                        `,
                        [cidadeId, usuarioId]
                    )

                    enderecoId = result.insertId
                }

                await conn.end()

                return {
                    id: enderecoId,
                    usuario_id: usuarioId,
                    cidade_id: cidadeId,
                }
            },

            async latestPedidoByCliente(email) {
                const conn = await getConnection()
                const usuarios = usuarioTable()

                const [pedidos] = await conn.execute(
                    `
                    SELECT
                        p.id,
                        p.status,
                        p.valor_total,
                        p.motoboy_id,
                        p.endereco_id,
                        GROUP_CONCAT(pr.nome ORDER BY ip.id SEPARATOR ', ') AS produtos
                    FROM pedidos p
                    INNER JOIN ${usuarios} u ON u.id = p.usuario_id
                    LEFT JOIN item_pedido ip ON ip.pedido_id = p.id
                    LEFT JOIN produtos pr ON pr.id = ip.produto_id
                    WHERE u.email = ?
                    GROUP BY p.id, p.status, p.valor_total, p.motoboy_id, p.endereco_id
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT 1
                    `,
                    [email]
                )

                await conn.end()

                if (!pedidos.length) {
                    return null
                }

                const pedido = pedidos[0]

                return {
                    id: Number(pedido.id),
                    status: Number(pedido.status),
                    valor_total: Number(pedido.valor_total),
                    motoboy_id: pedido.motoboy_id ? Number(pedido.motoboy_id) : null,
                    endereco_id: pedido.endereco_id ? Number(pedido.endereco_id) : null,
                    produtos: pedido.produtos || '',
                }
            },
         })

      return config
    },
  },
})
