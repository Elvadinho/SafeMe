import { useTranslation } from 'react-i18next'
import { FileText, Phone, Siren } from 'lucide-react'
import ButtonLink from './ButtonLink'
import Reveal from './Reveal'

// id="report" → every "Report" link on the page scrolls here.
// Later these buttons will open the real forms (/report, /documents/new).
export default function FinalCta() {
  const { t } = useTranslation()

  return (
    <section id="report" aria-labelledby="cta-title" className="border-t-2 border-teal-deep bg-amber py-20 md:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <Reveal className="card mx-auto max-w-4xl px-6 py-14 text-center sm:px-12">
          <p className="label-mono text-amber-deep">SafeMe · 117</p>
          <h2 id="cta-title" className="mt-4 text-5xl text-teal-deep text-balance md:text-7xl">
            {t('cta.title')}
          </h2>
          <p className="mx-auto mt-5 max-w-xl text-lg text-pretty">{t('cta.text')}</p>

          <div className="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
            {/* placeholder: becomes href="/report" when we build the report form */}
            <ButtonLink href="#report" size="lg">
              <Siren className="size-5" aria-hidden="true" />
              {t('cta.report')}
            </ButtonLink>
            <ButtonLink href="#modules" variant="secondary" size="lg">
              <FileText className="size-5" aria-hidden="true" />
              {t('cta.document')}
            </ButtonLink>
          </div>

          <p className="mx-auto mt-10 flex max-w-xs items-center justify-center gap-2 text-left text-sm text-muted sm:max-w-none">
            <Phone className="size-4 shrink-0 text-amber-deep" aria-hidden="true" />
            <span>{t('cta.emergency')}</span>
          </p>
        </Reveal>
      </div>
    </section>
  )
}
