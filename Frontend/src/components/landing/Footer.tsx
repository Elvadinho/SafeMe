import { useTranslation } from 'react-i18next'
import { Phone } from 'lucide-react'
import Logo from './Logo'
import { NAV_LINKS } from './navLinks'

export default function Footer() {
  const { t } = useTranslation()
  const year = new Date().getFullYear() // always the current year, no update needed

  return (
    <footer className="border-t-2 border-teal-deep bg-teal-deep text-white/70">
      <div className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-[2fr_1fr_1fr]">
        <div>
          <Logo tone="dark" />
          <p className="mt-5 max-w-xs font-display text-3xl font-extrabold leading-tight text-white [font-stretch:75%]">
            {t('footer.tagline')}
          </p>
        </div>

        <nav aria-labelledby="footer-product">
          <h2 id="footer-product" className="label-mono text-amber">
            {t('footer.product')}
          </h2>
          <ul className="mt-4 space-y-3 text-sm">
            {NAV_LINKS.map((link) => (
              <li key={link.href}>
                <a href={link.href} className="transition-colors duration-150 hover:text-amber">
                  {t(link.key)}
                </a>
              </li>
            ))}
          </ul>
        </nav>

        <div>
          <h2 className="label-mono text-amber">{t('footer.urgent')}</h2>
          <ul className="mt-4 space-y-3 text-sm">
            {/* tel: links → one tap calls the number on a phone */}
            <li>
              <a href="tel:117" className="flex items-center gap-2 transition-colors duration-150 hover:text-amber">
                <Phone className="size-4 text-amber" aria-hidden="true" />
                {t('footer.police')}
              </a>
            </li>
            <li>
              <a href="tel:118" className="flex items-center gap-2 transition-colors duration-150 hover:text-amber">
                <Phone className="size-4 text-amber" aria-hidden="true" />
                {t('footer.firefighters')}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div className="border-t border-white/15">
        <div className="label-mono mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-[10px] sm:flex-row sm:justify-between sm:px-6">
          <p>
            © {year} SafeMe. {t('footer.rights')}
          </p>
          <p>{t('footer.made')}</p>
        </div>
      </div>
    </footer>
  )
}
