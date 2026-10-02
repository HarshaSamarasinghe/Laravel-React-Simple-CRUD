import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    host: true, // allows external access (important for WSL/Docker)
    watch: {
      usePolling: true, // ensures file changes are detected
      interval: 100,    // check every 100ms
    },
  },
})
