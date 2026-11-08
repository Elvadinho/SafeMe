import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { BadgeCheck, Eye, MapPin } from 'lucide-react'
import { formatDuration } from '../../utils/formatDuration'
import AlertRadiusMap from './AlertRadiusMap'

// Demo: pretend the child disappeared 2h14 before the page opened
const DEMO_OFFSET_MS = (2 * 60 + 14) * 60 * 1000

export default function AlertCard() {
  const { t } = useTranslation()

  // Lazy initial state (arrow function): Date.now() runs only on the FIRST render,
  // not on every re-render. `startedAt` never changes, so we ignore the setter.
  const [startedAt] = useState(() => Date.now() - DEMO_OFFSET_MS)
  const [now, setNow] = useState(() => Date.now())

  // Your timer logic ✔: tick every second, and clean up on unmount.
  useEffect(() => {
    const id = setInterval(() => setNow(Date.now()), 1000)
    return () => clearInterval(id)
  }, [])

  const elapsed = formatDuration(now - startedAt)

  return (
    <div className="mx-auto w-full max-w-md lg:mr-0">
      <p className="mb-2 text-right text-xs font-medium text-muted">{t('card.demo')}</p>

      <article className="rounded-2xl border border-line bg-white p-5 shadow-xl shadow-slate-900/[0.06] sm:p-6">
        {/* Header: alert badge + verified badge */}
        <div className="flex items-center justify-between">
          <span className="inline-flex items-center gap-1.5 rounded-full bg-urgent-soft px-3 py-1 text-xs font-bold uppercase tracking-wide text-urgent">
            <span
              className="size-2 animate-pulse rounded-full bg-urgent motion-reduce:animate-none"
              aria-hidden="true"
            />
            {t('card.alert')}
          </span>
          <span className="inline-flex items-center gap-1 text-xs font-semibold text-safe">
            <BadgeCheck className="size-4" aria-hidden="true" />
            {t('card.verified')}
          </span>
        </div>

        {/* Child identity (initials instead of a real child's photo) */}
        <div className="mt-5 flex items-center gap-4">
          <div
            className="flex size-14 items-center justify-center rounded-xl bg-primary-soft text-lg font-bold text-primary"
            aria-hidden="true"
          >
            AN
          </div>
          <div>
            <h2 className="text-xl font-bold text-ink">Amina N.</h2>
            <p className="text-sm">{t('card.age', { count: 7 })}</p>
          </div>
        </div>

        {/* Key facts. <dl> = "description list": a term (dt) + its value (dd) */}
        <dl className="mt-5 grid grid-cols-2 gap-4 rounded-xl bg-surface-alt p-4">
          <div>
            <dt className="text-xs text-muted">{t('card.lastSeen')}</dt>
            <dd className="mt-1 flex items-center gap-1 text-sm font-semibold text-ink">
              <MapPin className="size-4 shrink-0 text-primary" aria-hidden="true" />
              Mokolo, Yaoundé
            </dd>
          </div>
          <div>
            <dt className="text-xs text-muted">{t('card.missingFor')}</dt>
            {/* tabular-nums: all digits same width → the timer doesn't wiggle */}
            <dd className="mt-1 font-mono text-lg font-bold tabular-nums text-urgent">{elapsed}</dd>
          </div>
        </dl>

        <div className="mt-4">
          <AlertRadiusMap label={t('card.mapLabel')} radiusLabel={t('card.radius')} />
        </div>

        <h3 className="mt-5 text-xs font-semibold uppercase tracking-wide text-muted">
          {t('card.sightings')}
        </h3>
        <ul className="mt-2 space-y-2 text-sm">
          <li className="flex items-center gap-2">
            <Eye className="size-4 shrink-0 text-primary" aria-hidden="true" />
            {t('card.sighting1')}
          </li>
          <li className="flex items-center gap-2">
            <Eye className="size-4 shrink-0 text-primary" aria-hidden="true" />
            {t('card.sighting2')}
          </li>
        </ul>

        <button
          type="button"
          className="mt-5 h-11 w-full rounded-lg bg-primary text-sm font-semibold text-white transition-[transform,background-color] duration-150 hover:bg-primary-hover active:scale-[0.98] motion-reduce:active:scale-100"
        >
          {t('card.cta')}
        </button>
      </article>
    </div>
  )
}
