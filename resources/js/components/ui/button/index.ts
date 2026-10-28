import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[10px] text-sm font-semibold transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground hover:brightness-[1.12]",
        destructive:
          "bg-bad text-white hover:brightness-[1.08] focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40",
        outline:
          "border border-border bg-card text-foreground hover:border-line-2",
        secondary:
          "bg-sunk text-foreground hover:brightness-[0.97]",
        ghost:
          "text-ink-2 hover:bg-sunk hover:text-foreground",
        link: "text-brand-ink underline-offset-4 hover:underline",
        accent:
          "bg-brand text-white hover:brightness-[1.08]",
      },
      size: {
        "default": "h-[38px] px-[15px] has-[>svg]:px-3",
        "sm": "h-8 rounded-lg gap-1.5 px-[11px] text-[13px] has-[>svg]:px-2.5",
        "lg": "h-10 px-6 has-[>svg]:px-4",
        "icon": "size-9 text-ink-2",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
