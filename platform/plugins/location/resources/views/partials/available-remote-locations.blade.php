@if (!empty($locations))
    <div class="table-responsive">
        <x-core::table>
            <x-core::table.body>
                @foreach ($locations as $countryCode => $countryName)
                    <x-core::table.body.row>
                        <x-core::table.body.cell>
                            {{ $countryName }}
                        </x-core::table.body.cell>
                        <x-core::table.body.cell class="text-end">
                            <x-core::button
                                icon="ti ti-download"
                                class="btn-import-location-data"
                                data-url="{{ route('location.bulk-import.import-location-data', strtolower($countryCode)) }}"
                            >
                                {{ trans('plugins/location::bulk-import.import') }}
                            </x-core::button>
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                @endforeach
            </x-core::table.body>
        </x-core::table>
    </div>
@else
    <span class="d-inline-block">{{ trans('core/base::tables.no_data') }}</span>
@endif
