<x-search-form :search="$search ?? old('search')"></x-search-form>

@isset($results)
    <div style="margin-bottom: 15px;">Результаты поиска по запросу "{{ $search ?? old('search') }}":</div>

    <div>
        @foreach ($results as $item)
            <div style="margin-bottom: 15px;">
                <b>id:         </b> {{ $item["_id"] }}<br>
                <b>tags:       </b> {{ json_encode($item["_source"]["tags"]) }}<br>
                <b>thumb_file: </b> {{ $item["_source"]["thumb_file"] }}<br>
                <b>url: </b> <a target="_blank" href="https://www.shutterstock.com/image/{{ $item["_id"] }}">https://www.shutterstock.com/image/{{ $item["_id"] }}</a>
            </div>
        @endforeach
    </div>

    <div>
        {{ $results->links() }}
    </div>
@endif
