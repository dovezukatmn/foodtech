<x-filament-panels::page>
    <form wire:submit.prevent="save">
        @php
            $settingsByGroup = $this->getSettingsByGroup();
            $groupIcons = [
                'general' => '🏢',
                'delivery' => '🚚',
                'iiko' => '🔗',
                'payment' => '💳',
            ];
        @endphp

        <div class="space-y-6">
            @foreach ($settingsByGroup as $groupKey => $group)
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $groupIcons[$groupKey] ?? '⚙️' }} {{ $group['label'] }}
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach ($group['settings'] as $setting)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        for="setting-{{ $setting->key }}">
                                        {{ $setting->display_name }}
                                    </label>
                                    @if ($setting->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $setting->description }}</p>
                                    @endif
                                </div>
                                <div class="md:col-span-2">
                                    @switch($setting->type)
                                        @case('boolean')
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="settings.{{ $setting->key }}"
                                                    id="setting-{{ $setting->key }}" class="sr-only peer" value="true"
                                                    @if (filter_var($setting->value, FILTER_VALIDATE_BOOLEAN)) checked @endif>
                                                <div
                                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600">
                                                </div>
                                                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ filter_var($settings[$setting->key] ?? $setting->value, FILTER_VALIDATE_BOOLEAN) ? 'Включено' : 'Выключено' }}
                                                </span>
                                            </label>
                                        @break

                                        @case('text')
                                            <textarea wire:model="settings.{{ $setting->key }}" id="setting-{{ $setting->key }}" rows="3"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ $settings[$setting->key] ?? $setting->value }}</textarea>
                                        @break

                                        @case('number')
                                            <input type="number" wire:model="settings.{{ $setting->key }}"
                                                id="setting-{{ $setting->key }}"
                                                value="{{ $settings[$setting->key] ?? $setting->value }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                step="any">
                                        @break

                                        @default
                                            <input type="text" wire:model="settings.{{ $setting->key }}"
                                                id="setting-{{ $setting->key }}"
                                                value="{{ $settings[$setting->key] ?? $setting->value }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    @endswitch
                                </div>
                            </div>
                            @if (!$loop->last)
                                <hr class="border-gray-100 dark:border-gray-800">
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 focus-visible:ring-primary-500/50 dark:focus-visible:ring-primary-400/50">
                <x-heroicon-o-check class="w-5 h-5" />
                Сохранить настройки
            </button>
        </div>
    </form>
</x-filament-panels::page>
