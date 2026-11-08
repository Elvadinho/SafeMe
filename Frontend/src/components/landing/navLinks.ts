import { withBase } from '../../utils/withBase'

// Shared by the Navbar and the Footer, so both menus always stay in sync.
// withBase('/#how') works from ANY page and ANY host ("/" locally, "/SafeMe/" on GitHub Pages).
// `as const` keeps each key as an exact string type ('nav.how'…),
// which lets the typed t() function check that the key really exists.
export const NAV_LINKS = [
  { href: withBase('/#how'), key: 'nav.how' },
  { href: withBase('/#modules'), key: 'nav.modules' },
  { href: withBase('/#tracker'), key: 'nav.tracker' },
  { href: withBase('/#ai'), key: 'nav.ai' },
  { href: withBase('/demo'), key: 'nav.demo' },
] as const
