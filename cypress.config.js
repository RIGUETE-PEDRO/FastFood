import { defineConfig } from "cypress"
import mysql from "mysql2/promise"
import dotenv from "dotenv"

dotenv.config()

export default defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || "http://localhost:8000",
    setupNodeEvents(on, config) {

      on('task', {
        async deleteUser(email) {
          const conn = await mysql.createConnection({
            host: process.env.DB_HOST,
            port: process.env.DB_PORT,
            user: process.env.DB_USERNAME,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_DATABASE
          })

          await conn.execute(
            `DELETE FROM ${process.env.DB_TABLE_USUARIO} WHERE email = ?`,
            [email]
          )

          await conn.end()
          return null
        }
      })

      return config
    },
  },
})
