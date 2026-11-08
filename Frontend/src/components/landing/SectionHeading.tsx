// The small mono label + big condensed title + intro text used by every section.
type SectionHeadingProps = {
  id?: string // used by <section aria-labelledby="..."> for screen readers
  eyebrow: string
  title: string
  accent?: string // optional second part of the title, in another color
  text?: string
  align?: 'left' | 'center'
  tone?: 'light' | 'dark' // dark = white text, for teal sections
  as?: 'h1' | 'h2' // h1 when this is THE main title of the page (e.g. /demo)
}

export default function SectionHeading({
  id,
  eyebrow,
  title,
  accent,
  text,
  align = 'left',
  tone = 'light',
  as: Heading = 'h2', // renamed with a capital: JSX needs it to use it as a tag
}: SectionHeadingProps) {
  const isDark = tone === 'dark'

  return (
    <div className={align === 'center' ? 'mx-auto max-w-3xl text-center' : 'max-w-3xl'}>
      <p className={`label-mono ${isDark ? 'text-amber' : 'text-amber-deep'}`}>{eyebrow}</p>
      <Heading
        id={id}
        className={`mt-4 text-5xl text-balance md:text-6xl ${isDark ? 'text-white' : 'text-teal-deep'}`}
      >
        {title}
        {/* Conditional rendering: the accent only exists for some sections */}
        {accent && (
          <>
            {' '}
            <span className={isDark ? 'text-amber' : 'text-amber-deep'}>{accent}</span>
          </>
        )}
      </Heading>
      {text && (
        <p className={`mt-5 text-lg text-pretty ${isDark ? 'text-white/80' : ''}`}>{text}</p>
      )}
    </div>
  )
}
