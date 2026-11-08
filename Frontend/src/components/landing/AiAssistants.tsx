import { useTranslation } from 'react-i18next'
import { Bot, ChartScatter, MessageCircleHeart, QrCode, type LucideIcon } from 'lucide-react'
import Reveal from './Reveal'
import SectionHeading from './SectionHeading'

const ITEM_ICONS: LucideIcon[] = [MessageCircleHeart, QrCode, ChartScatter]

export default function AiAssistants() {
  const { t } = useTranslation()
  const items = t('ai.items', { returnObjects: true })

  return (
    <section id="ai" aria-labelledby="ai-title" className="bg-white py-20 md:py-28">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <Reveal>
          <SectionHeading id="ai-title" eyebrow={t('ai.eyebrow')} title={t('ai.title')} />
        </Reveal>

        <div className="mt-14 grid items-stretch gap-8 lg:grid-cols-[1.15fr_1fr]">
          {/* Chat preview: a sample conversation with the parent assistant */}
          <Reveal className="h-full">
            <div className="card flex h-full flex-col justify-center gap-4 bg-cream! p-6 md:p-10" aria-hidden="true">
              <p className="ml-auto max-w-[80%] rounded-lg border-2 border-teal-deep bg-white px-4 py-3 text-sm font-medium text-teal-deep">
                {t('ai.chat.question')}
              </p>
              <div className="flex max-w-[88%] items-end gap-2">
                <span className="flex size-9 shrink-0 items-center justify-center rounded-md bg-amber-deep text-white">
                  <Bot className="size-5" />
                </span>
                <p className="rounded-lg border-2 border-teal-deep bg-teal px-4 py-3 text-sm text-white">{t('ai.chat.answer')}</p>
              </div>
              <p className="label-mono ml-11 mt-1 text-muted">{t('ai.chat.languages')}</p>
            </div>
          </Reveal>

          <ul className="flex flex-col gap-5">
            {items.map((item, index) => {
              const Icon = ITEM_ICONS[index]
              return (
                <li key={item.title} className="flex-1">
                  <Reveal delay={index * 100} className="card flex h-full gap-4 p-6">
                    <span className="flex size-12 shrink-0 items-center justify-center rounded-md bg-teal-soft text-teal">
                      <Icon className="size-6" aria-hidden="true" />
                    </span>
                    <div>
                      <h3 className="text-2xl text-teal-deep">{item.title}</h3>
                      <p className="mt-1 text-pretty">{item.text}</p>
                    </div>
                  </Reveal>
                </li>
              )
            })}
          </ul>
        </div>
      </div>
    </section>
  )
}
