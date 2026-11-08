// Shared by the Navbar and the Footer, so both menus always stay in sync.
// "/#how" (with the slash) works from ANY page: on /demo it goes back home first.
// `as const` keeps each key as an exact string type ('nav.how'…),
// which lets the typed t() function check that the key really exists.
export const NAV_LINKS = [
  { href: '/#how', key: 'nav.how' },
  { href: '/#modules', key: 'nav.modules' },
  { href: '/#tracker', key: 'nav.tracker' },
  { href: '/#ai', key: 'nav.ai' },
  { href: '/demo', key: 'nav.demo' },
] as const
