<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreePBX Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8" id="app">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">FreePBX Dashboard</h1>
            <p class="text-gray-600 mt-2">Real-time agents and queues monitoring</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Queues</p>
                        <p class="text-3xl font-bold text-gray-800" id="total-queues">-</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Available Agents</p>
                        <p class="text-3xl font-bold text-green-600" id="available-agents">-</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Busy Agents</p>
                        <p class="text-3xl font-bold text-yellow-600" id="busy-agents">-</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Waiting Calls</p>
                        <p class="text-3xl font-bold text-red-600" id="waiting-calls">-</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queues Table -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Queue Status</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waiting</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Wait Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agents Available</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Level</th>
                        </tr>
                    </thead>
                    <tbody id="queues-table" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Agents Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Agent Status</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extension</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queues</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calls Taken</th>
                        </tr>
                    </thead>
                    <tbody id="agents-table" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const refreshInterval = {{ config('freepbx.dashboard.refresh_interval', 5) }} * 1000;

        function fetchDashboardData() {
            axios.get('/api/freepbx/dashboard')
                .then(response => {
                    updateDashboard(response.data);
                })
                .catch(error => {
                    console.error('Error fetching dashboard data:', error);
                });
        }

        function updateDashboard(data) {
            // Update summary cards
            document.getElementById('total-queues').textContent = data.summary.total_queues;
            document.getElementById('available-agents').textContent = data.summary.available_agents;
            document.getElementById('busy-agents').textContent = data.summary.busy_agents;
            document.getElementById('waiting-calls').textContent = data.summary.total_waiting_calls;

            // Update queues table
            const queuesTable = document.getElementById('queues-table');
            queuesTable.innerHTML = data.queues.map(queue => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${queue.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${queue.waiting_calls}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${queue.average_wait_time}s</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${queue.agents_available}/${queue.agents_total}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${queue.service_level}%</td>
                </tr>
            `).join('');

            // Update agents table
            const agentsTable = document.getElementById('agents-table');
            agentsTable.innerHTML = data.agents.map(agent => {
                const statusColors = {
                    available: 'bg-green-100 text-green-800',
                    busy: 'bg-yellow-100 text-yellow-800',
                    unavailable: 'bg-red-100 text-red-800',
                    unknown: 'bg-gray-100 text-gray-800'
                };
                const statusColor = statusColors[agent.status] || statusColors.unknown;

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${agent.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${agent.extension}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor}">
                                ${agent.status}
                            </span>
                            ${agent.paused ? '<span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Paused</span>' : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${agent.queues.join(', ')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${agent.calls_taken}</td>
                    </tr>
                `;
            }).join('');
        }

        // Initial fetch
        fetchDashboardData();

        // Refresh periodically
        setInterval(fetchDashboardData, refreshInterval);
    </script>
</body>
</html>
