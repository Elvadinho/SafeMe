import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'
import fr from './locales/fr.json'
import en from './locales/en.json'

// `as const` makes the type exactly 'fr' | 'en' instead of just string
export const SUPPORTED_LANGUAGES = ['fr', 'en'] as const
export type Language = (typeof SUPPORTED_LANGUAGES)[number] // → 'fr' | 'en'

const STORAGE_KEY = 'safeme-lang'
const DEFAULT_LANGUAGE: Language = 'fr' // default for Cameroon

// A "type guard": if it returns true, TypeScript knows `value` is a Language
function isLanguage(value: string | null): value is Language {
  return SUPPORTED_LANGUAGES.includes(value as Language)
}

// Your logic from the JS version, now typed
function getInitialLanguage(): Language {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (isLanguage(saved)) {
      return saved
    }
  } catch {
    // storage blocked (private mode…) → use the default
  }
  return DEFAULT_LANGUAGE
}

// `satisfies typeof fr` = if en.json forgets a key that fr.json has,
// TypeScript shows an error. No more missing translations!
const resources = {
  fr: { translation: fr },
  en: { translation: en satisfies typeof fr },
}

// Every time the language changes: update <html lang> + remember the choice.
// ⚠️ Registered BEFORE init(): init() itself sets the first language and fires
// this event. Listening after init() would miss it (that was a real bug!).
i18n.on('languageChanged', (lng) => {
  document.documentElement.lang = lng
  try {
    localStorage.setItem(STORAGE_KEY, lng)
  } catch {
    // storage blocked → we simply don't remember
  }
})

void i18n.use(initReactI18next).init({
  resources,
  lng: getInitialLanguage(),
  fallbackLng: DEFAULT_LANGUAGE,
  interpolation: { escapeValue: false }, // React already escapes text (XSS safe)
})

export default i18n
