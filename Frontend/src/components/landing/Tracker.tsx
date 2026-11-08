import { useTranslation } from 'react-i18next'
import { BatteryMedium, MapPinned, Navigation, ShieldCheck, Siren, type LucideIcon } from 'lucide-react'
import Reveal from './Reveal'
import SectionHeading from './SectionHeading'

const FEATURE_ICONS: LucideIcon[] = [ShieldCheck, Siren, Navigation]
const BATTERY_PERCENT = 78

// The route from home to school. The SAME string is used twice:
// to draw the line (SVG <path d>) and to move the dot along it (CSS offset-path).
const ROUTE = 'M 40 140 C 80 140, 90 70, 140 80 S 200 60, 226 46'

// A phone screen showing the tracker app (pure illustration, built with HTML/SVG)
function PhoneMock() {
  const { t } = useTranslation()

  return (
    <div className="mx-auto w-[300px] rounded-[2.25rem] border-2 border-teal-deep bg-teal-deep p-3 shadow-[8px_8px_0_0_#f2a33a]">
      <div className="overflow-hidden rounded-[1.6rem] bg-cream">
        <div className="flex items-center justify-between px-5 pb-3 pt-5">
          <p className="font-display text-xl font-extrabold text-teal-deep [font-stretch:75%]">{t('tracker.device.name')}</p>
          <span className="label-mono flex items-center gap-1.5 text-[10px] text-teal">
            <span className="size-2 bg-teal" aria-hidden="true" />
            {t('tracker.device.online')}
          </span>
        </div>

        {/* map: fixed 276×180 px, so the offset-path coordinates match the SVG */}
        <div className="relative h-[180px] w-[276px] border-y-2 border-teal-deep bg-teal" role="img" aria-label={t('tracker.device.mapLabel')}>
          <svg width="276" height="180" viewBox="0 0 276 180" className="absolute inset-0" aria-hidden="true">
            <g stroke="#1b6681" strokeWidth="6" fill="none">
              <path d="M0 110 H276" />
              <path d="M120 0 V180" />
              <path d="M0 40 C 90 60, 180 20, 276 30" />
            </g>
            {/* safe zone around the school */}
            <circle cx="226" cy="46" r="34" fill="rgb(242 163 58 / 0.15)" stroke="#f2a33a" strokeWidth="2" strokeDasharray="4 5" />
            <path d={ROUTE} stroke="#f7f4ee" strokeWidth="3" fill="none" strokeLinecap="round" strokeDasharray="1 7" />
            <rect x="32" y="132" width="16" height="16" fill="#f7f4ee" stroke="#08394b" strokeWidth="2" />
            <rect x="220" y="40" width="12" height="12" fill="#f2a33a" stroke="#08394b" strokeWidth="2" />
          </svg>
          {/* the moving child: a dot following the route */}
          <span
            className="absolute left-0 top-0 size-3.5 animate-travel rounded-full border-2 border-teal-deep bg-amber"
            style={{ offsetPath: `path('${ROUTE}')`, offsetRotate: '0deg' }}
            aria-hidden="true"
          />
        </div>

        <div className="space-y-3 p-4 text-teal-deep">
          <div className="flex items-center gap-3 rounded-md border-2 border-teal-deep bg-white p-3">
            <ShieldCheck className="size-5 text-teal" aria-hidden="true" />
            <div className="min-w-0 flex-1 leading-tight">
              <p className="label-mono text-[10px] text-muted">{t('tracker.device.zone')}</p>
              <p className="text-xs font-semibold">{t('tracker.device.zoneName')}</p>
            </div>
            <span className="label-mono rounded bg-teal px-1.5 py-0.5 text-[10px] text-white">{t('tracker.device.inside')}</span>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div className="rounded-md border-2 border-teal-deep bg-white p-3">
              <p className="label-mono flex items-center gap-1.5 text-[10px] text-muted">
                <BatteryMedium className="size-3.5" aria-hidden="true" />
                {t('tracker.device.battery')}
              </p>
              <p className="mt-1 font-display text-2xl font-extrabold tabular-nums">{BATTERY_PERCENT}%</p>
              <span className="mt-1 block h-1.5 bg-teal-soft" aria-hidden="true">
                <span className="block h-full bg-teal" style={{ width: `${BATTERY_PERCENT}%` }} />
              </span>
            </div>
            {/* SOS lives on the physical device: here it is only a picture, not a button */}
            <div className="flex items-center justify-center rounded-md border-2 border-teal-deep bg-amber-soft p-3" role="img" aria-label={t('tracker.device.sosHint')}>
              <span aria-hidden="true" className="flex size-12 items-center justify-center rounded-full border-2 border-teal-deep bg-amber-deep font-mono text-xs font-bold text-white">
                {t('tracker.device.sos')}
              </span>
            </div>
          </div>

          <div className="rounded-md border-2 border-teal-deep bg-white p-3">
            <p className="label-mono text-[10px] text-muted">{t('tracker.device.events')}</p>
            <ul className="mt-2 space-y-1.5 font-mono text-xs tabular-nums">
              <li className="flex items-center gap-2"><MapPinned className="size-3.5 text-amber-deep" aria-hidden="true" />{t('tracker.device.event1')}</li>
              <li className="flex items-center gap-2"><MapPinned className="size-3.5 text-teal" aria-hidden="true" />{t('tracker.device.event2')}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  )
}

export default function Tracker() {
  const { t } = useTranslation()
  const features = t('tracker.features', { returnObjects: true })

  return (
    <section id="tracker" aria-labelledby="tracker-title" className="border-y-2 border-teal-deep bg-teal py-20 md:py-28">
      <div className="mx-auto grid max-w-6xl items-center gap-16 px-4 sm:px-6 lg:grid-cols-2">
        <div>
          <Reveal>
            <SectionHeading
              id="tracker-title"
              eyebrow={t('tracker.eyebrow')}
              title={t('tracker.title')}
              text={t('tracker.text')}
              tone="dark"
            />
          </Reveal>

          <ul className="mt-10 space-y-4">
            {features.map((feature, index) => {
              const Icon = FEATURE_ICONS[index]
              return (
                <li key={feature.title}>
                  <Reveal delay={index * 100} className="flex gap-4 rounded-lg border-2 border-white/20 p-5">
                    <span className="flex size-11 shrink-0 items-center justify-center rounded-md bg-amber text-teal-deep">
                      <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <div>
                      <h3 className="text-2xl text-white">{feature.title}</h3>
                      <p className="mt-1 text-sm text-white/75 text-pretty">{feature.text}</p>
                    </div>
                  </Reveal>
                </li>
              )
            })}
          </ul>
        </div>

        <Reveal delay={150}>
          <PhoneMock />
        </Reveal>
      </div>
    </section>
  )
}
