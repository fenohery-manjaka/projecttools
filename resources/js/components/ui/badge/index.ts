import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Badge } from "./Badge.vue"

export const badgeVariants = cva(
  "inline-flex items-center justify-center rounded-xl border border-transparent h-[23px] px-[9px] text-xs font-semibold w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-hidden",
  {
    variants: {
      variant: {
        // Pills of the prototype: a tinted background in the colour of the text.
        default:
          "bg-sunk text-ink-2 [a&]:hover:brightness-[0.97]",
        secondary:
          "bg-sunk text-muted-foreground",
        destructive:
         "bg-bad/13 text-bad focus-visible:ring-destructive/20",
        outline:
          "border-border text-foreground [a&]:hover:bg-sunk",
        ok: "bg-ok/13 text-ok",
        warn: "bg-warn/14 text-warn",
        bad: "bg-bad/13 text-bad",
        info: "bg-info/13 text-info",
        accent: "bg-accent text-brand-ink",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  },
)
export type BadgeVariants = VariantProps<typeof badgeVariants>
