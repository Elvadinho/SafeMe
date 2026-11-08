import { useTranslation } from 'react-i18next'
import { BellRing, Eye, FilePlus2, ShieldCheck, type LucideIcon } from 'lucide-react'
import Reveal from './Reveal'
import SectionHeading from './SectionHeading'

// Icons are matched to the translated steps by POSITION (index 0 → FilePlus2…)
const STEP_ICONS: LucideIcon[] = [FilePlus2, ShieldCheck, BellRing, Eye]

export default function HowItWorks() {
  const { t } = useTranslation()
  const steps = t('how.steps', { returnObjects: true })

  return (
    <section id="how" aria-labelledby="how-title" className="border-y-2 border-teal-deep bg-white py-20 md:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <Reveal>
          <SectionHeading id="how-title" eyebrow={t('how.eyebrow')} title={t('how.title')} />
        </Reveal>

        <ol className="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {steps.map((step, index) => {
            const Icon = STEP_ICONS[index] // a component stored in a variable: must start with a capital
            // padStart(2, '0') turns 1 → "01" (same width for every number)
            const number = String(index + 1).padStart(2, '0')

            return (
              <li key={step.title}>
                <Reveal delay={index * 100} className="card h-full p-6">
                  <div className="flex items-center justify-between">
                    <span className="font-display text-5xl font-extrabold text-amber-deep tabular-nums [font-stretch:75%]">
                      {number}
                    </span>
                    <span className="flex size-11 items-center justify-center rounded-md bg-teal-soft text-teal">
                      <Icon className="size-5" aria-hidden="true" />
                    </span>
                  </div>
                  <h3 className="mt-6 text-2xl text-teal-deep">{step.title}</h3>
                  <p className="mt-2 text-pretty">{step.text}</p>
                </Reveal>
              </li>
            )
          })}
        </ol>
      </div>
    </section>
  )
}
