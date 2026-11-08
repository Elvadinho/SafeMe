import { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { Menu, Siren, X } from 'lucide-react'
import ButtonLink from './ButtonLink'
import LanguageSwitcher from './LanguageSwitcher'
import Logo from './Logo'
import { NAV_LINKS } from './navLinks'
import { withBase } from '../../utils/withBase'

// A flat teal header, always the same color (like a "band"), sticky at the top.
export default function Navbar() {
  const { t } = useTranslation()
  const [isOpen, setIsOpen] = useState(false)
  const burgerRef = useRef<HTMLButtonElement>(null) // to give focus back after Escape

  // While the mobile menu is open:
  // - Escape closes it AND puts the keyboard focus back on the burger button
  // - widening the window to desktop size (lg = 64rem) closes it too
  useEffect(() => {
    if (!isOpen) return
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setIsOpen(false)
        burgerRef.current?.focus() // ?. = only if the button exists
      }
    }
    const desktop = window.matchMedia('(min-width: 64rem)')
    const onResize = () => {
      if (desktop.matches) setIsOpen(false)
    }
    window.addEventListener('keydown', onKeyDown)
    desktop.addEventListener('change', onResize)
    return () => {
      window.removeEventListener('keydown', onKeyDown)
      desktop.removeEventListener('change', onResize)
    }
  }, [isOpen])

  return (
    <header className="sticky top-0 z-50 border-b-2 border-teal-deep bg-teal">
      <div className="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <a href={withBase('/')} aria-label={t('nav.home')} className="rounded-md">
          <Logo tone="dark" />
        </a>

        {/* Desktop links */}
        <nav aria-label={t('nav.main')} className="hidden lg:block">
          <ul className="flex items-center gap-1">
            {NAV_LINKS.map((link) => (
              <li key={link.href}>
                <a
                  href={link.href}
                  className="label-mono rounded-md px-3 py-2 text-white/80 transition-colors duration-150 hover:text-amber"
                >
                  {t(link.key)}
                </a>
              </li>
            ))}
          </ul>
        </nav>

        <div className="flex items-center gap-3">
          <div className="hidden sm:block">
            <LanguageSwitcher tone="dark" />
          </div>
          {/* the wrapper hides it on phones: putting "hidden" on ButtonLink itself
              would clash with its own "inline-flex" class (and inline-flex wins) */}
          <div className="hidden sm:block">
            <ButtonLink href={withBase('/#report')}>
              <Siren className="size-4" aria-hidden="true" />
              {t('nav.report')}
            </ButtonLink>
          </div>

          {/* Burger: only below lg. 44px = comfortable touch target */}
          <button
            ref={burgerRef}
            type="button"
            onClick={() => setIsOpen((open) => !open)}
            aria-expanded={isOpen}
            // only point to the panel when it exists in the page
            aria-controls={isOpen ? 'mobile-menu' : undefined}
            aria-label={isOpen ? t('nav.closeMenu') : t('nav.openMenu')}
            className="flex size-11 items-center justify-center rounded-md text-white hover:bg-white/10 lg:hidden"
          >
            {isOpen ? <X className="size-6" aria-hidden="true" /> : <Menu className="size-6" aria-hidden="true" />}
          </button>
        </div>
      </div>

      {/* Mobile panel: rendered ONLY when open (conditional rendering) */}
      {isOpen && (
        <div id="mobile-menu" className="border-t border-white/15 px-4 pb-5 pt-2 lg:hidden">
          <nav aria-label={t('nav.main')}>
            <ul className="flex flex-col">
              {NAV_LINKS.map((link) => (
                <li key={link.href}>
                  <a
                    href={link.href}
                    onClick={() => setIsOpen(false)} // close after choosing
                    className="label-mono block rounded-md px-2 py-3.5 text-sm text-white hover:bg-white/10"
                  >
                    {t(link.key)}
                  </a>
                </li>
              ))}
            </ul>
          </nav>
          <div className="mt-3 flex items-center justify-between gap-3 sm:hidden">
            <LanguageSwitcher tone="dark" />
            <ButtonLink href={withBase('/#report')} onClick={() => setIsOpen(false)}>
              <Siren className="size-4" aria-hidden="true" />
              {t('nav.report')}
            </ButtonLink>
          </div>
        </div>
      )}
    </header>
  )
}
