import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// Vite = the dev server + bundler.
// - react()       → JSX/TSX support + fast refresh (page updates without reload)
// - tailwindcss() → scans src/ for class names and generates only the CSS you use
export default defineConfig({
  plugins: [react(), tailwindcss()],
})
