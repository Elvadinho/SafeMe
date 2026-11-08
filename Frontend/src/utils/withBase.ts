// Builds a link that works wherever the site is hosted.
// import.meta.env.BASE_URL comes from `base` in vite.config.ts: "/" or "/SafeMe/".
//   withBase('/demo')  → "/demo"          (local)   or "/SafeMe/demo"  (GitHub Pages)
//   withBase('/#how')  → "/#how"          (local)   or "/SafeMe/#how"  (GitHub Pages)
export function withBase(path: string): string {
  // BASE_URL already ends with "/", so we remove the leading "/" of `path`
  return import.meta.env.BASE_URL + path.replace(/^\//, '')
}

// The current path WITHOUT the base folder: "/SafeMe/demo" → "/demo"
export function currentPath(): string {
  const base = import.meta.env.BASE_URL
  const path = window.location.pathname
  return path.startsWith(base) ? '/' + path.slice(base.length) : path
}
