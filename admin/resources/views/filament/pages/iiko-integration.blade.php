<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Статус подключения --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">🔗 Подключение</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-4 flex-wrap">
                    <button wire:click="testConnection"
                        class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500">
                        <span wire:loading.remove wire:target="testConnection">🔍 Проверить подключение</span>
                        <span wire:loading wire:target="testConnection">⏳ Проверка...</span>
                    </button>

                    @if ($connectionStatus)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium {{ $connectionStatus['success'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $connectionStatus['success'] ? '✅' : '❌' }} {{ $connectionStatus['message'] }}
                        </span>
                    @endif
                </div>

                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    💡 Убедитесь, что API ключ и ID организации указаны в <a
                        href="{{ \App\Filament\Pages\ManageSettings::getUrl() }}"
                        class="text-primary-600 hover:underline">Настройках</a> (раздел iiko)
                </div>
            </div>
        </div>

        {{-- Синхронизация меню --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📋 Синхронизация меню</h3>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Импорт категорий и продуктов из iiko в систему. Существующие записи будут обновлены, новые —
                    созданы.
                </p>
                <div class="flex gap-3 flex-wrap">
                    <button wire:click="syncCategories"
                        class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg px-3 py-2 text-sm inline-grid shadow-sm bg-amber-600 text-white hover:bg-amber-500">
                        <span wire:loading.remove wire:target="syncCategories">📂 Категории</span>
                        <span wire:loading wire:target="syncCategories">⏳ Загрузка...</span>
                    </button>

                    <button wire:click="syncProducts"
                        class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg px-3 py-2 text-sm inline-grid shadow-sm bg-blue-600 text-white hover:bg-blue-500">
                        <span wire:loading.remove wire:target="syncProducts">🍽️ Продукты</span>
                        <span wire:loading wire:target="syncProducts">⏳ Загрузка...</span>
                    </button>

                    <button wire:click="syncAll"
                        class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg px-3 py-2 text-sm inline-grid shadow-sm bg-green-600 text-white hover:bg-green-500">
                        <span wire:loading.remove wire:target="syncAll">🔄 Полная синхронизация</span>
                        <span wire:loading wire:target="syncAll">⏳ Синхронизация...</span>
                    </button>
                </div>

                @if ($syncResult)
                    <div class="mt-4 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Результат:</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Категорий:</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $syncResult['categories']['synced'] ?? 0 }}</span>
                                @if (!empty($syncResult['categories']['error']))
                                    <span class="text-red-600 text-xs">❌ {{ $syncResult['categories']['error'] }}</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Продуктов:</span>
                                <span
                                    class="font-semibold text-gray-900 dark:text-white">{{ $syncResult['products']['synced'] ?? 0 }}</span>
                                @if (!empty($syncResult['products']['error']))
                                    <span class="text-red-600 text-xs">❌ {{ $syncResult['products']['error'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Информация --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📖 Справка</h3>
            </div>
            <div class="p-6">
                <div class="prose dark:prose-invert max-w-none text-sm">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 font-medium text-gray-700 dark:text-gray-300 w-1/3">API</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400">iiko Cloud API v1</td>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 font-medium text-gray-700 dark:text-gray-300">Документация</td>
                                <td class="py-2"><a href="https://api-ru.iiko.services/swagger/ui/index"
                                        target="_blank"
                                        class="text-primary-600 hover:underline">api-ru.iiko.services/swagger</a></td>
                            </tr>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 font-medium text-gray-700 dark:text-gray-300">Отправка заказов</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400">Автоматическая при создании заказа
                                    через API</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium text-gray-700 dark:text-gray-300">Авто-синхронизация</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400">Настраивается через Laravel Scheduler
                                    (cron)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
