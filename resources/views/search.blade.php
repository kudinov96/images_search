<x-search-form :search="$search ?? old('search')"></x-search-form>

@isset($results)
    <div style="margin-bottom: 15px;">Результаты поиска по запросу "{{ $search ?? old('search') }}":</div>

    <div style="display: flex; flex-wrap: wrap;">
        @foreach ($results as $item)
            <div style="margin-bottom: 15px;">
                {{--<b>id:         </b> {{ $item["_id"] }}<br>
                <b>tags:       </b> {{ json_encode($item["_source"]["tags"]) }}<br>
                <b>thumb_file: </b> {{ $item["_source"]["thumb_file"] }}<br>--}}
                <img src="{{ \Illuminate\Support\Facades\Storage::disk("s3")->url($item["_source"]["thumb_file"]) }}">
{{--
                <b>url: </b> <a target="_blank" href="https://www.shutterstock.com/image/{{ $item["_id"] }}">https://www.shutterstock.com/image/{{ $item["_id"] }}</a>
--}}
                <div class="tags" style="display: none;">tags: {{ json_encode($item["_source"]["tags"]) }}</div>
            </div>
        @endforeach
    </div>

    <div>
        {{ $results->links() }}
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Находим все изображения с тегами
            const images = document.querySelectorAll('img');

            images.forEach(image => {
                image.addEventListener('click', function () {
                    // Ищем ближайший соседний тег <div> с классом tags
                    const parentDiv = this.closest('div');
                    if (parentDiv) {
                        const tagsDiv = parentDiv.querySelector('.tags');
                        if (tagsDiv) {
                            // Переключаем видимость блока с тегами
                            tagsDiv.style.display = tagsDiv.style.display === 'block' ? 'none' : 'block';
                        }
                    }
                });
            });
        });
    </script>
@endif
