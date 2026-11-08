import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// Vite = the dev server + bundler.
// - react()       → JSX/TSX support + fast refresh (page updates without reload)
// - tailwindcss() → scans src/ for class names and generates only the CSS you use
// - base          → the folder the site lives in. "/" locally and on a normal server,
//                   "/SafeMe/" on GitHub Pages (the deploy workflow sets BASE_PATH).
export default defineConfig({
  base: process.env.BASE_PATH ?? '/',
  plugins: [react(), tailwindcss()],
})
