import AiAssistants from '../components/landing/AiAssistants'
import FinalCta from '../components/landing/FinalCta'
import Footer from '../components/landing/Footer'
import Hero from '../components/landing/Hero'
import HowItWorks from '../components/landing/HowItWorks'
import Modules from '../components/landing/Modules'
import Navbar from '../components/landing/Navbar'
import Tracker from '../components/landing/Tracker'
import WhySafeMe from '../components/landing/WhySafeMe'

// A "page" only ASSEMBLES sections; each section lives in its own component.
// Rhythm of "bands": teal hero → cream → white → cream → teal → white → amber → deep teal footer
export default function LandingPage() {
  return (
    <>
      <Navbar />
      <main>
        <Hero />
        <WhySafeMe />
        <HowItWorks />
        <Modules />
        <Tracker />
        <AiAssistants />
        <FinalCta />
      </main>
      <Footer />
    </>
  )
}
