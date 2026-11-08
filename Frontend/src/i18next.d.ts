// This file teaches TypeScript the SHAPE of our translations.
// Result: t('hero.title') autocompletes in your editor, and a typo like
// t('hero.titel') becomes a red error instead of a blank text on screen.
import 'i18next'
import type fr from './locales/fr.json'

declare module 'i18next' {
  interface CustomTypeOptions {
    defaultNS: 'translation'
    resources: {
      translation: typeof fr
    }
  }
}
