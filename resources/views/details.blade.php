@extends("layouts.default")
@section("title", "Ecommerce - Home")
@section("content")
    <main class="container" style="max-width: 900px">
        <section>
            <img src="{{$product->image}}" width="100%">
            @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
            @endif

            @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
            @endif
            <h1>{{$product->title}}</h1>
            <p>{{$product->price}}</p>
            <p>{{$product->description}}</p>
            <a href="{{route("cart.add", $product->id)}}" class="btn btn-success">Add to cart</a>
        </section>
    </main>

@endsection
