@extends('core/base::layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">{{ trans('plugins/product-bundles::bundles.list.title') }}</h4>
                    <a class="btn btn-primary" href="{{ route('products.create', ['bundle' => 1]) }}">{{ trans('plugins/product-bundles::bundles.list.create') }}</a>
                </div>
                <div class="card-body">
                    @if (session('success_msg'))
                        <div class="alert alert-success">{{ session('success_msg') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="60">{{ trans('plugins/product-bundles::bundles.list.columns.id') }}</th>
                                    <th>{{ trans('plugins/product-bundles::bundles.list.columns.name') }}</th>
                                    <th width="120">{{ trans('plugins/product-bundles::bundles.list.columns.type') }}</th>
                                    <th width="160">{{ trans('plugins/product-bundles::bundles.list.columns.pricing') }}</th>
                                    <th width="120">{{ trans('plugins/product-bundles::bundles.list.columns.active') }}</th>
                                    <th width="200">{{ trans('plugins/product-bundles::bundles.list.columns.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bundles as $bundle)
                                    <tr>
                                        <td>{{ $bundle->id }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $bundle->name }}</div>
                                            @if ($bundle->description)
                                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($bundle->description, 110) }}</div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info text-dark">{{ strtoupper($bundle->type) }}</span></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $bundle->pricing_type }}: {{ $bundle->pricing_value }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($bundle->is_active)
                                                <span class="badge bg-success">{{ trans('plugins/product-bundles::bundles.yes') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ trans('plugins/product-bundles::bundles.no') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $editUrl = $bundle->ecommerce_product_id
                                                    ? route('products.edit', $bundle->ecommerce_product_id)
                                                    : route('product-bundles.edit', $bundle);
                                            @endphp
                                            <a class="btn btn-sm btn-info" href="{{ $editUrl }}">{{ trans('plugins/product-bundles::bundles.list.buttons.edit') }}</a>

                                            <form method="POST" action="{{ route('product-bundles.destroy', $bundle) }}" class="d-inline"
                                                onsubmit="return confirm('{{ trans('plugins/product-bundles::bundles.list.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit">{{ trans('plugins/product-bundles::bundles.list.buttons.delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">{{ trans('plugins/product-bundles::bundles.list.empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {!! $bundles->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
