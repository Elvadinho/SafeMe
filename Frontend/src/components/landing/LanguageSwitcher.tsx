import { useTranslation } from 'react-i18next'
import { SUPPORTED_LANGUAGES, type Language } from '../../i18n'

// Full names for screen readers (the button itself only shows "FR"/"EN")
const LABELS: Record<Language, string> = {
  fr: 'Français',
  en: 'English',
}

type LanguageSwitcherProps = {
  tone?: 'light' | 'dark' // dark = used on the teal header
}

export default function LanguageSwitcher({ tone = 'light' }: LanguageSwitcherProps) {
  const { t, i18n } = useTranslation()
  const current = i18n.resolvedLanguage // the language actually in use
  const isDark = tone === 'dark'

  return (
    <div
      className={`flex overflow-hidden rounded-md border-2 font-mono text-xs font-bold ${
        isDark ? 'border-white/30' : 'border-teal-deep'
      }`}
      role="group"
      aria-label={t('nav.language')}
    >
      {SUPPORTED_LANGUAGES.map((lng) => {
        const active = lng === current

        // Pick the style from 2 questions: is it active? are we on dark?
        let style = isDark ? 'text-white/70 hover:text-white' : 'text-muted hover:text-teal-deep'
        if (active) style = isDark ? 'bg-white text-teal' : 'bg-teal text-white'

        return (
          <button
            key={lng}
            type="button"
            onClick={() => void i18n.changeLanguage(lng)}
            aria-pressed={active}
            aria-label={LABELS[lng]}
            className={`h-8 min-w-11 px-2 uppercase transition-colors duration-150 ${style}`}
          >
            {lng}
          </button>
        )
      })}
    </div>
  )
}
