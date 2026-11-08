import type { ReactNode } from 'react'
import { useTranslation } from 'react-i18next'
import { Check, Clock, EyeOff, Lock, MapPin, ShieldCheck, Users, X } from 'lucide-react'
import Reveal from './Reveal'
import SectionHeading from './SectionHeading'

type FeatureCardProps = {
  icon: ReactNode
  title: string
  text: string
  visual: ReactNode // the small illustration on top of the card
  className?: string
  delay?: number
}

// One card of the grid: an illustration on top, then icon + title + text.
function FeatureCard({ icon, title, text, visual, className = '', delay = 0 }: FeatureCardProps) {
  return (
    <Reveal delay={delay} className={className}>
      <article className="card flex h-full flex-col overflow-hidden">
        <div className="flex min-h-48 flex-1 items-center justify-center bg-teal-soft p-6">{visual}</div>
        <div className="border-t-2 border-teal-deep p-6">
          <div className="flex items-center gap-3">
            <span className="flex size-9 items-center justify-center rounded-md bg-teal text-white">{icon}</span>
            <h3 className="text-2xl text-teal-deep">{title}</h3>
          </div>
          <p className="mt-3 text-pretty">{text}</p>
        </div>
      </article>
    </Reveal>
  )
}

export default function WhySafeMe() {
  const { t } = useTranslation()
  const sightings = t('why.timeline.items', { returnObjects: true })

  return (
    <section aria-labelledby="why-title" className="bg-cream py-20 md:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <Reveal>
          <SectionHeading
            id="why-title"
            eyebrow={t('why.eyebrow')}
            title={t('why.title')}
            accent={t('why.titleAccent')}
            text={t('why.text')}
          />
        </Reveal>

        <div className="mt-14 grid gap-6 md:grid-cols-3">
          {/* 1. Moderation (wide) */}
          <FeatureCard
            className="md:col-span-2"
            icon={<ShieldCheck className="size-5" aria-hidden="true" />}
            title={t('why.verified.title')}
            text={t('why.verified.text')}
            visual={
              <div className="card w-full max-w-md p-4" aria-hidden="true">
                <div className="flex items-center gap-3">
                  <span className="flex size-11 items-center justify-center rounded-md bg-teal-soft font-display font-extrabold text-teal">
                    AN
                  </span>
                  <div className="min-w-0 flex-1">
                    <p className="truncate font-semibold text-teal-deep">{t('why.verified.child')}</p>
                    <p className="label-mono flex items-center gap-1.5 text-[10px] text-muted">
                      <Clock className="size-3.5" />
                      {t('why.verified.status')}
                    </p>
                  </div>
                </div>
                <div className="mt-4 grid grid-cols-2 gap-2 font-mono text-xs font-bold uppercase">
                  <span className="flex h-9 items-center justify-center gap-1.5 rounded-md border-2 border-teal-deep bg-teal text-white">
                    <Check className="size-4" />
                    {t('why.verified.approve')}
                  </span>
                  <span className="flex h-9 items-center justify-center gap-1.5 rounded-md border-2 border-teal-deep text-teal-deep">
                    <X className="size-4" />
                    {t('why.verified.reject')}
                  </span>
                </div>
              </div>
            }
          />

          {/* 2. Masked phone number */}
          <FeatureCard
            delay={100}
            icon={<Lock className="size-5" aria-hidden="true" />}
            title={t('why.masked.title')}
            text={t('why.masked.text')}
            visual={
              <div className="text-center" aria-hidden="true">
                <span className="mx-auto flex size-14 items-center justify-center rounded-md border-2 border-teal-deep bg-white text-teal">
                  <EyeOff className="size-6" />
                </span>
                <p className="mt-5 font-mono text-xl font-bold tracking-wider text-teal-deep tabular-nums">
                  {t('why.masked.number')}
                </p>
              </div>
            }
          />

          {/* 3. Neighbours first: concentric squares, like the map tiles */}
          <FeatureCard
            delay={100}
            icon={<Users className="size-5" aria-hidden="true" />}
            title={t('why.nearby.title')}
            text={t('why.nearby.text')}
            visual={
              <div className="grid grid-cols-7 gap-1" aria-hidden="true">
                {/* 49 tiles; their distance from the center decides the color */}
                {Array.from({ length: 49 }, (_, i) => {
                  const distance = Math.max(Math.abs((i % 7) - 3), Math.abs(Math.floor(i / 7) - 3))
                  const color =
                    distance === 0 ? 'bg-amber-deep' : distance === 1 ? 'bg-amber' : distance === 2 ? 'bg-teal/50' : 'bg-teal/20'
                  return <span key={i} className={`size-4 ${color}`} />
                })}
              </div>
            }
          />

          {/* 4. Timeline of sightings (wide) */}
          <FeatureCard
            delay={200}
            className="md:col-span-2"
            icon={<MapPin className="size-5" aria-hidden="true" />}
            title={t('why.timeline.title')}
            text={t('why.timeline.text')}
            visual={
              <ol className="w-full max-w-md space-y-3" aria-hidden="true">
                {sightings.map((item, index) => (
                  <li
                    key={item}
                    className="flex items-center gap-3 rounded-md border-2 border-teal-deep bg-white px-4 py-2.5"
                    style={{ opacity: 1 - index * 0.25 }} // older sightings fade
                  >
                    <MapPin className="size-4 text-amber-deep" />
                    <span className="font-mono text-sm font-medium text-teal-deep tabular-nums">{item}</span>
                    {index === 0 && (
                      <span className="label-mono ml-auto rounded bg-amber-deep px-2 py-0.5 text-[10px] text-white">
                        {t('why.timeline.live')}
                      </span>
                    )}
                  </li>
                ))}
              </ol>
            }
          />
        </div>
      </div>
    </section>
  )
}
