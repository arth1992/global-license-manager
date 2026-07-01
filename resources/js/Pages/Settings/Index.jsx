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
    });

    const [toast, setToast] = useState(null);

    useEffect(() => {
        if (toast) {
            const timer = setTimeout(() => setToast(null), 4000);
            return () => clearTimeout(timer);
        }
    }, [toast]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('settings.update'), {
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
            header={<h2 className="font-semibold text-xl text-slate-100 leading-tight">Global Payment Settings</h2>}
        >
            <Head title="Payment Settings" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    
                    {toast && (
                        <div className={`mb-6 p-4 rounded border ${toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'}`}>
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
