<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{route("home")}}"> Ecommerce App</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item active">
                <a class="nav-link" href="{{route("home")}}">Home <span class="sr-only"></span></a>
            </li>

            @auth
                <li class="nav-item">
                    <a class="nav-link" href="{{route("cart.show")}}">Cart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route("order.history")}}">Orders</a>
                </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route("logout")}}">Logout</a>
            </li>
            @endauth
        </ul>

    </div>
</nav>
