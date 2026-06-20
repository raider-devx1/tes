@if ($errors->any())
    <div class="bg-red-100 text-red-800 p-3 rounded">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li> $error </li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label class="block text-sm font-medium text-gray-700">Judul</label>
    <input type="text" name="judul" value=" old('judul', $informasi->judul ?? '') "
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Kategori</label>
    <select name="kategori"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        @foreach ($kategoriLabels as $key => $label)
            <option value=" $key " @selected(old('kategori', $informasi->kategori ?? '') === $key)>
                 $label 
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Konten</label>
    <textarea name="konten" rows="5"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required> old('konten', $informasi->konten ?? '') </textarea>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Urutan</label>
    <input type="number" name="urutan" min="0" value=" old('urutan', $informasi->urutan ?? 0) "
           class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
</div>