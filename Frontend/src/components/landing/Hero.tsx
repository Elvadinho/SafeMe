import type { CSSProperties } from 'react'
import { useTranslation } from 'react-i18next'
import { PlayCircle, Siren } from 'lucide-react'
import ButtonLink from './ButtonLink'
import CameroonMap from './CameroonMap'

// Each block of the hero fades up one after the other on page load
const enter = (delayMs: number): CSSProperties => ({ animationDelay: `${delayMs}ms` })

export default function Hero() {
  const { t } = useTranslation()
  const stats = t('hero.stats', { returnObjects: true })

  return (
    <section aria-labelledby="hero-title" className="border-b-2 border-teal-deep bg-teal">
      <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 pb-14 pt-14 sm:px-6 lg:grid-cols-[1.1fr_1fr] lg:pb-20 lg:pt-20">
        <div className="text-center lg:text-left">
          <p className="label-mono animate-fade-up text-amber" style={enter(0)}>
            {t('hero.eyebrow')}
          </p>

          <h1
            id="hero-title"
            className="mt-5 animate-fade-up text-6xl text-white text-balance sm:text-7xl lg:text-[5.25rem]"
            style={enter(80)}
          >
            {t('hero.titleStart')} <span className="text-amber">{t('hero.titleAccent')}</span>
          </h1>

          <p
            className="mx-auto mt-6 max-w-xl animate-fade-up text-lg text-white/80 text-pretty lg:mx-0"
            style={enter(160)}
          >
            {t('hero.subtitle')}
          </p>

          <div
            className="mt-9 flex animate-fade-up flex-col justify-center gap-4 sm:flex-row lg:justify-start"
            style={enter(240)}
          >
            <ButtonLink href="#report" size="lg">
              <Siren className="size-5" aria-hidden="true" />
              {t('hero.ctaReport')}
            </ButtonLink>
            <ButtonLink href="/demo" variant="onDark" size="lg">
              <PlayCircle className="size-5" aria-hidden="true" />
              {t('hero.ctaDemo')}
            </ButtonLink>
          </div>
        </div>

        <div className="animate-fade-up" style={enter(200)}>
          <CameroonMap />
        </div>
      </div>

      {/* Stat row: big condensed values + mono labels */}
      <div className="border-t-2 border-teal-deep bg-teal-deep/40">
        <dl className="mx-auto grid max-w-6xl grid-cols-2 gap-y-8 px-4 py-10 sm:px-6 md:grid-cols-4">
          {stats.map((stat, index) => (
            // <dt> = the label, <dd> = the value. flex-col-reverse shows the value on top
            <div key={stat.label} className="flex flex-col-reverse items-center gap-2 text-center">
              <dt className="label-mono text-white/70">{stat.label}</dt>
              <dd
                className={`font-display text-5xl font-extrabold tabular-nums [font-stretch:75%] md:text-6xl ${
                  index === 0 ? 'text-amber' : 'text-white'
                }`}
              >
                {stat.value}
              </dd>
            </div>
          ))}
        </dl>
      </div>
    </section>
  )
}
