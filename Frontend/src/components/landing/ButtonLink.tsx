import type { AnchorHTMLAttributes, ReactNode } from 'react'

// One reusable "button-looking link" with variants, instead of copying
// long className strings everywhere. Change a style here → it changes everywhere.
type Variant = 'primary' | 'secondary' | 'onDark'
type Size = 'md' | 'lg'

// Every variant shares the "hard shadow" look: a solid block offset by 4px.
// On press, the button moves INTO its shadow (translate + smaller shadow) → feels physical.
// Record<Variant, string> = exactly one style per variant (TypeScript checks it).
const VARIANTS: Record<Variant, string> = {
  // deep amber = the main action (report a disappearance)
  primary: 'border-teal-deep bg-amber-deep text-white hover:bg-[#a9580c]',
  // white = secondary action on light backgrounds
  secondary: 'border-teal-deep bg-white text-teal-deep hover:bg-cream',
  // on the teal hero: white outline, teal text inside
  onDark: 'border-teal-deep bg-cream text-teal-deep hover:bg-white',
}

const SIZES: Record<Size, string> = {
  md: 'h-10 px-4 text-xs',
  lg: 'h-12 px-6 text-sm',
}

type ButtonLinkProps = AnchorHTMLAttributes<HTMLAnchorElement> & {
  variant?: Variant
  size?: Size
  children: ReactNode
}

export default function ButtonLink({
  variant = 'primary',
  size = 'md',
  className = '',
  children,
  ...rest // everything else (href, onClick…) is passed to <a>
}: ButtonLinkProps) {
  return (
    <a
      className={`inline-flex items-center justify-center gap-2 rounded-md border-2 font-mono font-bold uppercase tracking-wide shadow-hard transition-[transform,box-shadow,background-color] duration-100 ease-out hover:-translate-x-px hover:-translate-y-px active:translate-x-1 active:translate-y-1 active:shadow-none motion-reduce:transform-none ${VARIANTS[variant]} ${SIZES[size]} ${className}`}
      {...rest}
    >
      {children}
    </a>
  )
}
