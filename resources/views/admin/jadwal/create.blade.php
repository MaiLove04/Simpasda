@extends('layouts.admin')

@section('content')


<div class="card border-0 shadow-sm p-4">


    <div class="d-flex justify-content-between mb-4">


        <h3 class="fw-bold mb-0">

            Tambah Jadwal Penjemputan

        </h3>



        <a
            href="/admin/jadwal"
            class="btn btn-secondary"
        >

            Kembali

        </a>


    </div>




    <form
        method="POST"
        action="/admin/jadwal"
    >

        @csrf




        {{-- NASABAH --}}
        <div class="mb-4">

            <label class="form-label fw-semibold">

                Nasabah

            </label>


            <select
                name="nasabah_id"
                id="nasabahSelect"
                class="form-select"
                required
            >

                <option value="">

                    -- Pilih Nasabah --

                </option>



                @foreach($nasabahs as $nasabah)

                    <option
                        value="{{ $nasabah->id }}"
                        data-alamat="{{ $nasabah->alamat }}"
                    >

                        {{ $nasabah->name }}

                    </option>

                @endforeach


            </select>

        </div>





        {{-- KURIR --}}
        <div class="mb-4">

            <label class="form-label fw-semibold">

                Kurir

            </label>


            <select
                name="kurir_id"
                class="form-select"
                required
            >

                <option value="">

                    -- Pilih Kurir --

                </option>



                @foreach($kurirs as $kurir)

                    <option
                        value="{{ $kurir->id }}"
                    >

                        {{ $kurir->name }}

                    </option>

                @endforeach


            </select>

        </div>






        {{-- TANGGAL --}}
        <div class="mb-4">

            <label class="form-label fw-semibold">

                Tanggal Penjemputan

            </label>


            <input
                type="datetime-local"
                name="tanggal_penjemputan"
                class="form-control"
                required
            >

        </div>







        {{-- ALAMAT --}}
        <div class="mb-4">

            <label class="form-label fw-semibold">

                Alamat Nasabah

            </label>


            <textarea
                name="alamat"
                id="alamatField"
                class="form-control bg-light"
                rows="3"
                readonly
            ></textarea>

        </div>







        {{-- CATATAN --}}
        <div class="mb-4">

            <label class="form-label fw-semibold">

                Catatan

            </label>


            <textarea
                name="catatan"
                class="form-control"
                rows="3"
                placeholder="Catatan tambahan..."
            ></textarea>

        </div>






        <button
            type="submit"
            class="btn btn-success px-4"
        >

            Simpan Jadwal

        </button>


    </form>


</div>





<script>

    const nasabahSelect =

        document.getElementById(
            'nasabahSelect'
        );



    const alamatField =

        document.getElementById(
            'alamatField'
        );



    nasabahSelect.addEventListener(

        'change',

        function () {

            const selectedOption =

                this.options[
                    this.selectedIndex
                ];



            alamatField.value =

                selectedOption.dataset.alamat
                || '';
        }
    );

</script>


@endsection