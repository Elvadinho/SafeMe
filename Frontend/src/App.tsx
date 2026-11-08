import DemoPage from './pages/DemoPage'
import LandingPage from './pages/LandingPage'
import { currentPath } from './utils/withBase'

// Tiny temporary "router": we read the URL path and choose the page.
// currentPath() removes the base folder, so "/SafeMe/demo" (GitHub Pages)
// and "/demo" (local) both become "/demo".
// On Day 3 we replace this with react-router (/login, /alerts, /admin…).
export default function App() {
  if (currentPath().startsWith('/demo')) {
    return <DemoPage />
  }
  return <LandingPage />
}
