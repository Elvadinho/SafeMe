import { useTranslation } from 'react-i18next'
import { ArrowLeft } from 'lucide-react'
import AlertCard from '../components/demo/AlertCard'
import Footer from '../components/landing/Footer'
import Navbar from '../components/landing/Navbar'
import SectionHeading from '../components/landing/SectionHeading'
import { withBase } from '../utils/withBase'

// /demo: the live alert card (timer + map + sightings) on its own page.
export default function DemoPage() {
  const { t } = useTranslation()

  return (
    <>
      <Navbar />
      <main className="bg-cream">

        <div className="relative mx-auto grid max-w-6xl items-center gap-12 px-4 pb-24 pt-16 sm:px-6 lg:grid-cols-2 lg:pt-24">
          <div>
            <a
              href={withBase('/')}
              className="mb-8 inline-flex items-center gap-2 text-sm font-semibold label-mono text-teal hover:text-amber-deep"
            >
              <ArrowLeft className="size-4" aria-hidden="true" />
              {t('demo.back')}
            </a>
            <SectionHeading as="h1" eyebrow={t('demo.eyebrow')} title={t('demo.title')} text={t('demo.text')} />
          </div>

          <div className="mx-auto w-full max-w-md">
            <AlertCard />
          </div>
        </div>
      </main>
      <Footer />
    </>
  )
}
