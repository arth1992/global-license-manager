import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={`flex w-full items-start border-l-4 py-2 pe-4 ps-3 ${
                active
                    ? 'border-indigo-500 bg-indigo-500/10 text-indigo-400 focus:border-indigo-400 focus:bg-indigo-500/20 focus:text-indigo-300'
                    : 'border-transparent text-slate-400 hover:border-slate-700 hover:bg-slate-800/50 hover:text-slate-200 focus:border-slate-700 focus:bg-slate-800/50 focus:text-slate-200'
            } text-base font-medium transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
