import { useEffect, useRef, useState, type ReactNode } from 'react'

type RevealProps = {
  children: ReactNode
  delay?: number // in ms: lets cards in a grid appear one after another
  className?: string
}

// Fades + slides its content in the first time it scrolls into view.
// The CSS lives in index.css (.reveal and .reveal[data-visible="true"]).
export default function Reveal({ children, delay = 0, className = '' }: RevealProps) {
  const ref = useRef<HTMLDivElement>(null) // gives us the real DOM <div>
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    const element = ref.current
    if (!element) return

    // IntersectionObserver = the browser tells us when the element enters the screen
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true)
          observer.disconnect() // only once: no blinking when scrolling back up
        }
      },
      { threshold: 0.15 }, // 15% of the element must be visible
    )

    observer.observe(element)
    return () => observer.disconnect() // cleanup when the component unmounts
  }, [])

  return (
    <div
      ref={ref}
      data-visible={visible}
      style={{ transitionDelay: `${delay}ms` }}
      className={`reveal ${className}`}
    >
      {children}
    </div>
  )
}
