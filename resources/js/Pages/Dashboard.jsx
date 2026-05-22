import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ stats, recentLogs }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white">
                            Dashboard Overview
                        </h2>
                        <p className="text-sm text-slate-400 mt-1">
                            Real-time statistics and cryptographic license activations feed.
                        </p>
                    </div>
                    <Link
                        href={route('licenses.index')}
                        className="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    >
                        <svg className="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Manage Licenses
                    </Link>
                </div>
            }
        >
            <Head title="Dashboard Overview" />

            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                {/* Metric Cards Grid */}
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Card 1: Total Licenses */}
                    <div className="relative overflow-hidden rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-slate-700 hover:shadow-md hover:shadow-indigo-600/5 group">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold text-slate-400">Total Licenses</span>
                            <div className="rounded-lg bg-slate-800 p-2 text-indigo-400 group-hover:bg-indigo-600/10 group-hover:text-indigo-300 transition-all">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-4">
                            <span className="text-3xl font-bold text-white tracking-tight">{stats.total}</span>
                            <p className="text-xs text-slate-500 mt-1">Issued client applications</p>
                        </div>
                    </div>

                    {/* Card 2: Active Licenses */}
                    <div className="relative overflow-hidden rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-slate-700 hover:shadow-md hover:shadow-emerald-600/5 group">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold text-slate-400">Active Keys</span>
                            <div className="rounded-lg bg-slate-800 p-2 text-emerald-400 group-hover:bg-emerald-600/10 group-hover:text-emerald-300 transition-all">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-4">
                            <span className="text-3xl font-bold text-white tracking-tight">{stats.active}</span>
                            <p className="text-xs text-slate-500 mt-1">Currently verified & online</p>
                        </div>
                    </div>

                    {/* Card 3: Suspended Licenses */}
                    <div className="relative overflow-hidden rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-slate-700 hover:shadow-md hover:shadow-rose-600/5 group">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold text-slate-400">Suspended Keys</span>
                            <div className="rounded-lg bg-slate-800 p-2 text-rose-400 group-hover:bg-rose-600/10 group-hover:text-rose-300 transition-all">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-4">
                            <span className="text-3xl font-bold text-white tracking-tight">{stats.suspended}</span>
                            <p className="text-xs text-slate-500 mt-1">Revoked or locked clients</p>
                        </div>
                    </div>

                    {/* Card 4: Expiring in 30 Days */}
                    <div className="relative overflow-hidden rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm transition-all hover:-translate-y-1 hover:border-slate-700 hover:shadow-md hover:shadow-amber-600/5 group">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold text-slate-400">Expiring Soon</span>
                            <div className="rounded-lg bg-slate-800 p-2 text-amber-400 group-hover:bg-amber-600/10 group-hover:text-amber-300 transition-all">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-4">
                            <span className="text-3xl font-bold text-white tracking-tight">{stats.expiring}</span>
                            <p className="text-xs text-slate-500 mt-1">Expiring within 30 days</p>
                        </div>
                    </div>
                </div>

                {/* Audit Timeline / Recent Activity */}
                <div className="rounded-xl border border-slate-800 bg-slate-900 shadow-sm overflow-hidden">
                    <div className="border-b border-slate-800 bg-slate-900/50 px-6 py-5">
                        <h3 className="text-lg font-bold text-white">Live Activity Stream</h3>
                        <p className="text-xs text-slate-400 mt-1">
                            Timeline of recent daily check-in logs and cryptographic activation events.
                        </p>
                    </div>

                    <div className="divide-y divide-slate-800 max-h-[500px] overflow-y-auto">
                        {recentLogs.length > 0 ? (
                            recentLogs.map((log) => (
                                <div key={log.id} className="p-6 transition-all hover:bg-slate-800/20">
                                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                        <div className="flex items-start space-x-3">
                                            {/* Success/Fail Indicator Dot */}
                                            <span className={`mt-1.5 flex h-2.5 w-2.5 shrink-0 rounded-full ring-4 ${
                                                log.is_success 
                                                    ? 'bg-emerald-500 ring-emerald-950' 
                                                    : 'bg-rose-500 ring-rose-950'
                                            }`} />
                                            <div>
                                                <div className="flex items-center space-x-2">
                                                    <span className="font-semibold text-slate-200">
                                                        {log.client_name}
                                                    </span>
                                                    <span className={`inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium border ${
                                                        log.event === 'activated' 
                                                            ? 'bg-indigo-950 border-indigo-800 text-indigo-300'
                                                            : log.event === 'daily_ping' 
                                                            ? 'bg-slate-800 border-slate-700 text-slate-300'
                                                            : 'bg-slate-800 border-slate-700 text-slate-300'
                                                    }`}>
                                                        {log.event.replace('_', ' ')}
                                                    </span>
                                                </div>
                                                <p className="text-sm text-slate-400 mt-1">{log.notes}</p>
                                                <div className="flex items-center space-x-4 text-xs text-slate-500 mt-2">
                                                    <span className="flex items-center">
                                                        <svg className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                                                        </svg>
                                                        IP: {log.ip_address}
                                                    </span>
                                                    {log.fingerprint && (
                                                        <span className="flex items-center">
                                                            <svg className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11.75c0-.776-.08-1.547-.235-2.292m0 0a17.96 17.96 0 013.374-3.597m1.125-1.197a17.964 17.964 0 013.374 3.597m0 0A19.797 19.797 0 0118 11.75c0 2.05-.333 4.01-1.002 5.992m-4.014-5.993c.123-.74.186-1.5.186-2.275 0-2.861-2.009-5.185-4.5-5.185S4 6.639 4 9.5c0 .775.063 1.536.186 2.275m5.814-2.275a2.5 2.5 0 00-5 0v1.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V9.5z" />
                                                            </svg>
                                                            FP: {log.fingerprint}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-right flex sm:flex-col items-center justify-between sm:justify-start space-x-2 sm:space-x-0">
                                            <span className="text-xs text-slate-500">{log.created_at}</span>
                                            {log.uuid && (
                                                <Link
                                                    href={route('licenses.show', log.uuid)}
                                                    className="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-all mt-1"
                                                >
                                                    View License &rarr;
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="flex flex-col items-center justify-center py-12 text-slate-500">
                                <svg className="h-10 w-10 text-slate-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span className="text-sm">No activity logs recorded yet.</span>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
