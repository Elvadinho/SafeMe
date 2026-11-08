import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css' // Tailwind + design tokens
import './i18n' // must run before <App /> so translations are ready
import App from './App'

// getElementById can return null (TypeScript knows it!), so we check it.
const rootElement = document.getElementById('root')
if (!rootElement) {
  throw new Error('Element #root not found in index.html')
}

// StrictMode renders components twice in development on purpose,
// to reveal bugs like a missing useEffect cleanup.
createRoot(rootElement).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
