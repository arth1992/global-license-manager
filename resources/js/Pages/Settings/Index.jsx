import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Index({ auth, settings }) {
    const { data, setData, post, processing, errors } = useForm({
        razorpay_key_id: settings?.razorpay_key_id || '',
        razorpay_key_secret: settings?.razorpay_key_secret || '',
        bank_details: settings?.bank_details || '',
        brand_color: settings?.brand_color || '#0f172a',
        logo: null,
        smtp_host: settings?.smtp_host || '',
        smtp_port: settings?.smtp_port || '',
        smtp_username: settings?.smtp_username || '',
        smtp_password: settings?.smtp_password || '',
        smtp_encryption: settings?.smtp_encryption || '',
        smtp_from_address: settings?.smtp_from_address || '',
        smtp_from_name: settings?.smtp_from_name || '',
    });

    const [toast, setToast] = useState(null);
    const [sendingTest, setSendingTest] = useState(false);

    const handleSendTestEmail = async () => {
        setSendingTest(true);
        setToast(null);
        try {
            const response = await fetch(route('settings.test-email'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            const res = await response.json();
            if (response.ok && res.status === 'success') {
                setToast({ type: 'success', message: res.message });
            } else {
                setToast({ type: 'error', message: res.message || 'SMTP connection verification failed.' });
            }
        } catch (error) {
            setToast({ type: 'error', message: error.message || 'SMTP connection timed out or network error.' });
        } finally {
            setSendingTest(false);
        }
    };

    useEffect(() => {
        if (toast) {
            const timer = setTimeout(() => setToast(null), 4000);
            return () => clearTimeout(timer);
        }
    }, [toast]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('settings.update'), {
            preserveScroll: true,
            onSuccess: () => {
                setToast({ type: 'success', message: 'Settings saved successfully.' });
            },
            onError: () => {
                setToast({ type: 'error', message: 'Failed to update settings.' });
            }
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-slate-100 leading-tight">Global Payment & System Settings</h2>}
        >
            <Head title="System Settings" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {toast && (
                        <div className={`p-4 rounded border ${toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'}`}>
                            {toast.message}
                        </div>
                    )}

                    <div className="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg border border-slate-800">
                        <div className="p-6 bg-slate-900 border-b border-slate-800">
                            <h3 className="text-lg font-bold text-white mb-6">Razorpay & Billing Configuration</h3>
                            
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-slate-300">Razorpay Key ID</label>
                                        <input 
                                            type="text" 
                                            value={data.razorpay_key_id}
                                            onChange={e => setData('razorpay_key_id', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="rzp_live_..."
                                        />
                                        {errors.razorpay_key_id && <p className="text-sm text-red-500">{errors.razorpay_key_id}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-slate-300">Razorpay Key Secret</label>
                                        <input 
                                            type="password" 
                                            value={data.razorpay_key_secret}
                                            onChange={e => setData('razorpay_key_secret', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="••••••••••••••••"
                                        />
                                        {errors.razorpay_key_secret && <p className="text-sm text-red-500">{errors.razorpay_key_secret}</p>}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="block text-sm font-medium text-slate-300">Manual Bank Transfer Details</label>
                                    <textarea 
                                        rows="4"
                                        value={data.bank_details}
                                        onChange={e => setData('bank_details', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="Bank Name: X&#10;Account Number: Y&#10;Routing: Z"
                                    ></textarea>
                                    <p className="text-xs text-slate-500">This text will be printed at the bottom of all generated invoice PDFs.</p>
                                    {errors.bank_details && <p className="text-sm text-red-500">{errors.bank_details}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="block text-sm font-medium text-slate-300">Brand Color</label>
                                    <div className="flex items-center space-x-3">
                                        <input
                                            type="color"
                                            value={data.brand_color}
                                            onChange={e => setData('brand_color', e.target.value)}
                                            className="h-9 w-14 rounded border border-slate-700 bg-slate-800 p-0.5 cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            value={data.brand_color}
                                            onChange={e => setData('brand_color', e.target.value)}
                                            className="block w-32 rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                    <p className="text-xs text-slate-500">Hex color code for invoice headers.</p>
                                    {errors.brand_color && <p className="text-sm text-red-500">{errors.brand_color}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="block text-sm font-medium text-slate-300">Invoice Logo</label>
                                    <div className="flex items-center space-x-4">
                                        {settings?.logo_url && !data.logo && (
                                            <img src={`/storage/${settings.logo_url}`} alt="Current Logo" className="h-16 w-auto object-contain rounded bg-white p-1" />
                                        )}
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={e => setData('logo', e.target.files[0])}
                                            className="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-900 file:text-indigo-300 hover:file:bg-indigo-800"
                                        />
                                    </div>
                                    <p className="text-xs text-slate-500">Upload a company logo for the top of your invoices.</p>
                                    {errors.logo && <p className="text-sm text-red-500">{errors.logo}</p>}
                                </div>

                                <div className="pt-8 mt-8 border-t border-slate-800">
                                    <div className="flex justify-between items-center mb-6">
                                        <div>
                                            <h3 className="text-lg font-bold text-white">Email Configuration (SMTP)</h3>
                                            <p className="text-sm text-slate-400">Configure how invoices are emailed to your clients.</p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={handleSendTestEmail}
                                            disabled={sendingTest || !data.smtp_host || !data.smtp_from_address}
                                            className="inline-flex justify-center py-2 px-4 border border-slate-700 shadow-sm text-xs font-semibold rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white disabled:opacity-50"
                                        >
                                            {sendingTest ? 'Sending Test...' : 'Send Test Email'}
                                        </button>
                                    </div>
                                    
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">SMTP Host</label>
                                            <input 
                                                type="text" 
                                                value={data.smtp_host}
                                                onChange={e => setData('smtp_host', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="smtp.mailtrap.io"
                                            />
                                            {errors.smtp_host && <p className="text-sm text-red-500">{errors.smtp_host}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">SMTP Port</label>
                                            <input 
                                                type="text" 
                                                value={data.smtp_port}
                                                onChange={e => setData('smtp_port', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="2525"
                                            />
                                            {errors.smtp_port && <p className="text-sm text-red-500">{errors.smtp_port}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">SMTP Username</label>
                                            <input 
                                                type="text" 
                                                value={data.smtp_username}
                                                onChange={e => setData('smtp_username', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                            {errors.smtp_username && <p className="text-sm text-red-500">{errors.smtp_username}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">SMTP Password</label>
                                            <input 
                                                type="password" 
                                                value={data.smtp_password}
                                                onChange={e => setData('smtp_password', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                            {errors.smtp_password && <p className="text-sm text-red-500">{errors.smtp_password}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">SMTP Encryption</label>
                                            <select
                                                value={data.smtp_encryption}
                                                onChange={e => setData('smtp_encryption', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option value="">None</option>
                                                <option value="tls">TLS</option>
                                                <option value="ssl">SSL</option>
                                            </select>
                                            {errors.smtp_encryption && <p className="text-sm text-red-500">{errors.smtp_encryption}</p>}
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">From Address</label>
                                            <input 
                                                type="email" 
                                                value={data.smtp_from_address}
                                                onChange={e => setData('smtp_from_address', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="billing@yourcompany.com"
                                            />
                                            {errors.smtp_from_address && <p className="text-sm text-red-500">{errors.smtp_from_address}</p>}
                                        </div>
                                        <div className="space-y-2">
                                            <label className="block text-sm font-medium text-slate-300">From Name</label>
                                            <input 
                                                type="text" 
                                                value={data.smtp_from_name}
                                                onChange={e => setData('smtp_from_name', e.target.value)}
                                                className="mt-1 block w-full rounded-md border-slate-700 bg-slate-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Global Admission Manager"
                                            />
                                            {errors.smtp_from_name && <p className="text-sm text-red-500">{errors.smtp_from_name}</p>}
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-4 border-t border-slate-800">
                                    <button 
                                        type="submit" 
                                        disabled={processing}
                                        className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                    >
                                        {processing ? 'Saving...' : 'Save Configuration'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
