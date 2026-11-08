type LogoProps = {
  tone?: 'light' | 'dark' // dark = drawn on the teal background
  showText?: boolean
}

// The SafeMe mark: a magnifying glass looking for a person.
// Drawn in SVG (not an image file) so it stays sharp at any size and
// can switch colors with the `tone` prop.
export default function Logo({ tone = 'light', showText = true }: LogoProps) {
  const isDark = tone === 'dark'
  const ring = isDark ? '#ffffff' : '#0e4d64'

  return (
    <span className="flex items-center gap-2">
      <svg viewBox="0 0 64 64" className="size-9" aria-hidden="true">
        {/* handle first, so the ring is drawn on top of it */}
        <line x1="40" y1="40" x2="56" y2="56" stroke="#f2a33a" strokeWidth="8" strokeLinecap="round" />
        <circle cx="27" cy="27" r="19" fill="none" stroke={ring} strokeWidth="7" />
        <circle cx="27" cy="21" r="5" fill={ring} />
        <path d="M17 36 a10 9 0 0 1 20 0 Z" fill={ring} />
      </svg>
      {showText && (
        <span className="font-display text-2xl font-extrabold tracking-tight [font-stretch:100%]">
          <span className={isDark ? 'text-white' : 'text-teal'}>Safe</span>
          <span className={isDark ? 'text-amber' : 'text-amber-deep'}>Me</span>
        </span>
      )}
    </span>
  )
}
