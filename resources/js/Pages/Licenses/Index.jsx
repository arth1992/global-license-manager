import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ licenses, filters }) {
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const { data, setData, post, processing, errors, reset } = useForm({
        client_name: '',
        client_email: '',
        max_tenants: 1,
        expires_at: '',
        features: [],
        base_fee: '',
        per_applicant_fee: '',
    });

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        router.get(route('licenses.index'), { search, status }, { preserveState: true });
    };

    const handleStatusFilterChange = (newStatus) => {
        setStatus(newStatus);
        router.get(route('licenses.index'), { search, status: newStatus }, { preserveState: true });
    };

    const handleFeatureCheckboxChange = (feature) => {
        if (data.features.includes(feature)) {
            setData('features', data.features.filter((f) => f !== feature));
        } else {
            setData('features', [...data.features, feature]);
        }
    };

    const handleCreateLicenseSubmit = (e) => {
        e.preventDefault();
        post(route('licenses.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                reset();
            },
        });
    };

    const availableFeatures = [
        { id: 'sso', name: 'Single Sign-On (SSO)' },
        { id: 'branding', name: 'Custom Branding' },
        { id: 'analytics', name: 'Advanced Analytics Dashboard' },
        { id: 'support', name: '24/7 Priority SLA Support' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white">
                            Client Licenses
                        </h2>
                        <p className="text-sm text-slate-400 mt-1">
                            Search, filter, view and register client hardware-bound licenses.
                        </p>
                    </div>
                    <button
                        onClick={() => setIsCreateOpen(true)}
                        className="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-500 hover:shadow-indigo-500/30 focus:outline-none focus:ring-2 focus:ring-indigo-600"
                    >
                        <svg className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Create License
                    </button>
                </div>
            }
        >
            <Head title="Manage Client Licenses" />

            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                {/* Search and Filters Bar */}
                <div className="rounded-xl border border-slate-800 bg-slate-900 p-5 shadow-sm">
                    <form onSubmit={handleSearchSubmit} className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div className="relative flex-1">
                            <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by name, email or domain..."
                                className="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            />
                        </div>

                        <div className="flex items-center gap-3">
                            <select
                                value={status}
                                onChange={(e) => handleStatusFilterChange(e.target.value)}
                                className="px-4 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-300 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="revoked">Revoked</option>
                            </select>

                            <button
                                type="submit"
                                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 rounded-lg text-white text-sm font-semibold transition-all focus:outline-none"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                {/* Table Section */}
                <div className="rounded-xl border border-slate-800 bg-slate-900 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse text-left">
                            <thead>
                                <tr className="border-b border-slate-800 bg-slate-900/50 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    <th className="px-6 py-4">Client Name</th>
                                    <th className="px-6 py-4">Email Address</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4">Max Tenants</th>
                                    <th className="px-6 py-4">Expiration Date</th>
                                    <th className="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800">
                                {licenses.data.length > 0 ? (
                                    licenses.data.map((lic) => (
                                        <tr key={lic.uuid} className="text-sm text-slate-300 hover:bg-slate-800/10 transition-all">
                                            <td className="px-6 py-4 font-semibold text-slate-100">
                                                {lic.client_name}
                                            </td>
                                            <td className="px-6 py-4 text-slate-400">
                                                {lic.client_email}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border ${
                                                    lic.status === 'active' 
                                                        ? 'bg-emerald-950/40 border-emerald-800 text-emerald-400'
                                                        : lic.status === 'pending'
                                                        ? 'bg-indigo-950/40 border-indigo-850 text-indigo-400'
                                                        : lic.status === 'suspended'
                                                        ? 'bg-amber-950/40 border-amber-800 text-amber-400'
                                                        : 'bg-rose-950/40 border-rose-800 text-rose-400'
                                                }`}>
                                                    <span className={`h-1.5 w-1.5 rounded-full mr-1.5 ${
                                                        lic.status === 'active' ? 'bg-emerald-400 animate-pulse' :
                                                        lic.status === 'pending' ? 'bg-indigo-400' :
                                                        lic.status === 'suspended' ? 'bg-amber-400' : 'bg-rose-400'
                                                    }`} />
                                                    {lic.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 font-mono font-medium text-slate-400">
                                                {lic.max_tenants}
                                            </td>
                                            <td className={`px-6 py-4 ${lic.is_expired ? 'text-rose-400 font-semibold' : 'text-slate-400'}`}>
                                                {lic.expires_at ? lic.expires_at.split(' ')[0] : 'Never'}
                                                {lic.is_expired && <span className="text-[10px] block mt-0.5 text-rose-500 font-normal">Expired</span>}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <Link
                                                    href={route('licenses.show', lic.uuid)}
                                                    className="inline-flex items-center rounded-lg bg-slate-800 hover:bg-slate-700 hover:text-white px-3 py-1.5 text-xs font-semibold text-slate-300 border border-slate-700 hover:border-slate-600 transition-all shadow-sm"
                                                >
                                                    View Details &rarr;
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="text-center py-12 text-slate-500">
                                            <svg className="h-12 w-12 text-slate-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                            <span className="text-sm font-medium">No client licenses found in system.</span>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination Bar */}
                    {licenses.links && licenses.links.length > 3 && (
                        <div className="flex items-center justify-between border-t border-slate-800 bg-slate-900/50 px-6 py-4">
                            <div className="text-xs text-slate-400">
                                Showing <span className="font-semibold text-slate-200">{licenses.from}</span> to{' '}
                                <span className="font-semibold text-slate-200">{licenses.to}</span> of{' '}
                                <span className="font-semibold text-slate-200">{licenses.total}</span> licenses
                            </div>
                            <div className="flex space-x-1">
                                {licenses.links.map((link, idx) => (
                                    <button
                                        key={idx}
                                        disabled={!link.url || link.active}
                                        onClick={() => router.get(link.url, { search, status }, { preserveState: true })}
                                        className={`rounded px-2.5 py-1.5 text-xs font-semibold transition-all focus:outline-none ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-slate-850 hover:bg-slate-800 text-slate-300 border border-slate-700 hover:border-slate-600'
                                                : 'bg-slate-900 text-slate-600 cursor-not-allowed opacity-50 border border-slate-800'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Create License Slide-In Modal */}
            {isCreateOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-all">
                    <div className="relative w-full max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-2xl transition-all">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                            <h3 className="text-lg font-bold text-white">Create New License</h3>
                            <button
                                onClick={() => setIsCreateOpen(false)}
                                className="rounded-lg p-1.5 text-slate-500 hover:bg-slate-800 hover:text-white transition-all focus:outline-none"
                            >
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleCreateLicenseSubmit} className="mt-4 space-y-4">
                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    Client Name
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={data.client_name}
                                    onChange={(e) => setData('client_name', e.target.value)}
                                    placeholder="e.g. Royal University of London"
                                    className="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 placeholder-slate-600 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                />
                                {errors.client_name && (
                                    <span className="text-xs text-rose-500 block mt-1">{errors.client_name}</span>
                                )}
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    Client Email Address
                                </label>
                                <input
                                    type="email"
                                    required
                                    value={data.client_email}
                                    onChange={(e) => setData('client_email', e.target.value)}
                                    placeholder="e.g. administrator@royaluniv.ac.uk"
                                    className="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 placeholder-slate-600 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                />
                                {errors.client_email && (
                                    <span className="text-xs text-rose-500 block mt-1">{errors.client_email}</span>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        Max Tenant Scopes
                                    </label>
                                    <input
                                        type="number"
                                        required
                                        min="1"
                                        value={data.max_tenants}
                                        onChange={(e) => setData('max_tenants', parseInt(e.target.value))}
                                        className="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                    />
                                    {errors.max_tenants && (
                                        <span className="text-xs text-rose-500 block mt-1">{errors.max_tenants}</span>
                                    )}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                            Custom Base Fee (Optional)
                                        </label>
                                        <div className="relative">
                                            <span className="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">₹</span>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={data.base_fee}
                                                onChange={(e) => setData('base_fee', e.target.value)}
                                                placeholder="Default: 2000"
                                                className="w-full pl-8 pr-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 placeholder-slate-600 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                            />
                                        </div>
                                        {errors.base_fee && (
                                            <span className="text-xs text-rose-500 block mt-1">{errors.base_fee}</span>
                                        )}
                                    </div>
    
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                            Custom Per-Applicant Fee
                                        </label>
                                        <div className="relative">
                                            <span className="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">₹</span>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={data.per_applicant_fee}
                                                onChange={(e) => setData('per_applicant_fee', e.target.value)}
                                                placeholder="Default: 200"
                                                className="w-full pl-8 pr-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 placeholder-slate-600 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none text-slate-300"
                                            />
                                        </div>
                                        {errors.per_applicant_fee && (
                                            <span className="text-xs text-rose-500 block mt-1">{errors.per_applicant_fee}</span>
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        Expiration Date
                                    </label>
                                    <input
                                        type="date"
                                        required
                                        value={data.expires_at}
                                        onChange={(e) => setData('expires_at', e.target.value)}
                                        className="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-lg text-slate-100 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none text-slate-300"
                                    />
                                    {errors.expires_at && (
                                        <span className="text-xs text-rose-500 block mt-1">{errors.expires_at}</span>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    Enabled Feature Toggles
                                </label>
                                <div className="space-y-2 border border-slate-800 rounded-lg bg-slate-950 p-3">
                                    {availableFeatures.map((feat) => (
                                        <label key={feat.id} className="flex items-center space-x-3 cursor-pointer text-slate-300 hover:text-white transition-all">
                                            <input
                                                type="checkbox"
                                                checked={data.features.includes(feat.id)}
                                                onChange={() => handleFeatureCheckboxChange(feat.id)}
                                                className="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-600 focus:ring-offset-slate-950"
                                            />
                                            <span className="text-xs font-semibold">{feat.name}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>

                            <div className="flex items-center justify-end space-x-3 border-t border-slate-800 pt-4 mt-6">
                                <button
                                    type="button"
                                    onClick={() => setIsCreateOpen(false)}
                                    className="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-slate-300 text-sm font-semibold transition-all focus:outline-none"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center justify-center rounded-lg bg-indigo-600 hover:bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-600/20 transition-all disabled:opacity-50 focus:outline-none"
                                >
                                    {processing ? 'Processing...' : 'Register License'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
