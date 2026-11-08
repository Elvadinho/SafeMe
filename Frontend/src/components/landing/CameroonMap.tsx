import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { CAMEROON_ROWS, CITIES, type City } from '../../data/cameroonTiles'

// ---- Grid geometry -------------------------------------------------------
const TILE = 10 // one tile = 10×10 units in the SVG
const GAP = 2 // space between tiles → the "pixel" look
const COLS = CAMEROON_ROWS[0].length
const ROWS = CAMEROON_ROWS.length

// ---- Demo activity per city (fake numbers, clearly labelled on screen) ----
// Later this comes from the Laravel API: GET /api/alerts/stats-by-city
const ACTIVITY: Record<string, { alerts: number; sightings: number }> = {
  Douala: { alerts: 3, sightings: 5 },
  'Yaoundé': { alerts: 2, sightings: 6 },
  Bafoussam: { alerts: 1, sightings: 2 },
  Bamenda: { alerts: 1, sightings: 3 },
  Garoua: { alerts: 1, sightings: 2 },
  Maroua: { alerts: 2, sightings: 1 },
  'Ngaoundéré': { alerts: 0, sightings: 2 },
  Bertoua: { alerts: 1, sightings: 1 },
  Ebolowa: { alerts: 0, sightings: 1 },
  Buea: { alerts: 1, sightings: 2 },
  Kribi: { alerts: 0, sightings: 2 },
}

// Tiles around a city, from the closest to the farthest (a small spiral)
const SPIRAL = [
  [0, 0], [1, 0], [0, 1], [-1, 0], [0, -1], [1, 1], [-1, 1],
  [1, -1], [-1, -1], [2, 0], [0, 2], [-2, 0], [0, -2],
]

type TileKind = 'alert' | 'sighting'

const isLand = (col: number, row: number) => CAMEROON_ROWS[row]?.[col] === '#'

// Computed ONCE when the file loads (the data never changes):
// "col,row" → which color that tile gets.
const HIGHLIGHTS = new Map<string, TileKind>()
for (const city of CITIES) {
  const { alerts, sightings } = ACTIVITY[city.name] ?? { alerts: 0, sightings: 0 }
  let painted = 0
  for (const [dx, dy] of SPIRAL) {
    if (painted >= alerts + sightings) break
    const col = city.col + dx
    const row = city.row + dy
    const key = `${col},${row}`
    if (!isLand(col, row) || HIGHLIGHTS.has(key)) continue
    // the first tiles are alerts, the next ones are sightings
    HIGHLIGHTS.set(key, painted < alerts ? 'alert' : 'sighting')
    painted++
  }
}

const TILE_COLORS: Record<TileKind | 'land', string> = {
  land: '#1b6681',
  alert: '#f2a33a',
  sighting: '#f7f4ee',
}

// Position of a tile's center, in % of the map → used for HTML elements on top of the SVG
const leftPct = (col: number) => `${((col + 0.5) / COLS) * 100}%`
const topPct = (row: number) => `${((row + 0.5) / ROWS) * 100}%`

export default function CameroonMap() {
  const { t } = useTranslation()
  // Which city shows its tooltip. Douala by default so the map never looks empty.
  const [active, setActive] = useState<City>(CITIES[1])
  const activity = ACTIVITY[active.name]

  return (
    <figure className="mx-auto w-full max-w-[420px]">
      <div className="relative">
        <svg
          viewBox={`0 0 ${COLS * TILE} ${ROWS * TILE}`}
          className="block h-auto w-full"
          role="img"
          aria-label={t('map.label')}
        >
          {/* 2 nested .map(): rows, then each character of the row */}
          {CAMEROON_ROWS.map((line, row) =>
            [...line].map((char, col) => {
              if (char !== '#') return null // outside Cameroon: draw nothing
              const kind = HIGHLIGHTS.get(`${col},${row}`)
              return (
                <rect
                  key={`${col}-${row}`}
                  x={col * TILE}
                  y={row * TILE}
                  width={TILE - GAP}
                  height={TILE - GAP}
                  fill={TILE_COLORS[kind ?? 'land']}
                  className={kind === 'alert' ? 'animate-blink' : undefined}
                />
              )
            }),
          )}
        </svg>

        {/* Pulse on the active city */}
        <span
          className="pointer-events-none absolute size-3 -translate-x-1/2 -translate-y-1/2 animate-ring rounded-full border-2 border-amber"
          style={{ left: leftPct(active.col), top: topPct(active.row) }}
          aria-hidden="true"
        />

        {/* One invisible button per city: hover OR keyboard focus shows its tooltip */}
        {CITIES.map((city) => (
          <button
            key={city.name}
            type="button"
            onMouseEnter={() => setActive(city)}
            onFocus={() => setActive(city)}
            onClick={() => setActive(city)} // touch screens have no hover
            aria-label={`${city.name}: ${t('map.alerts', { count: ACTIVITY[city.name]?.alerts ?? 0 })}`}
            className="absolute size-6 -translate-x-1/2 -translate-y-1/2 rounded-sm"
            style={{ left: leftPct(city.col), top: topPct(city.row) }}
          />
        ))}

        {/* Tooltip, placed just above the active city */}
        <div
          className="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-[calc(100%+14px)] whitespace-nowrap rounded-md border-2 border-teal-deep bg-teal-deep px-2.5 py-1.5 font-mono text-xs font-bold text-white shadow-hard-sm"
          style={{ left: leftPct(active.col), top: topPct(active.row) }}
          aria-live="polite"
        >
          {active.name}{' '}
          <span className="text-amber">{t('map.alerts', { count: activity?.alerts ?? 0 })}</span>
          {' · '}
          <span className="text-white/70">{t('map.sightings', { count: activity?.sightings ?? 0 })}</span>
        </div>
      </div>

      {/* Legend */}
      <figcaption className="label-mono mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-white/70">
        <span className="flex items-center gap-2">
          <span className="size-2.5 bg-amber" aria-hidden="true" />
          {t('map.legendAlerts')}
        </span>
        <span className="flex items-center gap-2">
          <span className="size-2.5 bg-cream" aria-hidden="true" />
          {t('map.legendSightings')}
        </span>
        <span className="text-white/45">{t('map.demo')}</span>
      </figcaption>
    </figure>
  )
}
