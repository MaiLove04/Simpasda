<!DOCTYPE html>
<html>
<head>

    <title>ASRI Admin</title>

    <style>

        body{
            margin:0;
            height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            background:#f5f7fa;

            font-family:Arial;
        }


        .card{

            width:350px;

            background:white;

            padding:30px;

            border-radius:12px;

            box-shadow:
                0 4px 12px rgba(0,0,0,.1);
        }


        h2{

            text-align:center;
        }


        input{

            width:100%;

            padding:12px;

            margin-bottom:15px;

            box-sizing:border-box;

            border:1px solid #ddd;

            border-radius:8px;
        }


        button{

            width:100%;

            padding:12px;

            border:none;

            border-radius:8px;

            background:#27ae60;

            color:white;

            cursor:pointer;
        }

    </style>

</head>
<body>



<div class="card">


    <h2>

        Login Admin

    </h2>



    @if(session('error'))

        <p>

            {{ session('error') }}

        </p>

    @endif



    <form
        method="POST"
        action="/admin/login"
    >

        @csrf


        <input
            type="email"
            name="email"
            placeholder="Email"
        >



        <input
            type="password"
            name="password"
            placeholder="Password"
        >



        <button>

            Login

        </button>

    </form>


</div>


</body>
</html>