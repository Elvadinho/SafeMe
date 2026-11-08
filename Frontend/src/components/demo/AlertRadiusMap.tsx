// A tiny illustrated "map" for the hero card: the red pin is the last place
// the child was seen, the dashed circle is the alert radius, the blue dots are
// sightings, and the red rings pulse outward (the radius grows over time).
// Pure SVG = no map library, no network request, instant on slow connections.
type AlertRadiusMapProps = {
  label: string // description read by screen readers
  radiusLabel: string // the small caption, e.g. "Alert radius · 5 km"
}

export default function AlertRadiusMap({ label, radiusLabel }: AlertRadiusMapProps) {
  return (
    <figure className="relative overflow-hidden rounded-xl border border-line bg-surface-alt">
      <svg viewBox="0 0 320 150" className="block h-auto w-full" role="img" aria-label={label}>
        {/* Streets */}
        <g className="fill-none stroke-line" strokeWidth="7" strokeLinecap="round">
          <path d="M-10 42 C 80 58, 170 18, 330 46" />
          <path d="M-10 118 C 90 102, 205 134, 330 106" />
          <path d="M118 -10 C 132 50, 104 100, 126 160" />
          <path d="M236 -10 C 220 58, 252 110, 240 160" />
        </g>

        {/* Alert radius (dashed) */}
        <circle
          cx="160"
          cy="75"
          r="62"
          className="fill-primary/5 stroke-primary/40"
          strokeDasharray="4 5"
          strokeWidth="1.5"
        />

        {/* Two pulsing rings, the 2nd one delayed by half the animation.
            transform-box: fill-box → each ring scales from its own center.
            Hidden for people who turned off animations. */}
        <circle
          cx="160"
          cy="75"
          r="62"
          className="origin-center animate-ring fill-none stroke-urgent/50 [transform-box:fill-box] motion-reduce:hidden"
          strokeWidth="2"
        />
        <circle
          cx="160"
          cy="75"
          r="62"
          className="origin-center animate-ring fill-none stroke-urgent/50 [transform-box:fill-box] motion-reduce:hidden"
          strokeWidth="2"
          style={{ animationDelay: '1.5s' }}
        />

        {/* Sightings */}
        <circle cx="206" cy="52" r="6" className="fill-primary stroke-white" strokeWidth="2.5" />
        <circle cx="122" cy="101" r="6" className="fill-primary stroke-white" strokeWidth="2.5" />

        {/* Last seen location */}
        <circle cx="160" cy="75" r="9" className="fill-urgent stroke-white" strokeWidth="3" />
      </svg>

      <figcaption className="absolute bottom-2 left-2 rounded-md bg-white/90 px-2 py-1 text-[11px] font-semibold text-ink shadow-sm">
        {radiusLabel}
      </figcaption>
    </figure>
  )
}
