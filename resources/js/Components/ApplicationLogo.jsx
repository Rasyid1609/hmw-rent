import { Link } from "@inertiajs/react";
import { IconInnerShadowBottomRight } from "@tabler/icons-react";
import { cn } from "@/lib/utils";

export default function ApplicationLogo({ url = "#", size =
    'size-9', isTitle = true
}) {
    return (
        <Link href={url} className="flex items-center gap-2">
            <IconInnerShadowBottomRight className={cn('text-orange-500', size)} />
            {isTitle && (
                <div className="flex flex-col">
                    <span className="font-bold leading-none text-foreground text-2xl">HMRent</span>
                </div>
            )}
        </Link>
    );
}
