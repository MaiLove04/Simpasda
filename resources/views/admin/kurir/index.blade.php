@extends('layouts.admin')

@section('content')


<div class="card border-0 shadow-sm p-4">


    <div class="d-flex justify-content-between mb-4">


        <h3 class="fw-bold mb-0">

            Data Kurir

        </h3>




        <a
            href="/admin/kurir/create"
            class="btn btn-success"
        >

            + Tambah Kurir

        </a>


    </div>






    <table class="table align-middle">


        <thead>

            <tr>

                <th>No</th>

                <th>Foto</th>

                <th>Nama</th>

                <th>Email</th>

                <th>Status</th>

                <th>Aksi</th>

            </tr>

        </thead>






        <tbody>


            @foreach($kurirs as $index => $kurir)

                <tr>



                    <td>

                        {{ $index + 1 }}

                    </td>






                    <td>

                        @if($kurir->foto)

                            <img
                                src="/{{ $kurir->foto }}"
                                width="50"
                                height="50"
                                style="
                                    border-radius:50%;
                                    object-fit:cover;
                                "
                            >

                        @else

                            <img
                                src="https://ui-avatars.com/api/?name={{ $kurir->name }}"
                                width="50"
                                height="50"
                                style="
                                    border-radius:50%;
                                "
                            >

                        @endif

                    </td>








                    <td>

                        {{ $kurir->name }}

                    </td>








                    <td>

                        {{ $kurir->email }}

                    </td>








                    <td>

                        <span
                            class="badge bg-success"
                        >

                            {{ $kurir->status }}

                        </span>

                    </td>









                    <td>


                        <a
                            href="/admin/kurir/{{ $kurir->id }}/edit"
                            class="btn btn-warning btn-sm"
                        >

                            Edit

                        </a>







                        <form
                            method="POST"
                            action="/admin/kurir/{{ $kurir->id }}"
                            style="display:inline;"
                        >

                            @csrf

                            @method('DELETE')



                            <button
                                class="btn btn-danger btn-sm"
                                onclick="
                                    return confirm(
                                        'Hapus kurir?'
                                    )
                                "
                            >

                                Hapus

                            </button>


                        </form>


                    </td>


                </tr>

            @endforeach


        </tbody>


    </table>


</div>


@endsection