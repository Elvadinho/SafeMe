import DemoPage from './pages/DemoPage'
import LandingPage from './pages/LandingPage'

// Tiny temporary "router": we read the URL path and choose the page.
// Vite's dev server answers every path with index.html, so /demo works.
// On Day 3 we replace this with react-router (/login, /alerts, /admin…).
export default function App() {
  const path = window.location.pathname

  if (path.startsWith('/demo')) {
    return <DemoPage />
  }
  return <LandingPage />
}
