import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      // Esto fuerza a Vite a usar siempre la misma copia de React
      'react': path.resolve(__dirname, 'node_modules/react'),
    },
  },
})