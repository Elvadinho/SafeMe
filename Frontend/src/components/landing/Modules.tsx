import { useTranslation } from 'react-i18next'
import { ArrowRight, Check, FileText, MapPin, Siren, UserRound } from 'lucide-react'
import ButtonLink from './ButtonLink'
import Reveal from './Reveal'
import SectionHeading from './SectionHeading'

type FeatureListProps = { items: string[]; tone: 'light' | 'dark' }

// The check-list used by both module cards
function FeatureList({ items, tone }: FeatureListProps) {
  return (
    <ul className="grid gap-3 sm:grid-cols-2">
      {items.map((item) => (
        <li key={item} className="flex items-start gap-2.5 text-sm">
          <span
            className={`mt-0.5 flex size-5 shrink-0 items-center justify-center rounded ${
              tone === 'dark' ? 'bg-amber text-teal-deep' : 'bg-teal text-white'
            }`}
          >
            <Check className="size-3" aria-hidden="true" />
          </span>
          {item}
        </li>
      ))}
    </ul>
  )
}

export default function Modules() {
  const { t } = useTranslation()

  return (
    <section id="modules" aria-labelledby="modules-title" className="bg-cream py-20 md:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <Reveal>
          <SectionHeading id="modules-title" eyebrow={t('modules.eyebrow')} title={t('modules.title')} />
        </Reveal>

        <div className="mt-14 grid gap-8 lg:grid-cols-2">
          {/* ---------- Missing children: main module, teal ---------- */}
          <Reveal>
            {/* "bg-teal!" = important: beats the white background of the "card" utility */}
            <article className="card flex h-full flex-col bg-teal! p-8 text-white/85 md:p-10">
              <span className="label-mono w-fit rounded border-2 border-amber px-2 py-1 text-amber">
                {t('modules.children.tag')}
              </span>
              <h3 className="mt-6 text-4xl text-white md:text-5xl">{t('modules.children.title')}</h3>
              <p className="mb-8 mt-4 max-w-md text-lg text-pretty">{t('modules.children.text')}</p>
              <FeatureList items={t('modules.children.features', { returnObjects: true })} tone="dark" />
              <div className="mt-auto pt-10">
                <ButtonLink href="#report" size="lg">
                  <Siren className="size-5" aria-hidden="true" />
                  {t('modules.children.cta')}
                </ButtonLink>
              </div>
            </article>
          </Reveal>

          {/* ---------- Found documents: white, with a masked ID card ---------- */}
          <Reveal delay={120}>
            <article className="card flex h-full flex-col p-8 md:p-10">
              <span className="label-mono w-fit rounded border-2 border-teal px-2 py-1 text-teal">
                {t('modules.documents.tag')}
              </span>
              <h3 className="mt-6 text-4xl text-teal-deep md:text-5xl">{t('modules.documents.title')}</h3>
              <p className="mt-4 max-w-md text-lg text-pretty">{t('modules.documents.text')}</p>

              {/* The sample document: everything sensitive is masked */}
              <div className="my-8 flex justify-center" aria-hidden="true">
                <div className="w-full max-w-sm -rotate-2 rounded-lg border-2 border-teal-deep bg-amber-soft p-5 shadow-hard transition-transform duration-300 hover:rotate-0">
                  <p className="label-mono flex items-center gap-2 text-[10px] text-amber-deep">
                    <FileText className="size-4" />
                    {t('modules.documents.sample.type')}
                  </p>
                  <div className="mt-4 flex gap-4">
                    <span className="flex h-20 w-16 items-center justify-center rounded border-2 border-teal-deep/30 bg-white text-teal/40">
                      <UserRound className="size-8" />
                    </span>
                    <div className="space-y-1.5 pt-1">
                      <p className="font-mono font-bold tracking-wider text-teal-deep">{t('modules.documents.sample.name')}</p>
                      <p className="font-mono text-sm text-muted tabular-nums">{t('modules.documents.sample.number')}</p>
                      <p className="flex items-center gap-1 text-xs">
                        <MapPin className="size-3.5 text-amber-deep" />
                        {t('modules.documents.sample.found')}
                      </p>
                    </div>
                  </div>
                  <p className="label-mono mt-4 rounded bg-teal px-3 py-1.5 text-center text-[10px] text-white">
                    {t('modules.documents.sample.status')}
                  </p>
                </div>
              </div>

              <FeatureList items={t('modules.documents.features', { returnObjects: true })} tone="light" />
              <div className="mt-auto pt-10">
                <ButtonLink href="#report" variant="secondary" size="lg">
                  {t('modules.documents.cta')}
                  <ArrowRight className="size-5" aria-hidden="true" />
                </ButtonLink>
              </div>
            </article>
          </Reveal>
        </div>
      </div>
    </section>
  )
}
