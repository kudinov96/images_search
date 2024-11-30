<form action="{{ route("search.search") }}" method="GET">
    @csrf
    @method("POST")

    <label>
        <span>Поиск</span>
        <input type="text" name="search" value="{{ old('search', $search ?? "") }}">
    </label>

    <button type="submit">Найти</button>
</form>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

