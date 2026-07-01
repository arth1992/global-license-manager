import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Index({ auth, settings }) {
    const { data, setData, post, processing, errors } = useForm({
        razorpay_key_id: settings?.razorpay_key_id || '',
        razorpay_key_secret: settings?.razorpay_key_secret || '',
        bank_details: settings?.bank_details || '',
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
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Global Payment Settings</h2>}
        >
            <Head title="Payment Settings" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    
                    {toast && (
                        <div className={`mb-6 p-4 rounded border ${toast.type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'}`}>
                            {toast.message}
                        </div>
                    )}

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h3 className="text-lg font-bold text-gray-900 mb-6">Razorpay & Billing Configuration</h3>
                            
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Razorpay Key ID</label>
                                        <input 
                                            type="text" 
                                            value={data.razorpay_key_id}
                                            onChange={e => setData('razorpay_key_id', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="rzp_live_..."
                                        />
                                        {errors.razorpay_key_id && <p className="text-sm text-red-600">{errors.razorpay_key_id}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Razorpay Key Secret</label>
                                        <input 
                                            type="password" 
                                            value={data.razorpay_key_secret}
                                            onChange={e => setData('razorpay_key_secret', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="••••••••••••••••"
                                        />
                                        {errors.razorpay_key_secret && <p className="text-sm text-red-600">{errors.razorpay_key_secret}</p>}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="block text-sm font-medium text-gray-700">Manual Bank Transfer Details</label>
                                    <textarea 
                                        rows="4"
                                        value={data.bank_details}
                                        onChange={e => setData('bank_details', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="Bank Name: X&#10;Account Number: Y&#10;Routing: Z"
                                    ></textarea>
                                    <p className="text-xs text-gray-500">This text will be printed at the bottom of all generated invoice PDFs.</p>
                                    {errors.bank_details && <p className="text-sm text-red-600">{errors.bank_details}</p>}
                                </div>

                                <div className="pt-4 border-t border-gray-200">
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
