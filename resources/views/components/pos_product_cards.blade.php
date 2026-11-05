@foreach($products as $product)
    @include('components.pos_product_card', ['product' => $product])
@endforeach
