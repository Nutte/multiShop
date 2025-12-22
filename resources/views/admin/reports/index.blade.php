<!-- FILE: resources/views/admin/reports/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Financial Reports')

@section('content')
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Financial Performance</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded shadow text-white">
            <div class="text-xs uppercase font-bold opacity-75">Total Revenue</div>
            <div class="text-4xl font-bold mt-2">${{ number_format($grandTotal, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Avg. Check</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Revenue</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($reportData as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700">
                            {{ $row['tenant'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $row['orders_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($row['average_check'], 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-green-600">
                            ${{ number_format($row['total_revenue'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection