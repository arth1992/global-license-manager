import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ license, activations, logs }) {
    const [copied, setCopied] = useState(false);
    const [updatingStatus, setUpdatingStatus] = useState(false);
    const [resettingLicense, setResettingLicense] = useState(false);
    const [deletingClient, setDeletingClient] = useState(false);

    const { post, processing: generatingKey } = useForm();

    const handleCopyKey = () => {
        if (!license.license_key) return;
        navigator.clipboard.writeText(license.license_key);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const handleGenerateKey = () => {
        post(route('licenses.generate-key', license.uuid));
    };

    const handleStatusChange = (status) => {
        router.patch(route('licenses.status', license.uuid), { status }, {
            onStart: () => setUpdatingStatus(true),
            onFinish: () => setUpdatingStatus(false),
        });
    };

    const handleResetLicense = () => {
        if (!confirm('Are you sure you want to delete this client\'s license key and clear all machine/domain fingerprint bindings? This will reset the status to pending and force the client to reactivate.')) {
            return;
        }

        router.post(route('licenses.reset', license.uuid), {}, {
            onStart: () => setResettingLicense(true),
            onFinish: () => setResettingLicense(false),
        });
    };

    const handleDeleteClient = () => {
        if (!confirm('WARNING: Are you sure you want to completely delete this client and all associated license records, daily ping logs, and activation handshakes? This action is permanent and cannot be undone.')) {
            return;
        }

        router.delete(route('licenses.destroy', license.uuid), {
            onStart: () => setDeletingClient(true),
            onFinish: () => setDeletingClient(false),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div className="flex items-center space-x-3">
                        <Link
                            href={route('licenses.index')}
                            className="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-850 transition-all focus:outline-none"
                        >
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                                {license.client_name}
                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border ${
                                    license.status === 'active' 
                                        ? 'bg-emerald-950/40 border-emerald-800 text-emerald-400'
                                        : license.status === 'pending'
                                        ? 'bg-indigo-950/40 border-indigo-850 text-indigo-400'
                                        : license.status === 'suspended'
                                        ? 'bg-amber-950/40 border-amber-800 text-amber-400'
                                        : 'bg-rose-950/40 border-rose-800 text-rose-400'
                                }`}>
                                    {license.status}
                                </span>
                            </h2>
                            <p className="text-xs text-slate-400 mt-1 font-mono">
                                License ID: {license.uuid}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {license.status !== 'active' && (
                            <button
                                onClick={() => handleStatusChange('active')}
                                disabled={updatingStatus}
                                className="inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-500 px-3.5 py-2 text-sm font-semibold text-white transition-all disabled:opacity-50 focus:outline-none"
                            >
                                Activate
                            </button>
                        )}
                        {license.status !== 'suspended' && (
                            <button
                                onClick={() => handleStatusChange('suspended')}
                                disabled={updatingStatus}
                                className="inline-flex items-center justify-center rounded-lg bg-amber-600 hover:bg-amber-500 px-3.5 py-2 text-sm font-semibold text-white transition-all disabled:opacity-50 focus:outline-none"
                            >
                                Suspend
                            </button>
                        )}
                        {license.status !== 'revoked' && (
                            <button
                                onClick={() => handleStatusChange('revoked')}
                                disabled={updatingStatus}
                                className="inline-flex items-center justify-center rounded-lg bg-rose-600 hover:bg-rose-500 px-3.5 py-2 text-sm font-semibold text-white transition-all disabled:opacity-50 focus:outline-none"
                            >
                                Revoke
                            </button>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`License - ${license.client_name}`} />

            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left Hand: Core Details + Cryptographic Key Generator */}
                    <div className="lg:col-span-1 space-y-8">
                        {/* Core Details Panel */}
                        <div className="rounded-xl border border-slate-800 bg-slate-900 shadow-sm overflow-hidden">
                            <div className="border-b border-slate-800 bg-slate-900/50 px-6 py-4">
                                <h3 className="font-bold text-white text-base">Key Properties</h3>
                            </div>
                            <div className="p-6 space-y-4 text-sm">
                                <div>
                                    <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Client Email</span>
                                    <p className="text-slate-200 mt-0.5 font-medium">{license.client_email}</p>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Max Tenant Scopes</span>
                                        <p className="text-slate-200 mt-0.5 font-mono">{license.max_tenants}</p>
                                    </div>
                                    <div>
                                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Expiration Date</span>
                                        <p className={`mt-0.5 ${license.is_expired ? 'text-rose-400 font-semibold' : 'text-slate-200 font-medium'}`}>
                                            {license.expires_at ? license.expires_at.split(' ')[0] : 'Never'}
                                        </p>
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Locked Domain</span>
                                        <p className="text-slate-300 mt-0.5 font-medium truncate" title={license.domain || 'Not activated'}>
                                            {license.domain || <span className="text-slate-600 text-xs italic">Pending Binding</span>}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Locked Fingerprint</span>
                                        <p className="text-slate-300 mt-0.5 font-mono truncate" title={license.fingerprint || 'Not activated'}>
                                            {license.fingerprint ? `${license.fingerprint.substring(0, 10)}...` : <span className="text-slate-600 text-xs italic">Pending Binding</span>}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">Activated Timestamp</span>
                                    <p className="text-slate-300 mt-0.5 font-medium">
                                        {license.activated_at || <span className="text-slate-600 text-xs italic">Unactivated</span>}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Enabled Features</span>
                                    <div className="flex flex-wrap gap-1.5">
                                        {license.features && license.features.length > 0 ? (
                                            license.features.map((feat) => (
                                                <span key={feat} className="rounded-md border border-slate-700 bg-slate-800/80 px-2 py-0.5 text-xs font-semibold text-slate-300">
                                                    {feat}
                                                </span>
                                            ))
                                        ) : (
                                            <span className="text-slate-600 text-xs italic">No special features enabled</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* License Key Display Section */}
                        <div className="rounded-xl border border-slate-800 bg-slate-900 shadow-sm overflow-hidden">
                            <div className="border-b border-slate-800 bg-slate-900/50 px-6 py-4 flex items-center justify-between">
                                <h3 className="font-bold text-white text-base">Cryptographic License Key</h3>
                                {license.license_key && (
                                    <button
                                        onClick={handleCopyKey}
                                        className="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center focus:outline-none"
                                    >
                                        {copied ? (
                                            <span className="text-emerald-400 flex items-center animate-pulse">
                                                <svg className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Copied!
                                            </span>
                                        ) : (
                                            <span className="flex items-center">
                                                <svg className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                </svg>
                                                Copy Key
                                            </span>
                                        )}
                                    </button>
                                )}
                            </div>
                            <div className="p-6 space-y-4">
                                {license.license_key ? (
                                    <div>
                                        <div className="rounded-lg bg-slate-950 border border-slate-850 p-4 font-mono text-[10px] text-indigo-300/80 leading-relaxed break-all select-all h-48 overflow-y-auto shadow-inner">
                                            {license.license_key}
                                        </div>
                                        <button
                                            onClick={handleGenerateKey}
                                            disabled={generatingKey}
                                            className="w-full mt-4 inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800 hover:bg-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition-all disabled:opacity-50"
                                        >
                                            {generatingKey ? 'Re-signing...' : 'Re-generate Cryptographic Key'}
                                        </button>
                                    </div>
                                ) : (
                                    <div className="text-center py-6">
                                        <svg className="h-10 w-10 text-slate-700 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <p className="text-xs text-slate-400 font-medium">
                                            No cryptographic key signed yet.
                                        </p>
                                        <button
                                            onClick={handleGenerateKey}
                                            disabled={generatingKey}
                                            className="w-full mt-4 inline-flex items-center justify-center rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition-all disabled:opacity-50"
                                        >
                                            {generatingKey ? 'Signing...' : 'Sign & Generate License Key'}
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Danger Zone Panel */}
                        <div className="rounded-xl border border-rose-950 bg-rose-950/10 shadow-sm overflow-hidden">
                            <div className="border-b border-rose-950 bg-rose-950/20 px-6 py-4">
                                <h3 className="font-bold text-rose-400 text-base">Danger Zone</h3>
                            </div>
                            <div className="p-6 space-y-4 text-sm">
                                <div>
                                    <h4 className="font-semibold text-slate-200">Reset Machine Bindings & Key</h4>
                                    <p className="text-xs text-slate-400 mt-1">
                                        This will delete the cryptographic license key, and clear all domain and hardware fingerprint bindings. The client status resets to "pending" and they must reactivate.
                                    </p>
                                    <button
                                        onClick={handleResetLicense}
                                        disabled={resettingLicense}
                                        className="mt-3 inline-flex items-center justify-center rounded-lg border border-amber-800 bg-amber-950/25 hover:bg-amber-900/40 px-3 py-1.5 text-xs font-semibold text-amber-400 transition-all focus:outline-none"
                                    >
                                        {resettingLicense ? 'Resetting...' : 'Reset License Key & Bindings'}
                                    </button>
                                </div>
                                <div className="border-t border-rose-950/40 pt-4">
                                    <h4 className="font-semibold text-slate-200">Delete Client Entirely</h4>
                                    <p className="text-xs text-slate-400 mt-1">
                                        Permanently delete this client, their license key record, all logs, and activation handshakes from the central manager. This action cannot be undone.
                                    </p>
                                    <button
                                        onClick={handleDeleteClient}
                                        disabled={deletingClient}
                                        className="mt-3 inline-flex items-center justify-center rounded-lg bg-rose-700 hover:bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition-all focus:outline-none shadow-md shadow-rose-900/30"
                                    >
                                        {deletingClient ? 'Deleting...' : 'Delete Client & Data'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Hand: Handshake activation list + Audit timeline */}
                    <div className="lg:col-span-2 space-y-8">
                        {/* Handshake activations requests table */}
                        <div className="rounded-xl border border-slate-800 bg-slate-900 shadow-sm overflow-hidden">
                            <div className="border-b border-slate-800 bg-slate-900/50 px-6 py-4">
                                <h3 className="font-bold text-white text-base">Pending & Historical Activations Handshakes</h3>
                                <p className="text-xs text-slate-400 mt-0.5">
                                    Requests generated by client wizard. Administrators can view matching Request IDs.
                                </p>
                            </div>
                            <div className="overflow-x-auto max-h-[300px] overflow-y-auto">
                                <table className="w-full text-left border-collapse">
                                    <thead>
                                        <tr className="border-b border-slate-800 bg-slate-900/30 text-xs font-bold uppercase tracking-wider text-slate-400">
                                            <th className="px-6 py-3">Request ID</th>
                                            <th className="px-6 py-3">Locked Domain</th>
                                            <th className="px-6 py-3">Client IP</th>
                                            <th className="px-6 py-3">Expiry Date</th>
                                            <th className="px-6 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-800">
                                        {activations.length > 0 ? (
                                            activations.map((act) => (
                                                <tr key={act.id} className="text-xs text-slate-300">
                                                    <td className="px-6 py-3 font-semibold font-mono text-indigo-400 select-all">
                                                        {act.request_id}
                                                    </td>
                                                    <td className="px-6 py-3 truncate max-w-[150px]" title={act.domain}>
                                                        {act.domain}
                                                    </td>
                                                    <td className="px-6 py-3 font-mono text-slate-400">
                                                        {act.ip_address}
                                                    </td>
                                                    <td className="px-6 py-3 text-slate-400">
                                                        {act.expires_at ? act.expires_at.split(' ')[0] : 'Never'}
                                                    </td>
                                                    <td className="px-6 py-3">
                                                        <span className={`inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold border ${
                                                            act.status === 'used'
                                                                ? 'bg-emerald-950/40 border-emerald-800 text-emerald-450'
                                                                : act.status === 'pending'
                                                                ? 'bg-indigo-950/40 border-indigo-850 text-indigo-400'
                                                                : 'bg-slate-800 border-slate-700 text-slate-400'
                                                        }`}>
                                                            {act.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="5" className="text-center py-6 text-slate-600 text-xs italic">
                                                    No activation handshake requests found for this license key.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {/* Audit Trail Timeline */}
                        <div className="rounded-xl border border-slate-800 bg-slate-900 shadow-sm overflow-hidden">
                            <div className="border-b border-slate-800 bg-slate-900/50 px-6 py-4">
                                <h3 className="font-bold text-white text-base">Key Audit Log Timeline</h3>
                                <p className="text-xs text-slate-400 mt-0.5">
                                    Full audit history of manual overrides, code generation, and client daily verification calls.
                                </p>
                            </div>
                            <div className="p-6 divide-y divide-slate-800/60 max-h-[300px] overflow-y-auto">
                                {logs.length > 0 ? (
                                    logs.map((log) => (
                                        <div key={log.id} className="py-3 flex items-start justify-between text-xs transition-all hover:bg-slate-850/10">
                                            <div className="flex items-start space-x-2.5">
                                                <span className={`mt-1 flex h-2 w-2 shrink-0 rounded-full ring-2 ${
                                                    log.is_success 
                                                        ? 'bg-emerald-500 ring-emerald-950' 
                                                        : 'bg-rose-500 ring-rose-950'
                                                }`} />
                                                <div>
                                                    <div className="flex items-center space-x-2">
                                                        <span className="font-bold text-slate-200 capitalize">
                                                            {log.event.replace('_', ' ')}
                                                        </span>
                                                        <span className="text-[10px] text-slate-500">
                                                            IP: {log.ip_address || '127.0.0.1'}
                                                        </span>
                                                    </div>
                                                    <p className="text-slate-400 mt-0.5">{log.notes}</p>
                                                </div>
                                            </div>
                                            <span className="text-slate-500 font-mono text-[10px]">{log.created_at}</span>
                                        </div>
                                    ))
                                ) : (
                                    <div className="text-center py-6 text-slate-600 italic">
                                        No audit history logs recorded.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
