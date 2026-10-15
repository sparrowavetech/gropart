<ul class="ep-category-tree">
@foreach($categories->filter(fn ($node) => $parent ? (int) $node->parent_id === $parent : ! $categories->contains('id', $node->parent_id)) as $node)
    @continue(in_array($node->id, $visited))
    @php
        $children = $categories->where('parent_id', $node->id);
        $active = $category === (int) $node->id;
        $selected = $categories->firstWhere('id', $category);
        $expanded = $active || ($selected && str_starts_with($selected->enquiry_label, $node->enquiry_label . ' / '));
    @endphp
    <li>
        <div class="ep-category-row">
            <a @class(['ep-category-active' => $active]) href="{{ $catalogUrl . '?' . http_build_query(array_filter(['category' => $node->id, 'q' => $search, 'sort' => $sort])) }}" @if($active) aria-current="page" @endif>{{ $node->name }}</a>
            @if($children->isNotEmpty())<button type="button" class="ep-category-toggle" aria-label="{{ __('plugins/enquiry-products::messages.toggle_subcategories_for_name', ['name' => $node->name]) }}" aria-expanded="{{ $expanded ? 'true' : 'false' }}">{{ $expanded ? '−' : '+' }}</button>@endif
        </div>
        @if($children->isNotEmpty())
            <div class="ep-category-children" @if(!$expanded) hidden @endif>
                @include('plugins/enquiry-products::partials.category-tree', ['parent' => (int) $node->id, 'visited' => [...$visited, $node->id]])
            </div>
        @endif
    </li>
@endforeach
</ul>
